<?php
/**
 * Agent Model
 *
 * Represents an agent entity with data structure and validation rules.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Models;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agent Model class
 *
 * @since 1.0.0
 */
class Agent {

    /**
     * Agent ID
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Agent data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Modified fields
     *
     * @var array
     */
    protected $modified = array();

    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Default agent data structure
     *
     * @var array
     */
    protected static $defaults = array(
        // Personal Information
        'title' => '',
        'first_name' => '',
        'last_name' => '',
        'known_as' => '',
        'initials' => '',
        'gender' => '',
        'race' => '',
        
        // Identification
        'id_type' => 'sa_id', // sa_id or passport
        'id_number' => '',
        'passport_number' => '',
        
        // Contact Information
        'phone' => '',
        'email' => '',
        'street_address' => '',
        'city' => '',
        'province' => '',
        'postal_code' => '',
        
        // SACE Registration
        'sace_number' => '',
        'phase_registered' => '',
        'subjects_registered' => '',
        
        // Qualifications
        'highest_qualification' => '',
        
        // Quantum Tests
        'quantum_maths_passed' => false,
        'quantum_science_passed' => false,
        'quantum_communications' => '',
        'quantum_mathematics' => '',
        'quantum_training' => '',
        
        // Criminal Record
        'criminal_record_checked' => false,
        'criminal_record_date' => '',
        'criminal_record_file' => '',
        
        // Agreement
        'signed_agreement' => false,
        'signed_agreement_date' => '',
        'agreement_file_path' => '',
        
        // Banking Details
        'bank_name' => '',
        'account_holder' => '',
        'account_number' => '',
        'branch_code' => '',
        'account_type' => '',
        
        // Working Areas
        'preferred_areas' => '', // JSON encoded array
        
        // Training
        'agent_training_date' => '',
        
        // Metadata
        'date_loaded' => '',
        'created_at' => '',
        'updated_at' => '',
        'created_by' => 0,
        'updated_by' => 0,
        'status' => 'active', // active, inactive, suspended
        'notes' => '',
    );

    /**
     * Required fields
     *
     * @var array
     */
    protected static $required_fields = array(
        'first_name',
        'last_name',
        'gender',
        'race',
        'phone',
        'email',
        'street_address',
        'city',
        'province',
        'postal_code',
    );

    /**
     * Validation rules
     *
     * @var array
     */
    protected static $validation_rules = array(
        'email' => 'email',
        'phone' => 'phone',
        'id_number' => 'sa_id',
        'passport_number' => 'passport',
        'postal_code' => 'numeric',
        'bank_account_number' => 'numeric',
        'branch_code' => 'numeric',
    );

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param array|int $data Agent data or ID
     */
    public function __construct($data = array()) {
        if (is_numeric($data)) {
            $this->load($data);
        } elseif (is_array($data)) {
            $this->set_data($data);
        }
    }

    /**
     * Load agent by ID
     *
     * @since 1.0.0
     * @param int $id Agent ID
     * @return bool Success
     */
    public function load($id) {
        $id = absint($id);
        if (!$id) {
            return false;
        }
        
        $agent_queries = new \WeCoza\Agents\Database\AgentQueries();
        $data = $agent_queries->get_agent($id);
        
        if (!$data) {
            return false;
        }
        
        $this->id = $id;
        $this->set_data($data);
        $this->modified = array();
        
        return true;
    }

    /**
     * Save agent
     *
     * @since 1.0.0
     * @return bool|int Agent ID on success, false on failure
     */
    public function save() {
        // Validate before saving
        if (!$this->validate()) {
            return false;
        }
        
        $agent_queries = new \WeCoza\Agents\Database\AgentQueries();
        
        // Prepare data for saving
        $save_data = $this->get_save_data();
        
        if ($this->id) {
            // Update existing agent
            $success = $agent_queries->update_agent($this->id, $save_data);
            if ($success) {
                $this->modified = array();
                return $this->id;
            }
        } else {
            // Create new agent
            $id = $agent_queries->create_agent($save_data);
            if ($id) {
                $this->id = $id;
                $this->modified = array();
                return $id;
            }
        }
        
        return false;
    }

    /**
     * Delete agent
     *
     * @since 1.0.0
     * @return bool Success
     */
    public function delete() {
        if (!$this->id) {
            return false;
        }
        
        $agent_queries = new \WeCoza\Agents\Database\AgentQueries();
        $success = $agent_queries->delete_agent($this->id);
        
        if ($success) {
            $this->id = 0;
            $this->data = array();
            $this->modified = array();
        }
        
        return $success;
    }

