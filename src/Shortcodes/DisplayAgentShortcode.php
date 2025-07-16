<?php
/**
 * Display Agent Shortcode
 *
 * Handles the agent display table shortcode.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Shortcodes;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display Agent Shortcode class
 *
 * @since 1.0.0
 */
class DisplayAgentShortcode extends AbstractShortcode {

    /**
     * Current page
     *
     * @var int
     */
    private $current_page = 1;

    /**
     * Items per page
     *
     * @var int
     */
    private $per_page = 10;

    /**
     * Search query
     *
     * @var string
     */
    private $search_query = '';

    /**
     * Sort column
     *
     * @var string
     */
    private $sort_column = 'last_name';

    /**
     * Sort order
     *
     * @var string
     */
    private $sort_order = 'ASC';

    /**
     * Initialize shortcode
     *
     * @since 1.0.0
     */
    protected function init() {
        $this->tag = 'wecoza_display_agents';
        $this->default_atts = array(
            'per_page' => 10,
            'show_search' => true,
            'show_filters' => true,
            'show_pagination' => true,
            'show_actions' => true,
            'columns' => '', // Comma-separated list of columns to show
        );
    }

    /**
     * Check permissions
     *
     * @since 1.0.0
     * @return bool
     */
    protected function check_permissions() {
        // Allow all users to view agents, but actions require editor permissions
        return true;
    }

    /**
     * Enqueue assets
     *
     * @since 1.0.0
     */
    protected function enqueue_assets() {
        parent::enqueue_assets();
        
        // Additional assets for display table
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.1.3', true);
    }

    /**
     * Render shortcode content
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     */
    protected function render_shortcode($atts, $content) {
        // Get request parameters
        $this->current_page = max(1, $this->get_request_param('paged', 1, 'GET'));
        $this->per_page = max(1, min(100, $this->get_request_param('per_page', $atts['per_page'], 'GET')));
        $this->search_query = $this->get_request_param('search', '', 'GET');
        $this->sort_column = $this->get_request_param('orderby', 'last_name', 'GET');
        $this->sort_order = strtoupper($this->get_request_param('order', 'ASC', 'GET'));
        
        // Validate sort order
        if (!in_array($this->sort_order, array('ASC', 'DESC'))) {
            $this->sort_order = 'ASC';
        }
        
        // Get agents
        $agents = $this->get_agents();
        $total_agents = $this->get_total_agents();
        
        // Calculate pagination
        $total_pages = ceil($total_agents / $this->per_page);
        $start_index = ($this->current_page - 1) * $this->per_page + 1;
        $end_index = min($start_index + $this->per_page - 1, $total_agents);
        
        // Determine columns to display
        $columns = $this->get_display_columns($atts['columns']);
        
        // Display the table
        $this->load_template('agent-display-table.php', array(
            'agents' => $agents,
            'total_agents' => $total_agents,
            'current_page' => $this->current_page,
            'per_page' => $this->per_page,
            'total_pages' => $total_pages,
            'start_index' => $start_index,
            'end_index' => $end_index,
            'search_query' => $this->search_query,
            'sort_column' => $this->sort_column,
            'sort_order' => $this->sort_order,
            'columns' => $columns,
            'atts' => $atts,
            'can_manage' => $this->can_manage_agents(),
        ), 'display');
        
        // Load modal template if actions are enabled
        if ($atts['show_actions']) {
            $this->load_template('agent-modal.php', array(), 'display');
        }
    }

    /**
     * Get agents for display
     *
     * @since 1.0.0
     * @return array Agents array
     */
    private function get_agents() {
        // For now, return hardcoded data matching the original shortcode
        // In production, this would query the database
        $all_agents = $this->get_hardcoded_agents();
        
        // Apply search filter
        if (!empty($this->search_query)) {
            $all_agents = array_filter($all_agents, array($this, 'filter_by_search'));
            $all_agents = array_values($all_agents); // Re-index array
        }
        
        // Apply sorting
        usort($all_agents, array($this, 'sort_agents'));
        
        // Apply pagination
        $offset = ($this->current_page - 1) * $this->per_page;
        return array_slice($all_agents, $offset, $this->per_page);
    }

    /**
     * Get total number of agents
     *
     * @since 1.0.0
     * @return int Total agents
     */
    private function get_total_agents() {
        // For now, return hardcoded count
        $all_agents = $this->get_hardcoded_agents();
        
        // Apply search filter for accurate count
        if (!empty($this->search_query)) {
            $all_agents = array_filter($all_agents, array($this, 'filter_by_search'));
        }
        
        return count($all_agents);
    }

