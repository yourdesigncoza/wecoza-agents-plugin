<?php
/**
 * Single Agent Shortcode
 *
 * Handles the single agent display shortcode.
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
 * Single Agent Shortcode class
 *
 * Displays a single agent's complete information on a dedicated page.
 *
 * @since 1.0.0
 */
class SingleAgentShortcode extends AbstractShortcode {
    
    /**
     * Agent ID from URL parameter
     *
     * @var int
     */
    private $agent_id = 0;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Initialize shortcode
     *
     * @since 1.0.0
     */
    protected function init() {
        $this->tag = 'wecoza_display_single_agent';
        $this->default_atts = array(
            'agent_id' => 0, // Can be overridden by URL parameter
        );
    }

    /**
     * Check permissions
     *
     * @since 1.0.0
     * @return bool
     */
    protected function check_permissions() {
        // Allow all users to view agent details for now
        // In the future, this could check for specific capabilities
        return true;
    }

    /**
     * Enqueue assets
     *
     * @since 1.0.0
     */
    protected function enqueue_assets() {
        parent::enqueue_assets();
        
        // Bootstrap is already loaded by parent theme
        // Additional assets can be added here if needed
    }

    /**
     * Render shortcode content
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     */
    protected function render_shortcode($atts, $content) {
        // Sub-task 1.9: Add URL parameter handling
        // Get agent_id from URL parameter if not provided in shortcode attributes
        $agent_id = $atts['agent_id'];
        if (empty($agent_id)) {
            $agent_id = $this->get_request_param('agent_id', 0, 'GET');
        }
        
        // Validate agent ID
        $agent_id = intval($agent_id);
        
        // Sub-task 1.11: Pass all required variables to template using load_template() method
        // Prepare template variables
        $template_args = array(
            'agent_id' => $agent_id,
            'agent' => false,
            'error' => false,
            'loading' => false,
            'back_url' => $this->get_back_url(),
            'can_manage' => $this->can_manage_agents(),
            'date_format' => get_option('date_format'),
        );
        
        // Load the template
        $this->load_template('agent-single-display.php', $template_args, 'display');
    }
    
    /**
     * Get back URL to agents list
     *
     * @since 1.0.0
     * @return string Back URL
     */
    private function get_back_url() {
        // Sub-task 1.10: Create get_back_url() method
        // Try to get the referrer first
        $referrer = wp_get_referer();
        
        // If referrer exists and is from the same site, use it
        if ($referrer && strpos($referrer, home_url()) === 0) {
            // Check if referrer is the agents list page (contains our display shortcode)
            // This ensures we go back to the correct page with filters/search preserved
            if (strpos($referrer, 'wecoza_display_agents') !== false || 
                strpos($referrer, 'agents') !== false ||
                strpos($referrer, 'agent-list') !== false) {
                return $referrer;
            }
        }
        
        // Default to home page with /app/agents/ path
        // In production, this should be configurable or use a settings page
        return home_url('/app/agents/');
    }
}