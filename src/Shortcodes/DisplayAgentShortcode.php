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

use WeCoza\Agents\Database\AgentQueries;
use Exception;

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
     * Agent queries instance
     *
     * @var AgentQueries
     */
    protected $agent_queries;

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
        
        // Initialize agent queries
        $this->agent_queries = new AgentQueries();
        
        // Register AJAX handlers
        add_action('wp_ajax_wecoza_agents_paginate', array($this, 'handle_ajax_pagination'));
        add_action('wp_ajax_nopriv_wecoza_agents_paginate', array($this, 'handle_ajax_pagination'));
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
        
        // Use minified versions unless in debug mode
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        
        // Additional assets for display table
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.1.3', true);
        
        // Table search functionality
        wp_enqueue_script(
            'wecoza-agents-table-search',
            WECOZA_AGENTS_JS_URL . 'agents-table-search' . $suffix . '.js',
            array('jquery', 'wecoza-agents'),
            WECOZA_AGENTS_VERSION,
            true
        );
        
        // AJAX pagination functionality
        wp_enqueue_script(
            'wecoza-agents-ajax-pagination',
            WECOZA_AGENTS_JS_URL . 'agents-ajax-pagination' . $suffix . '.js',
            array('jquery', 'wecoza-agents'),
            WECOZA_AGENTS_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('wecoza-agents-ajax-pagination', 'wecoza_agents_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wecoza_agents_pagination'),
            'loading_text' => __('Loading...', 'wecoza-agents-plugin'),
            'error_text' => __('Error loading agents. Please try again.', 'wecoza-agents-plugin'),
        ));
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
        
        // Get agent statistics
        $statistics = $this->get_agent_statistics();
        
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
            'statistics' => $statistics,
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
        try {
            // Build query arguments
            $args = array(
                'status' => 'all', // Get all agents regardless of status
                'orderby' => $this->map_sort_column($this->sort_column),
                'order' => $this->sort_order,
                'limit' => $this->per_page,
                'offset' => ($this->current_page - 1) * $this->per_page,
                'search' => $this->search_query,
            );
            
            // Get agents from database
            $agents = $this->agent_queries->get_agents($args);
            
            // Map fields for frontend
            $mapped_agents = array();
            foreach ($agents as $agent) {
                $mapped_agents[] = $this->map_agent_fields($agent);
            }
            
            return $mapped_agents;
            
        } catch (Exception $e) {
            error_log('WeCoza Agents: Error fetching agents - ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get total number of agents
     *
     * @since 1.0.0
     * @return int Total agents
     */
    private function get_total_agents() {
        try {
            // Build query arguments without pagination for count
            $args = array(
                'status' => 'all',
                'search' => $this->search_query,
                'limit' => 0, // No limit to get total count
            );
            
            // Get all agents matching the criteria
            $agents = $this->agent_queries->get_agents($args);
            
            return count($agents);
            
        } catch (Exception $e) {
            error_log('WeCoza Agents: Error counting agents - ' . $e->getMessage());
            return 0;
        }
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

    /**
     * Get view URL for single agent
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return string View URL
     */
    public function get_view_url($agent_id) {
        // Sub-task 3.1: Add get_view_url($agent_id) method
        // Sub-task 3.2: Implement get_view_url() to return URL to single agent page
        // Sub-task 3.3: Use home_url() or get_permalink() to generate proper WordPress URLs
        
        // Try to get the single agent page URL from settings first
        // In production, this would be stored in plugin settings
        $single_agent_page_id = get_option('wecoza_agents_single_page_id', 0);
        
        if ($single_agent_page_id && get_post_status($single_agent_page_id) === 'publish') {
            // Use the configured page
            $base_url = get_permalink($single_agent_page_id);
        } else {
            // Fallback to a default path
            // This assumes a page with slug 'agent-view' exists
            $base_url = home_url('/app/agent-view/');
        }
        
        // Add agent_id as query parameter
        return add_query_arg('agent_id', $agent_id, $base_url);
    }

    /**
     * Map database agent fields to frontend expected fields
     *
     * @since 1.0.0
     * @param array $agent Agent data from database
     * @return array Mapped agent data
     */
    private function map_agent_fields($agent) {
        return array(
            'id' => $agent['agent_id'],
            'first_name' => $agent['first_name'],
            'initials' => $agent['initials'],
            'last_name' => $agent['surname'],
            'gender' => $agent['gender'],
            'race' => $agent['race'],
            'phone' => $agent['tel_number'],
            'email' => $agent['email_address'],
            'city' => $agent['city'],
            'status' => $agent['status'],
            'sace_number' => $agent['sace_number'],
            'quantum_maths_score' => intval($agent['quantum_maths_score']),
            'quantum_science_score' => intval($agent['quantum_science_score']),
        );
    }

    /**
     * Map frontend column names to database column names
     *
     * @since 1.0.0
     * @param string $column Frontend column name
     * @return string Database column name
     */
    private function map_sort_column($column) {
        $map = array(
            'last_name' => 'surname',
            'phone' => 'tel_number',
            'email' => 'email_address',
        );
        
        return isset($map[$column]) ? $map[$column] : $column;
    }

    /**
     * Get agent statistics for display
     *
     * @since 1.0.0
     * @return array Agent statistics with counts and badges
     */
    private function get_agent_statistics() {
        try {
            $db = \WeCoza\Agents\Database\DatabaseService::getInstance();
            
            // Get total agents count
            $total_sql = "SELECT COUNT(*) as count FROM agents WHERE status != 'deleted'";
            $total_result = $db->query($total_sql);
            $total_agents = $total_result ? $total_result->fetch()['count'] : 0;
            
            // Get active agents count
            $active_sql = "SELECT COUNT(*) as count FROM agents WHERE status = 'active'";
            $active_result = $db->query($active_sql);
            $active_agents = $active_result ? $active_result->fetch()['count'] : 0;
            
            // Get SACE registered count
            $sace_sql = "SELECT COUNT(*) as count FROM agents WHERE sace_number IS NOT NULL AND sace_number != '' AND status != 'deleted'";
            $sace_result = $db->query($sace_sql);
            $sace_registered = $sace_result ? $sace_result->fetch()['count'] : 0;
            
            // Get quantum qualified count
            $quantum_sql = "SELECT COUNT(*) as count FROM agents WHERE (quantum_maths_score > 0 OR quantum_science_score > 0) AND status != 'deleted'";
            $quantum_result = $db->query($quantum_sql);
            $quantum_qualified = $quantum_result ? $quantum_result->fetch()['count'] : 0;
            
            // Return statistics without demo badges
            return array(
                'total_agents' => array(
                    'label' => __('Total Agents', 'wecoza-agents-plugin'),
                    'count' => $total_agents,
                    'badge' => null,
                    'badge_type' => null
                ),
                'active_agents' => array(
                    'label' => __('Active Agents', 'wecoza-agents-plugin'),
                    'count' => $active_agents,
                    'badge' => null,
                    'badge_type' => null
                ),
                'sace_registered' => array(
                    'label' => __('SACE Registered', 'wecoza-agents-plugin'),
                    'count' => $sace_registered,
                    'badge' => null,
                    'badge_type' => null
                ),
                'quantum_qualified' => array(
                    'label' => __('Quantum Qualified', 'wecoza-agents-plugin'),
                    'count' => $quantum_qualified,
                    'badge' => null,
                    'badge_type' => null
                )
            );
            
        } catch (Exception $e) {
            error_log('WeCoza Agents: Error fetching statistics - ' . $e->getMessage());
            
            // Return zeros on error
            return array(
                'total_agents' => array(
                    'label' => __('Total Agents', 'wecoza-agents-plugin'),
                    'count' => 0,
                    'badge' => null,
                    'badge_type' => null
                ),
                'active_agents' => array(
                    'label' => __('Active Agents', 'wecoza-agents-plugin'),
                    'count' => 0,
                    'badge' => null,
                    'badge_type' => null
                ),
                'sace_registered' => array(
                    'label' => __('SACE Registered', 'wecoza-agents-plugin'),
                    'count' => 0,
                    'badge' => null,
                    'badge_type' => null
                ),
                'quantum_qualified' => array(
                    'label' => __('Quantum Qualified', 'wecoza-agents-plugin'),
                    'count' => 0,
                    'badge' => null,
                    'badge_type' => null
                )
            );
        }
    }
    
    /**
     * Handle AJAX pagination request
     *
     * @since 1.0.0
     */
    public function handle_ajax_pagination() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wecoza_agents_pagination')) {
            wp_send_json_error(array('message' => __('Security check failed', 'wecoza-agents-plugin')));
        }
        
        // Get request parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'surname';
        $order = isset($_POST['order']) ? strtoupper(sanitize_text_field($_POST['order'])) : 'ASC';
        
        // Set instance variables
        $this->current_page = max(1, $page);
        $this->per_page = max(1, min(100, $per_page));
        $this->search_query = $search;
        $this->sort_column = $this->map_sort_column($orderby);
        $this->sort_order = in_array($order, array('ASC', 'DESC')) ? $order : 'ASC';
        
        // Get agents data
        $agents = $this->get_agents();
        $total_agents = $this->get_total_agents();
        
        // Calculate pagination
        $total_pages = ceil($total_agents / $this->per_page);
        $start_index = ($this->current_page - 1) * $this->per_page + 1;
        $end_index = min($start_index + $this->per_page - 1, $total_agents);
        
        // Get columns configuration
        $columns = $this->get_display_columns('');
        
        // Get statistics
        $statistics = $this->get_agent_statistics();
        
        // Prepare response data
        $response = array(
            'agents' => $agents,
            'total_agents' => $total_agents,
            'current_page' => $this->current_page,
            'per_page' => $this->per_page,
            'total_pages' => $total_pages,
            'start_index' => $start_index,
            'end_index' => $end_index,
            'statistics' => $statistics,
        );
        
        // Capture the table HTML
        ob_start();
        $this->load_template('agent-display-table-rows.php', array(
            'agents' => $agents,
            'columns' => $columns,
            'can_manage' => $this->can_manage_agents(),
            'show_actions' => true,
        ), 'display');
        $table_html = ob_get_clean();
        
        // Capture the pagination HTML
        ob_start();
        $this->load_template('agent-pagination.php', array(
            'current_page' => $this->current_page,
            'total_pages' => $total_pages,
            'per_page' => $this->per_page,
            'start_index' => $start_index,
            'end_index' => $end_index,
            'total_agents' => $total_agents,
        ), 'display');
        $pagination_html = ob_get_clean();
        
        $response['table_html'] = $table_html;
        $response['pagination_html'] = $pagination_html;
        $response['statistics_html'] = $this->get_statistics_html($statistics);
        
        wp_send_json_success($response);
    }
    
    /**
     * Get statistics HTML
     *
     * @since 1.0.0
     * @param array $statistics Statistics data
     * @return string HTML
     */
    private function get_statistics_html($statistics) {
        ob_start();
        ?>
        <div class="row g-0 flex-nowrap">
            <?php foreach ($statistics as $stat_key => $stat_data) : ?>
            <div class="col-auto <?php echo $stat_key === 'total_agents' ? 'pe-4' : 'px-4'; ?>">
                <h6 class="text-body-tertiary">
                    <?php echo esc_html($stat_data['label']); ?> : <?php echo esc_html($stat_data['count']); ?>
                    <?php if (!empty($stat_data['badge'])) : ?>
                    <div class="badge badge-phoenix fs-10 badge-phoenix-<?php echo esc_attr($stat_data['badge_type']); ?>">
                        <?php echo esc_html($stat_data['badge']); ?>
                    </div>
                    <?php endif; ?>
                </h6>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}