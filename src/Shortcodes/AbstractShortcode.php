<?php
/**
 * Abstract Shortcode Class
 *
 * Base class for all shortcodes in the plugin.
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
 * Abstract Shortcode class
 *
 * @since 1.0.0
 */
abstract class AbstractShortcode {

    /**
     * Shortcode tag
     *
     * @var string
     */
    protected $tag = '';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $default_atts = array();

    /**
     * Database service
     *
     * @var \WeCoza\Agents\Database\DatabaseService
     */
    protected $db;

    /**
     * Agent queries
     *
     * @var \WeCoza\Agents\Database\AgentQueries
     */
    protected $agent_queries;

    /**
     * Whether assets have been enqueued
     *
     * @var bool
     */
    protected static $assets_enqueued = false;
    
    /**
     * Whether to use conditional loading
     *
     * @var bool
     */
    protected static $conditional_loading = true;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Initialize database services
        $this->db = \WeCoza\Agents\Database\DatabaseService::getInstance();
        $this->agent_queries = new \WeCoza\Agents\Database\AgentQueries();
        
        // Initialize shortcode
        $this->init();
        
        // Register shortcode
        if (!empty($this->tag)) {
            add_shortcode($this->tag, array($this, 'render'));
        }
        
        // Setup conditional loading
        $this->setup_conditional_loading();
    }

    /**
     * Initialize shortcode
     *
     * Override this method in child classes to set up shortcode properties.
     *
     * @since 1.0.0
     */
    abstract protected function init();

    /**
     * Render shortcode output
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @return string Shortcode output
     */
    public function render($atts = array(), $content = '') {
        // Check permissions
        if (!$this->check_permissions()) {
            return $this->get_permission_denied_message();
        }
        
        // Parse attributes
        $atts = $this->parse_attributes($atts);
        
        // Enqueue assets
        $this->enqueue_assets();
        
        // Start output buffering
        ob_start();
        
        try {
            // Render the shortcode
            $this->render_shortcode($atts, $content);
        } catch (\Exception $e) {
            // Handle errors
            $this->handle_error($e);
        }
        
        // Get and return output
        return ob_get_clean();
    }

    /**
     * Render the actual shortcode content
     *
     * Override this method in child classes to output shortcode content.
     *
     * @since 1.0.0
     * @param array $atts Parsed attributes
     * @param string $content Shortcode content
     */
    abstract protected function render_shortcode($atts, $content);

    /**
     * Parse shortcode attributes
     *
     * @since 1.0.0
     * @param array $atts Raw attributes
     * @return array Parsed attributes
     */
    protected function parse_attributes($atts) {
        $atts = shortcode_atts($this->default_atts, $atts, $this->tag);
        
        // Allow filtering of attributes
        $atts = apply_filters('wecoza_agents_shortcode_atts', $atts, $this->tag);
        $atts = apply_filters("wecoza_agents_{$this->tag}_atts", $atts);
        
        return $atts;
    }

    /**
     * Check permissions
     *
     * Override this method to implement custom permission checks.
     *
     * @since 1.0.0
     * @return bool Whether user has permission
     */
    protected function check_permissions() {
        // By default, allow all users
        return true;
    }

    /**
     * Get permission denied message
     *
     * @since 1.0.0
     * @return string Permission denied message
     */
    protected function get_permission_denied_message() {
        return sprintf(
            '<div class="wecoza-agents-error">%s</div>',
            esc_html__('You do not have permission to view this content.', 'wecoza-agents-plugin')
        );
    }

    /**
     * Enqueue assets
     *
     * Override this method to enqueue shortcode-specific assets.
     *
     * @since 1.0.0
     */
    protected function enqueue_assets() {
        // Only enqueue once
        if (self::$assets_enqueued) {
            return;
        }
        
        // Mark as enqueued
        self::$assets_enqueued = true;
        
        // Enqueue common assets
        $this->enqueue_common_assets();
    }
    
    /**
     * Setup conditional loading
     *
     * @since 1.0.0
     */
    protected function setup_conditional_loading() {
        if (self::$conditional_loading && !is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'conditional_enqueue_assets'));
        }
    }
    
    /**
     * Conditionally enqueue assets based on shortcode presence
     *
     * @since 1.0.0
     */
    public function conditional_enqueue_assets() {
        if (self::$assets_enqueued) {
            return;
        }
        
        global $post;
        
        // Check if shortcode is present in post content
        if (is_singular() && $post && has_shortcode($post->post_content, $this->tag)) {
            $this->enqueue_assets();
            return;
        }
        
        // Check if shortcode is present in widgets
        if ($this->check_widgets_for_shortcode()) {
            $this->enqueue_assets();
            return;
        }
        
        // Check if shortcode might be present in theme files or other content
        if ($this->check_theme_content_for_shortcode()) {
            $this->enqueue_assets();
            return;
        }
    }
    
    /**
     * Check widgets for shortcode
     *
     * @since 1.0.0
     * @return bool Whether shortcode is found in widgets
     */
    protected function check_widgets_for_shortcode() {
        $widgets = wp_get_sidebars_widgets();
        
        foreach ($widgets as $sidebar => $widget_list) {
            if (empty($widget_list) || $sidebar === 'wp_inactive_widgets') {
                continue;
            }
            
            foreach ($widget_list as $widget) {
                $widget_content = $this->get_widget_content($widget);
                if ($widget_content && has_shortcode($widget_content, $this->tag)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get widget content for shortcode checking
     *
     * @since 1.0.0
     * @param string $widget_id Widget ID
     * @return string Widget content
     */
    protected function get_widget_content($widget_id) {
        // Check text widgets
        if (strpos($widget_id, 'text-') === 0) {
            $text_widgets = get_option('widget_text');
            $widget_number = str_replace('text-', '', $widget_id);
            
            if (isset($text_widgets[$widget_number]['text'])) {
                return $text_widgets[$widget_number]['text'];
            }
        }
        
        // Check custom HTML widgets
        if (strpos($widget_id, 'custom_html-') === 0) {
            $html_widgets = get_option('widget_custom_html');
            $widget_number = str_replace('custom_html-', '', $widget_id);
            
            if (isset($html_widgets[$widget_number]['content'])) {
                return $html_widgets[$widget_number]['content'];
            }
        }
        
        return '';
    }
    
    /**
     * Check theme content for shortcode
     *
     * @since 1.0.0
     * @return bool Whether shortcode might be present in theme content
     */
    protected function check_theme_content_for_shortcode() {
        // If we're on a page that commonly uses shortcodes, load assets
        if (is_page() || is_single()) {
            return true;
        }
        
        // Check if this is a page template that might use shortcodes
        $template = get_page_template_slug();
        if ($template && $this->template_may_use_shortcodes($template)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if template may use shortcodes
     *
     * @since 1.0.0
     * @param string $template Template name
     * @return bool Whether template may use shortcodes
     */
    protected function template_may_use_shortcodes($template) {
        // Define templates that commonly use shortcodes
        $shortcode_templates = array(
            'page-templates/agents.php',
            'page-templates/agent-form.php',
            'page-templates/agent-display.php',
            'templates/agents.php',
        );
        
        return in_array($template, $shortcode_templates);
    }
    
    /**
     * Force asset loading (disable conditional loading)
     *
     * @since 1.0.0
     */
    public static function force_asset_loading() {
        self::$conditional_loading = false;
    }

    /**
     * Enqueue common assets
     *
     * @since 1.0.0
     */
    protected function enqueue_common_assets() {
        // Use minified versions unless in debug mode
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        
        // CSS
        wp_enqueue_style(
            'wecoza-agents',
            WECOZA_AGENTS_CSS_URL . 'agents-extracted' . $suffix . '.css',
            array(),
            WECOZA_AGENTS_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'wecoza-agents',
            WECOZA_AGENTS_JS_URL . 'agents-app' . $suffix . '.js',
            array('jquery'),
            WECOZA_AGENTS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'wecoza-agents',
            'wecoza_agents',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wecoza_agents'),
                'i18n' => $this->get_js_translations(),
                'debug' => defined('WP_DEBUG') && WP_DEBUG,
            )
        );
    }

    /**
     * Get JavaScript translations
     *
     * @since 1.0.0
     * @return array Translations
     */
    protected function get_js_translations() {
        return array(
            'error' => __('An error occurred. Please try again.', 'wecoza-agents-plugin'),
            'loading' => __('Loading...', 'wecoza-agents-plugin'),
            'confirm_delete' => __('Are you sure you want to delete this agent?', 'wecoza-agents-plugin'),
            'save' => __('Save', 'wecoza-agents-plugin'),
            'cancel' => __('Cancel', 'wecoza-agents-plugin'),
            'search' => __('Search', 'wecoza-agents-plugin'),
            'no_results' => __('No results found.', 'wecoza-agents-plugin'),
        );
    }

    /**
     * Handle errors
     *
     * @since 1.0.0
     * @param \Exception $e Exception object
     */
    protected function handle_error($e) {
        // Log error
        wecoza_agents_log($e->getMessage(), 'error');
        
        // Display error message in development
        if (WP_DEBUG) {
            echo '<div class="wecoza-agents-error">';
            echo '<strong>' . esc_html__('Error:', 'wecoza-agents-plugin') . '</strong> ';
            echo esc_html($e->getMessage());
            echo '</div>';
        } else {
            // Display generic error in production
            echo '<div class="wecoza-agents-error">';
            echo esc_html__('An error occurred while displaying this content.', 'wecoza-agents-plugin');
            echo '</div>';
        }
    }

    /**
     * Load template
     *
     * @since 1.0.0
     * @param string $template Template name
     * @param array $args Template arguments
     * @param string $type Template type (forms, display, partials)
     */
    protected function load_template($template, $args = array(), $type = '') {
        // Allow themes to override templates
        $template_paths = array();
        
        // Theme override paths
        $template_paths[] = get_stylesheet_directory() . '/wecoza-agents/' . $type . '/' . $template;
        $template_paths[] = get_template_directory() . '/wecoza-agents/' . $type . '/' . $template;
        
        // Plugin default path
        $template_paths[] = WECOZA_AGENTS_TEMPLATES_DIR . $type . '/' . $template;
        
        // Find first existing template
        $template_file = '';
        foreach ($template_paths as $path) {
            if (file_exists($path)) {
                $template_file = $path;
                break;
            }
        }
        
        if (empty($template_file)) {
            $this->handle_error(new \Exception("Template not found: {$template}"));
            return;
        }
        
        // Extract arguments
        if (!empty($args)) {
            extract($args);
        }
        
        // Include template
        include $template_file;
    }

    /**
     * Get current user
     *
     * @since 1.0.0
     * @return \WP_User|false Current user object or false
     */
    protected function get_current_user() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        return wp_get_current_user();
    }

    /**
     * Can current user manage agents
     *
     * @since 1.0.0
     * @return bool Whether user can manage agents
     */
    protected function can_manage_agents() {
        return current_user_can('edit_others_posts'); // Editors and above
    }

    /**
     * Get request parameter
     *
     * @since 1.0.0
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @param string $method Request method (GET, POST, REQUEST)
     * @return mixed Parameter value
     */
    protected function get_request_param($key, $default = null, $method = 'REQUEST') {
        $value = $default;
        
        switch (strtoupper($method)) {
            case 'GET':
                if (isset($_GET[$key])) {
                    $value = $_GET[$key];
                }
                break;
                
            case 'POST':
                if (isset($_POST[$key])) {
                    $value = $_POST[$key];
                }
                break;
                
            default:
                if (isset($_REQUEST[$key])) {
                    $value = $_REQUEST[$key];
                }
                break;
        }
        
        // Sanitize based on expected type
        if (is_array($default)) {
            return is_array($value) ? array_map('sanitize_text_field', $value) : array();
        } elseif (is_int($default)) {
            return intval($value);
        } elseif (is_bool($default)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } else {
            return sanitize_text_field($value);
        }
    }

    /**
     * Verify nonce
     *
     * @since 1.0.0
     * @param string $action Nonce action
     * @param string $name Nonce name
     * @return bool Whether nonce is valid
     */
    protected function verify_nonce($action, $name = '_wpnonce') {
        $nonce = $this->get_request_param($name, '');
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Add success message
     *
     * @since 1.0.0
     * @param string $message Success message
     */
    protected function add_success_message($message) {
        echo '<div class="alert alert-subtle-success alert-dismissible fade show wecoza-agents-notification">';
        echo '<i class="fa-solid fa-circle-check icon-success"></i> ';
        echo esc_html($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }

    /**
     * Add error message
     *
     * @since 1.0.0
     * @param string $message Error message
     */
    protected function add_error_message($message) {
        echo '<div class="alert alert-subtle-danger alert-dismissible fade show wecoza-agents-notification">';
        echo '<i class="fa-solid fa-circle-exclamation icon-danger"></i> ';
        echo esc_html($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }

    /**
     * Format date for display
     *
     * @since 1.0.0
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    protected function format_date($date, $format = '') {
        if (empty($date) || $date === '0000-00-00') {
            return '';
        }
        
        if (empty($format)) {
            $format = get_option('date_format');
        }
        
        return date_i18n($format, strtotime($date));
    }

    /**
     * Format phone number
     *
     * @since 1.0.0
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    protected function format_phone($phone) {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format as needed (example for SA numbers)
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            return substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }
        
        return $phone;
    }

    /**
     * Get pagination HTML
     *
     * @since 1.0.0
     * @param int $total Total items
     * @param int $per_page Items per page
     * @param int $current_page Current page
     * @return string Pagination HTML
     */
    protected function get_pagination_html($total, $per_page, $current_page) {
        $total_pages = ceil($total / $per_page);
        
        if ($total_pages <= 1) {
            return '';
        }
        
        $output = '<nav aria-label="' . esc_attr__('Agent pagination', 'wecoza-agents-plugin') . '">';
        $output .= '<ul class="pagination">';
        
        // Previous link
        if ($current_page > 1) {
            $output .= '<li class="page-item">';
            $output .= '<a class="page-link" href="' . esc_url(add_query_arg('paged', $current_page - 1)) . '">';
            $output .= esc_html__('Previous', 'wecoza-agents-plugin');
            $output .= '</a>';
            $output .= '</li>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i === $current_page) ? ' active' : '';
            $output .= '<li class="page-item' . $active . '">';
            
            if ($i === $current_page) {
                $output .= '<span class="page-link">' . $i . '</span>';
            } else {
                $output .= '<a class="page-link" href="' . esc_url(add_query_arg('paged', $i)) . '">' . $i . '</a>';
            }
            
            $output .= '</li>';
        }
        
        // Next link
        if ($current_page < $total_pages) {
            $output .= '<li class="page-item">';
            $output .= '<a class="page-link" href="' . esc_url(add_query_arg('paged', $current_page + 1)) . '">';
            $output .= esc_html__('Next', 'wecoza-agents-plugin');
            $output .= '</a>';
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</nav>';
        
        return $output;
    }
}