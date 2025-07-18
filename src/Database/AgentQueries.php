<?php
/**
 * Agent Queries
 *
 * Handles all database queries related to agents.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Database;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agent Queries class
 *
 * @since 1.0.0
 */
class AgentQueries {

    /**
     * Database service instance
     *
     * @var DatabaseService
     */
    private $db;

    /**
     * Table names
     *
     * @var array
     */
    private $tables;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->db = DatabaseService::getInstance();
        $this->init_tables();
    }

    /**
     * Initialize table names
     *
     * @since 1.0.0
     */
    private function init_tables() {
        // PostgreSQL-only table names (no prefix needed)
        $this->tables = array(
            'agents' => 'agents',
            'agent_meta' => 'agent_meta',
            'agent_notes' => 'agent_notes',
            'agent_absences' => 'agent_absences',
        );
    }

    /**
     * Get table name
     *
     * @since 1.0.0
     * @param string $table Table identifier
     * @return string Full table name
     */
    private function get_table($table) {
        return isset($this->tables[$table]) ? $this->tables[$table] : '';
    }

    /**
     * Create a new agent
     *
     * @since 1.0.0
     * @param array $data Agent data
     * @return int|false Agent ID on success, false on failure
     */
    public function create_agent($data) {
        // Sanitize and validate data
        $clean_data = $this->sanitize_agent_data($data);
        
        if (empty($clean_data['first_name']) || empty($clean_data['surname']) || empty($clean_data['email_address'])) {
            return false;
        }
        
        // Check if email already exists
        if ($this->get_agent_by_email($clean_data['email_address'])) {
            return false;
        }
        
        // Check if ID number already exists
        if (!empty($clean_data['sa_id_no']) && $this->get_agent_by_id_number($clean_data['sa_id_no'])) {
            return false;
        }
        
        // Add timestamps
        $clean_data['created_at'] = current_time('mysql');
        $clean_data['updated_at'] = current_time('mysql');
        $clean_data['created_by'] = get_current_user_id();
        $clean_data['updated_by'] = get_current_user_id();
        
        // Insert agent
        return $this->db->insert($this->get_table('agents'), $clean_data);
    }

    /**
     * Get agent by ID
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return array|null Agent data or null if not found
     */
    public function get_agent($agent_id) {
        $sql = "SELECT * FROM {$this->get_table('agents')} WHERE agent_id = :agent_id AND status != 'deleted' LIMIT 1";
        $params = array('agent_id' => $agent_id);
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        return $result ? $result->fetch() : null;
    }

    /**
     * Get agent by email
     *
     * @since 1.0.0
     * @param string $email Email address
     * @return array|null Agent data or null if not found
     */
    public function get_agent_by_email($email) {
        $sql = "SELECT * FROM {$this->get_table('agents')} WHERE email_address = :email AND status != 'deleted' LIMIT 1";
        $params = array('email' => $email);
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        return $result ? $result->fetch() : null;
    }

    /**
     * Get agent by ID number
     *
     * @since 1.0.0
     * @param string $id_number ID number
     * @return array|null Agent data or null if not found
     */
    public function get_agent_by_id_number($id_number) {
        $sql = "SELECT * FROM {$this->get_table('agents')} WHERE sa_id_no = :id_number AND status != 'deleted' LIMIT 1";
        $params = array('id_number' => $id_number);
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        return $result ? $result->fetch() : null;
    }

    /**
     * Get all agents
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return array Array of agents
     */
    public function get_agents($args = array()) {
        $defaults = array(
            'status' => 'active',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0,
            'search' => '',
            'meta_query' => array(),
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $sql = "SELECT * FROM {$this->get_table('agents')} WHERE 1=1";
        $params = array();
        
        // Status filter
        if (!empty($args['status'])) {
            if ($args['status'] === 'all') {
                $sql .= " AND status != 'deleted'";
            } else {
                $sql .= " AND status = :status";
                $params['status'] = $args['status'];
            }
        }
        
        // Search
        if (!empty($args['search'])) {
            $search = '%' . $args['search'] . '%';
            $sql .= " AND (
                first_name LIKE :search1 OR 
                surname LIKE :search2 OR 
                email_address LIKE :search3 OR 
                tel_number LIKE :search4 OR
                sa_id_no LIKE :search5
            )";
            $params['search1'] = $search;
            $params['search2'] = $search;
            $params['search3'] = $search;
            $params['search4'] = $search;
            $params['search5'] = $search;
        }
        
        // Order
        $allowed_orderby = array('agent_id', 'first_name', 'surname', 'email_address', 'created_at', 'updated_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY $orderby $order";
        
        // Limit and offset
        if ($args['limit'] > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = (int) $args['limit'];
            $params['offset'] = (int) $args['offset'];
        }
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        return $result ? $result->fetchAll() : array();
    }

    /**
     * Update agent
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update_agent($agent_id, $data) {
        // Remove fields that shouldn't be updated
        unset($data['agent_id']);
        unset($data['created_at']);
        unset($data['created_by']);
        
        // Sanitize data
        $clean_data = $this->sanitize_agent_data($data);
        
        // Add update timestamp
        $clean_data['updated_at'] = current_time('mysql');
        $clean_data['updated_by'] = get_current_user_id();
        
        // Update agent
        $result = $this->db->update(
            $this->get_table('agents'),
            $clean_data,
            array('agent_id' => $agent_id)
        );
        
        return $result !== false;
    }

    /**
     * Delete agent (soft delete)
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return bool Success status
     */
    public function delete_agent($agent_id) {
        return $this->update_agent($agent_id, array('status' => 'deleted'));
    }

    /**
     * Permanently delete agent
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return bool Success status
     */
    public function delete_agent_permanently($agent_id) {
        // Delete related data first
        $this->delete_agent_meta($agent_id);
        $this->delete_agent_notes($agent_id);
        $this->delete_agent_absences($agent_id);
        
        // Delete agent
        $result = $this->db->delete(
            $this->get_table('agents'),
            array('agent_id' => $agent_id)
        );
        
        return $result !== false;
    }

    /**
     * Count agents
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return int Agent count
     */
    public function count_agents($args = array()) {
        $defaults = array(
            'status' => 'active',
            'search' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT COUNT(*) as total FROM {$this->get_table('agents')} WHERE 1=1";
        $params = array();
        
        // Status filter
        if (!empty($args['status'])) {
            if ($args['status'] === 'all') {
                $sql .= " AND status != 'deleted'";
            } else {
                $sql .= " AND status = :status";
                $params['status'] = $args['status'];
            }
        }
        
        // Search
        if (!empty($args['search'])) {
            $search = '%' . $args['search'] . '%';
            $sql .= " AND (
                first_name LIKE :search1 OR 
                surname LIKE :search2 OR 
                email_address LIKE :search3
            )";
            $params['search1'] = $search;
            $params['search2'] = $search;
            $params['search3'] = $search;
        }
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        $row = $result ? $result->fetch() : null;
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Add agent meta
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return int|false Meta ID on success, false on failure
     */
    public function add_agent_meta($agent_id, $meta_key, $meta_value) {
        // Check if meta already exists
        $existing = $this->get_agent_meta($agent_id, $meta_key, true);
        
        if ($existing !== null) {
            // Update existing
            return $this->update_agent_meta($agent_id, $meta_key, $meta_value);
        }
        
        // Insert new meta
        return $this->db->insert($this->get_table('agent_meta'), array(
            'agent_id' => $agent_id,
            'meta_key' => $meta_key,
            'meta_value' => maybe_serialize($meta_value),
            'created_at' => current_time('mysql')
        ));
    }

    /**
     * Get agent meta
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $meta_key Meta key (optional)
     * @param bool $single Return single value
     * @return mixed Meta value(s)
     */
    public function get_agent_meta($agent_id, $meta_key = '', $single = false) {
        $sql = "SELECT * FROM {$this->get_table('agent_meta')} WHERE agent_id = :agent_id";
        $params = array('agent_id' => $agent_id);
        
        if (!empty($meta_key)) {
            $sql .= " AND meta_key = :meta_key";
            $params['meta_key'] = $meta_key;
        }
        
        $sql .= " ORDER BY id ASC";
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        $rows = $result ? $result->fetchAll() : array();
        
        if (empty($rows)) {
            return $single ? null : array();
        }
        
        if ($single) {
            return maybe_unserialize($rows[0]['meta_value']);
        }
        
        $meta = array();
        foreach ($rows as $row) {
            $meta[$row['meta_key']][] = maybe_unserialize($row['meta_value']);
        }
        
        return $meta;
    }

    /**
     * Update agent meta
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return bool Success status
     */
    public function update_agent_meta($agent_id, $meta_key, $meta_value) {
        $result = $this->db->update(
            $this->get_table('agent_meta'),
            array('meta_value' => maybe_serialize($meta_value)),
            array(
                'agent_id' => $agent_id,
                'meta_key' => $meta_key
            )
        );
        
        return $result !== false;
    }

    /**
     * Delete agent meta
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $meta_key Meta key (optional)
     * @return bool Success status
     */
    public function delete_agent_meta($agent_id, $meta_key = '') {
        $where = array('agent_id' => $agent_id);
        
        if (!empty($meta_key)) {
            $where['meta_key'] = $meta_key;
        }
        
        $result = $this->db->delete($this->get_table('agent_meta'), $where);
        
        return $result !== false;
    }

    /**
     * Add agent note
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $note Note content
     * @param string $note_type Note type
     * @return int|false Note ID on success, false on failure
     */
    public function add_agent_note($agent_id, $note, $note_type = 'general') {
        return $this->db->insert($this->get_table('agent_notes'), array(
            'agent_id' => $agent_id,
            'note' => $note,
            'note_type' => $note_type,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ));
    }

    /**
     * Get agent notes
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param array $args Query arguments
     * @return array Array of notes
     */
    public function get_agent_notes($agent_id, $args = array()) {
        $defaults = array(
            'note_type' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT * FROM {$this->get_table('agent_notes')} WHERE agent_id = :agent_id";
        $params = array('agent_id' => $agent_id);
        
        if (!empty($args['note_type'])) {
            $sql .= " AND note_type = :note_type";
            $params['note_type'] = $args['note_type'];
        }
        
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int) $args['limit'];
        }
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        return $result ? $result->fetchAll() : array();
    }

    /**
     * Delete agent notes
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return bool Success status
     */
    public function delete_agent_notes($agent_id) {
        $result = $this->db->delete(
            $this->get_table('agent_notes'),
            array('agent_id' => $agent_id)
        );
        
        return $result !== false;
    }

    /**
     * Add agent absence
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $absence_date Absence date
     * @param string $reason Reason for absence
     * @return int|false Absence ID on success, false on failure
     */
    public function add_agent_absence($agent_id, $absence_date, $reason = '') {
        return $this->db->insert($this->get_table('agent_absences'), array(
            'agent_id' => $agent_id,
            'absence_date' => $absence_date,
            'reason' => $reason,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ));
    }

    /**
     * Get agent absences
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param array $args Query arguments
     * @return array Array of absences
     */
    public function get_agent_absences($agent_id, $args = array()) {
        $defaults = array(
            'from_date' => '',
            'to_date' => '',
            'orderby' => 'absence_date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT * FROM {$this->get_table('agent_absences')} WHERE agent_id = :agent_id";
        $params = array('agent_id' => $agent_id);
        
        if (!empty($args['from_date'])) {
            $sql .= " AND absence_date >= :from_date";
            $params['from_date'] = $args['from_date'];
        }
        
        if (!empty($args['to_date'])) {
            $sql .= " AND absence_date <= :to_date";
            $params['to_date'] = $args['to_date'];
        }
        
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        $result = $this->db->query($sql, $params);
        
        // PostgreSQL-only result handling
        return $result ? $result->fetchAll() : array();
    }

    /**
     * Delete agent absences
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return bool Success status
     */
    public function delete_agent_absences($agent_id) {
        $result = $this->db->delete(
            $this->get_table('agent_absences'),
            array('agent_id' => $agent_id)
        );
        
        return $result !== false;
    }

    /**
     * Sanitize agent data
     *
     * @since 1.0.0
     * @param array $data Raw agent data
     * @return array Sanitized data
     */
    private function sanitize_agent_data($data) {
        $fields = array(
            // Personal Information (database column names)
            'title' => 'sanitize_text_field',
            'first_name' => 'sanitize_text_field',
            'second_name' => 'sanitize_text_field',  // Added missing field
            'surname' => 'sanitize_text_field',  // Database column name
            'initials' => 'sanitize_text_field',
            'gender' => 'sanitize_text_field',
            'race' => 'sanitize_text_field',
            
            // Identification (database column names)
            'id_type' => 'sanitize_text_field',
            'sa_id_no' => 'sanitize_text_field',  // Database column name
            'passport_number' => 'sanitize_text_field',
            
            // Contact Information (database column names)
            'tel_number' => 'sanitize_text_field',  // Database column name
            'email_address' => 'sanitize_email',  // Database column name
            
            // Address Information (database column names)
            'residential_address_line' => 'sanitize_textarea_field',  // Database column name
            'address_line_2' => 'sanitize_text_field',
            'city' => 'sanitize_text_field',
            'province' => 'sanitize_text_field',
            'residential_postal_code' => 'sanitize_text_field',  // Database column name
            
            // Working Areas (database column names)
            'preferred_working_area_1' => array($this, 'sanitize_working_area'),
            'preferred_working_area_2' => array($this, 'sanitize_working_area'),
            'preferred_working_area_3' => array($this, 'sanitize_working_area'),
            
            // SACE Registration (database column names)
            'sace_number' => 'sanitize_text_field',
            'sace_registration_date' => 'sanitize_text_field',  // Added missing field
            'sace_expiry_date' => 'sanitize_text_field',  // Added missing field
            'phase_registered' => 'sanitize_text_field',
            'subjects_registered' => 'sanitize_textarea_field',
            
            // Qualifications (database column names)
            'highest_qualification' => 'sanitize_text_field',
            
            // Quantum Tests (database column names)
            'quantum_maths_score' => 'absint',
            'quantum_science_score' => 'absint',
            'quantum_assessment' => 'absint',  // Fixed spelling and changed to absint for numbers
            
            // Criminal Record (database column names)
            'criminal_record_date' => 'sanitize_text_field',
            'criminal_record_file' => 'sanitize_text_field',
            
            // Agreement (database column names)
            'signed_agreement_date' => 'sanitize_text_field',
            'signed_agreement_file' => 'sanitize_text_field',  // Database column name
            
            // Banking Details (database column names)
            'bank_name' => 'sanitize_text_field',
            'account_holder' => 'sanitize_text_field',
            'bank_account_number' => 'sanitize_text_field',  // Database column name
            'bank_branch_code' => 'sanitize_text_field',  // Database column name
            'account_type' => 'sanitize_text_field',
            
            // Training (database column names)
            'agent_training_date' => 'sanitize_text_field',
            
            // Metadata (database column names)
            'agent_notes' => 'sanitize_textarea_field',  // Database column name
            'status' => 'sanitize_text_field',
            'created_at' => 'sanitize_text_field',
            'updated_at' => 'sanitize_text_field',
            'created_by' => 'absint',
            'updated_by' => 'absint',
            
            // Legacy fields
            'residential_suburb' => 'sanitize_text_field',
            'residential_town_id' => 'absint',
        );
        
        $clean_data = array();
        
        foreach ($fields as $field => $sanitize_function) {
            if (isset($data[$field])) {
                $clean_data[$field] = call_user_func($sanitize_function, $data[$field]);
            }
        }
        
        return $clean_data;
    }

    /**
     * Sanitize working area field
     * Returns NULL for empty values instead of 0 to avoid foreign key violations
     *
     * @since 1.0.0
     * @param mixed $value The value to sanitize
     * @return int|null Sanitized value or NULL if empty
     */
    private function sanitize_working_area($value) {
        if (empty($value) || $value === '' || $value === '0') {
            return null;
        }
        return absint($value);
    }

    /**
     * Search agents
     *
     * @since 1.0.0
     * @param string $search Search term
     * @param array $args Additional query arguments
     * @return array Array of agents
     */
    public function search_agents($search, $args = array()) {
        $args['search'] = $search;
        return $this->get_agents($args);
    }

    /**
     * Get agents by status
     *
     * @since 1.0.0
     * @param string $status Agent status
     * @param array $args Additional query arguments
     * @return array Array of agents
     */
    public function get_agents_by_status($status, $args = array()) {
        $args['status'] = $status;
        return $this->get_agents($args);
    }

    /**
     * Bulk update agent status
     *
     * @since 1.0.0
     * @param array $agent_ids Array of agent IDs
     * @param string $status New status
     * @return int Number of updated agents
     */
    public function bulk_update_status($agent_ids, $status) {
        if (empty($agent_ids) || !is_array($agent_ids)) {
            return 0;
        }
        
        $count = 0;
        foreach ($agent_ids as $agent_id) {
            if ($this->update_agent($agent_id, array('status' => $status))) {
                $count++;
            }
        }
        
        return $count;
    }
}