    /**
     * Set agent data
     *
     * @since 1.0.0
     * @param array $data Agent data
     */
    public function set_data($data) {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get agent data
     *
     * @since 1.0.0
     * @return array Agent data
     */
    public function get_data() {
        return array_merge(self::$defaults, $this->data);
    }

    /**
     * Get data for saving
     *
     * @since 1.0.0
     * @return array Data to save
     */
    protected function get_save_data() {
        $data = $this->get_data();
        
        // Remove fields that shouldn't be saved directly
        unset($data['id']);
        
        // Set timestamps
        if (!$this->id) {
            $data['created_at'] = current_time('mysql');
            $data['created_by'] = get_current_user_id();
        }
        $data['updated_at'] = current_time('mysql');
        $data['updated_by'] = get_current_user_id();
        
        // Convert boolean fields
        $boolean_fields = array(
            'quantum_maths_passed',
            'quantum_science_passed',
            'criminal_record_checked',
            'signed_agreement'
        );
        
        foreach ($boolean_fields as $field) {
            $data[$field] = $data[$field] ? 1 : 0;
        }
        
        return $data;
    }

    /**
     * Magic getter
     *
     * @since 1.0.0
     * @param string $key Property key
     * @return mixed Property value
     */
    public function __get($key) {
        if ($key === 'id') {
            return $this->id;
        }
        
        return $this->get($key);
    }

    /**
     * Magic setter
     *
     * @since 1.0.0
     * @param string $key Property key
     * @param mixed $value Property value
     */
    public function __set($key, $value) {
        if ($key === 'id') {
            $this->id = absint($value);
        } else {
            $this->set($key, $value);
        }
    }

    /**
     * Magic isset
     *
     * @since 1.0.0
     * @param string $key Property key
     * @return bool Whether property is set
     */
    public function __isset($key) {
        if ($key === 'id') {
            return isset($this->id);
        }
        
        return isset($this->data[$key]);
    }

    /**
     * Get property value
     *
     * @since 1.0.0
     * @param string $key Property key
     * @param mixed $default Default value
     * @return mixed Property value
     */
    public function get($key, $default = null) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        if (isset(self::$defaults[$key])) {
            return self::$defaults[$key];
        }
        
        return $default;
    }

    /**
     * Set property value
     *
     * @since 1.0.0
     * @param string $key Property key
     * @param mixed $value Property value
     */
    public function set($key, $value) {
        // Track modifications
        if (!isset($this->data[$key]) || $this->data[$key] !== $value) {
            $this->modified[$key] = true;
        }
        
        $this->data[$key] = $value;
    }

    /**
     * Check if field was modified
     *
     * @since 1.0.0
     * @param string $key Property key
     * @return bool Whether field was modified
     */
    public function is_modified($key = null) {
        if ($key === null) {
            return !empty($this->modified);
        }
        
        return isset($this->modified[$key]);
    }

    /**
     * Get modified fields
     *
     * @since 1.0.0
     * @return array Modified field keys
     */
    public function get_modified_fields() {
        return array_keys($this->modified);
    }