    /**
     * Get hardcoded agents data
     *
     * @since 1.0.0
     * @return array Agents data
     */
    private function get_hardcoded_agents() {
        return array(
            array(
                'id' => 1,
                'first_name' => 'Peter',
                'initials' => 'P.',
                'last_name' => 'Wessels',
                'gender' => 'Male',
                'race' => 'White',
                'phone' => '0123456789',
                'email' => 'peter.w@example.com',
                'city' => 'Cape Town',
            ),
            array(
                'id' => 2,
                'first_name' => 'Sarah',
                'initials' => 'S.',
                'last_name' => 'Johnson',
                'gender' => 'Female',
                'race' => 'African',
                'phone' => '0987654321',
                'email' => 'sarah.j@example.com',
                'city' => 'Johannesburg',
            ),
            array(
                'id' => 3,
                'first_name' => 'David',
                'initials' => 'D.',
                'last_name' => 'Smith',
                'gender' => 'Male',
                'race' => 'Coloured',
                'phone' => '0212223344',
                'email' => 'david.s@example.com',
                'city' => 'Durban',
            ),
            array(
                'id' => 4,
                'first_name' => 'Maria',
                'initials' => 'M.',
                'last_name' => 'Garcia',
                'gender' => 'Female',
                'race' => 'Indian',
                'phone' => '0334455667',
                'email' => 'maria.g@example.com',
                'city' => 'Pretoria',
            ),
            array(
                'id' => 5,
                'first_name' => 'John',
                'initials' => 'J.',
                'last_name' => 'Doe',
                'gender' => 'Male',
                'race' => 'White',
                'phone' => '0112233445',
                'email' => 'john.d@example.com',
                'city' => 'Bloemfontein',
            ),
            array(
                'id' => 6,
                'first_name' => 'Emily',
                'initials' => 'E.',
                'last_name' => 'Davis',
                'gender' => 'Female',
                'race' => 'African',
                'phone' => '0445566778',
                'email' => 'emily.d@example.com',
                'city' => 'Port Elizabeth',
            ),
            array(
                'id' => 7,
                'first_name' => 'Michael',
                'initials' => 'M.',
                'last_name' => 'Brown',
                'gender' => 'Male',
                'race' => 'Coloured',
                'phone' => '0556677889',
                'email' => 'michael.b@example.com',
                'city' => 'East London',
            ),
            array(
                'id' => 8,
                'first_name' => 'Linda',
                'initials' => 'L.',
                'last_name' => 'Taylor',
                'gender' => 'Female',
                'race' => 'Indian',
                'phone' => '0667788990',
                'email' => 'linda.t@example.com',
                'city' => 'Kimberley',
            ),
            array(
                'id' => 9,
                'first_name' => 'Robert',
                'initials' => 'R.',
                'last_name' => 'Wilson',
                'gender' => 'Male',
                'race' => 'White',
                'phone' => '0778899001',
                'email' => 'robert.w@example.com',
                'city' => 'Polokwane',
            ),
            array(
                'id' => 10,
                'first_name' => 'Jessica',
                'initials' => 'J.',
                'last_name' => 'Lee',
                'gender' => 'Female',
                'race' => 'African',
                'phone' => '0889900112',
                'email' => 'jessica.l@example.com',
                'city' => 'Nelspruit',
            ),
        );
    }

    /**
     * Filter agents by search query
     *
     * @since 1.0.0
     * @param array $agent Agent data
     * @return bool Whether agent matches search
     */
    private function filter_by_search($agent) {
        $search = strtolower($this->search_query);
        $searchable_fields = array('first_name', 'last_name', 'email', 'phone', 'city');
        
        foreach ($searchable_fields as $field) {
            if (isset($agent[$field]) && stripos($agent[$field], $search) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Sort agents
     *
     * @since 1.0.0
     * @param array $a First agent
     * @param array $b Second agent
     * @return int Sort result
     */
    private function sort_agents($a, $b) {
        $column = $this->sort_column;
        
        // Handle missing values
        $val_a = isset($a[$column]) ? $a[$column] : '';
        $val_b = isset($b[$column]) ? $b[$column] : '';
        
        // Compare values
        $result = strcasecmp($val_a, $val_b);
        
        // Apply sort order
        return ($this->sort_order === 'DESC') ? -$result : $result;
    }

    /**
     * Get display columns
     *
     * @since 1.0.0
     * @param string $columns_setting Columns setting from shortcode
     * @return array Columns to display
     */
    private function get_display_columns($columns_setting) {
        $default_columns = array(
            'first_name' => __('First Name', 'wecoza-agents-plugin'),
            'initials' => __('Initials', 'wecoza-agents-plugin'),
            'last_name' => __('Surname', 'wecoza-agents-plugin'),
            'gender' => __('Gender', 'wecoza-agents-plugin'),
            'race' => __('Race', 'wecoza-agents-plugin'),
            'phone' => __('Tel Number', 'wecoza-agents-plugin'),
            'email' => __('Email Address', 'wecoza-agents-plugin'),
            'city' => __('City/Town', 'wecoza-agents-plugin'),
        );
        
        // If specific columns are requested, filter the default set
        if (!empty($columns_setting)) {
            $requested = array_map('trim', explode(',', $columns_setting));
            $columns = array();
            
            foreach ($requested as $col) {
                if (isset($default_columns[$col])) {
                    $columns[$col] = $default_columns[$col];
                }
            }
            
            return !empty($columns) ? $columns : $default_columns;
        }
        
        return $default_columns;
    }

    /**
     * Get sort URL
     *
     * @since 1.0.0
     * @param string $column Column to sort by
     * @return string Sort URL
     */
    public function get_sort_url($column) {
        $args = array(
            'orderby' => $column,
            'order' => ($this->sort_column === $column && $this->sort_order === 'ASC') ? 'DESC' : 'ASC',
        );
        
        // Preserve other parameters
        if (!empty($this->search_query)) {
            $args['search'] = $this->search_query;
        }
        if ($this->per_page != 10) {
            $args['per_page'] = $this->per_page;
        }
        
        return add_query_arg($args);
    }

    /**
     * Get edit URL for agent
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return string Edit URL
     */
    public function get_edit_url($agent_id) {
        // Get the capture form page URL
        // In production, this would be configurable
        $capture_page_url = home_url('/agent-capture/');
        
        return add_query_arg('agent_id', $agent_id, $capture_page_url);
    }
}