    /**
     * Validate agent data
     *
     * @since 1.0.0
     * @return bool Whether data is valid
     */
    public function validate() {
        $this->errors = array();
        $data = $this->get_data();
        
        // Check required fields
        foreach (self::$required_fields as $field) {
            if (empty($data[$field])) {
                $this->errors[$field] = sprintf(
                    __('%s is required.', 'wecoza-agents-plugin'),
                    ucfirst(str_replace('_', ' ', $field))
                );
            }
        }
        
        // Validate email
        if (!empty($data['email']) && !is_email($data['email'])) {
            $this->errors['email'] = __('Please enter a valid email address.', 'wecoza-agents-plugin');
        }
        
        // Validate ID number or passport based on type
        if ($data['id_type'] === 'sa_id') {
            if (empty($data['id_number'])) {
                $this->errors['id_number'] = __('SA ID number is required.', 'wecoza-agents-plugin');
            } else {
                $validation = \WeCoza\Agents\Helpers\ValidationHelper::validate_sa_id($data['id_number']);
                if (!$validation['valid']) {
                    $this->errors['id_number'] = $validation['message'];
                }
            }
        } else {
            if (empty($data['passport_number'])) {
                $this->errors['passport_number'] = __('Passport number is required.', 'wecoza-agents-plugin');
            } else {
                $validation = \WeCoza\Agents\Helpers\ValidationHelper::validate_passport($data['passport_number']);
                if (!$validation['valid']) {
                    $this->errors['passport_number'] = $validation['message'];
                }
            }
        }
        
        // Validate phone number format
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) < 10) {
                $this->errors['phone'] = __('Please enter a valid phone number.', 'wecoza-agents-plugin');
            }
        }
        
        // Validate postal code
        if (!empty($data['postal_code']) && !is_numeric($data['postal_code'])) {
            $this->errors['postal_code'] = __('Postal code must be numeric.', 'wecoza-agents-plugin');
        }
        
        // Validate bank details if provided
        if (!empty($data['bank_account_number']) && !is_numeric($data['bank_account_number'])) {
            $this->errors['bank_account_number'] = __('Account number must be numeric.', 'wecoza-agents-plugin');
        }
        
        if (!empty($data['branch_code']) && !is_numeric($data['branch_code'])) {
            $this->errors['branch_code'] = __('Branch code must be numeric.', 'wecoza-agents-plugin');
        }
        
        // Validate dates
        $date_fields = array(
            'criminal_record_date',
            'signed_agreement_date',
            'agent_training_date'
        );
        
        foreach ($date_fields as $field) {
            if (!empty($data[$field]) && !$this->is_valid_date($data[$field])) {
                $this->errors[$field] = sprintf(
                    __('%s must be a valid date.', 'wecoza-agents-plugin'),
                    ucfirst(str_replace('_', ' ', $field))
                );
            }
        }
        
        // Allow filtering of validation
        $this->errors = apply_filters('wecoza_agents_validate_agent', $this->errors, $data, $this);
        
        return empty($this->errors);
    }

    /**
     * Get validation errors
     *
     * @since 1.0.0
     * @return array Validation errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Check if date is valid
     *
     * @since 1.0.0
     * @param string $date Date string
     * @return bool Whether date is valid
     */
    protected function is_valid_date($date) {
        if (empty($date) || $date === '0000-00-00') {
            return true; // Allow empty dates
        }
        
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Get display name
     *
     * @since 1.0.0
     * @return string Display name
     */
    public function get_display_name() {
        $parts = array();
        
        if ($this->get('title')) {
            $parts[] = $this->get('title');
        }
        
        if ($this->get('first_name')) {
            $parts[] = $this->get('first_name');
        }
        
        if ($this->get('known_as') && $this->get('known_as') !== $this->get('first_name')) {
            $parts[] = '(' . $this->get('known_as') . ')';
        }
        
        if ($this->get('last_name')) {
            $parts[] = $this->get('last_name');
        }
        
        return implode(' ', $parts);
    }

    /**
     * Get initials
     *
     * @since 1.0.0
     * @return string Initials
     */
    public function get_initials() {
        if ($this->get('initials')) {
            return $this->get('initials');
        }
        
        $initials = '';
        
        if ($this->get('first_name')) {
            $initials .= strtoupper(substr($this->get('first_name'), 0, 1)) . '.';
        }
        
        if ($this->get('last_name')) {
            $initials .= ' ' . strtoupper(substr($this->get('last_name'), 0, 1)) . '.';
        }
        
        return trim($initials);
    }

    /**
     * Get preferred areas as array
     *
     * @since 1.0.0
     * @return array Preferred areas
     */
    public function get_preferred_areas() {
        $areas = $this->get('preferred_areas');
        
        if (is_string($areas)) {
            $decoded = json_decode($areas, true);
            return is_array($decoded) ? $decoded : array();
        }
        
        return is_array($areas) ? $areas : array();
    }

    /**
     * Set preferred areas
     *
     * @since 1.0.0
     * @param array $areas Preferred areas
     */
    public function set_preferred_areas($areas) {
        if (is_array($areas)) {
            $this->set('preferred_areas', json_encode(array_values($areas)));
        }
    }

    /**
     * Check if agent has quantum qualification
     *
     * @since 1.0.0
     * @param string $type Quantum type (maths, science, or null for any)
     * @return bool Whether agent has qualification
     */
    public function has_quantum_qualification($type = null) {
        if ($type === 'maths') {
            return (bool) $this->get('quantum_maths_passed');
        }
        
        if ($type === 'science') {
            return (bool) $this->get('quantum_science_passed');
        }
        
        // Check if has any quantum qualification
        return $this->get('quantum_maths_passed') || $this->get('quantum_science_passed');
    }

    /**
     * Check if agent has signed agreement
     *
     * @since 1.0.0
     * @return bool Whether agreement is signed
     */
    public function has_signed_agreement() {
        return (bool) $this->get('signed_agreement');
    }

    /**
     * Check if criminal record is checked
     *
     * @since 1.0.0
     * @return bool Whether criminal record is checked
     */
    public function has_criminal_record_check() {
        return (bool) $this->get('criminal_record_checked');
    }

    /**
     * Get status label
     *
     * @since 1.0.0
     * @return string Status label
     */
    public function get_status_label() {
        $status = $this->get('status', 'active');
        $labels = array(
            'active' => __('Active', 'wecoza-agents-plugin'),
            'inactive' => __('Inactive', 'wecoza-agents-plugin'),
            'suspended' => __('Suspended', 'wecoza-agents-plugin'),
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Convert to array
     *
     * @since 1.0.0
     * @return array Agent data array
     */
    public function to_array() {
        $data = $this->get_data();
        $data['id'] = $this->id;
        return $data;
    }

    /**
     * Convert to JSON
     *
     * @since 1.0.0
     * @return string JSON representation
     */
    public function to_json() {
        return json_encode($this->to_array());
    }
}