<?php
/**
 * Backwards Compatibility Handler
 *
 * Handles backwards compatibility for any breaking changes during the migration
 * from theme-based to plugin-based agent functionality.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Backwards Compatibility class
 *
 * @since 1.0.0
 */
class BackwardsCompatibility {

    /**
     * Instance
     *
     * @var BackwardsCompatibility
     */
    private static $instance = null;

    /**
     * Theme constants mapping
     *
     * @var array
     */
    private $theme_constants = array();

    /**
     * Function mappings
     *
     * @var array
     */
    private $function_mappings = array();

    /**
     * Legacy hook mappings
     *
     * @var array
     */
    private $hook_mappings = array();

    /**
     * Get instance
     *
     * @since 1.0.0
     * @return BackwardsCompatibility
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init_compatibility_mappings();
        $this->init_hooks();
    }

    /**
     * Initialize compatibility mappings
     *
     * @since 1.0.0
     */
    private function init_compatibility_mappings() {
        // Theme constants that might be referenced
        $this->theme_constants = array(
            'WECOZA_CHILD_DIR' => get_stylesheet_directory(),
            'WECOZA_CHILD_URL' => get_stylesheet_directory_uri(),
            'WECOZA_PLUGIN_VERSION' => WECOZA_AGENTS_VERSION
        );

        // Function mappings for deprecated theme functions
        $this->function_mappings = array(
            'load_agents_files' => array($this, 'load_agents_files_fallback'),
            'enqueue_agents_assets' => array($this, 'enqueue_agents_assets_fallback'),
            'agents_capture_shortcode' => array($this, 'agents_capture_shortcode_fallback'),
            'wecoza_display_agents_shortcode' => array($this, 'wecoza_display_agents_shortcode_fallback')
        );

        // Hook mappings for legacy actions/filters
        $this->hook_mappings = array(
            'wecoza_theme_agents_loaded' => 'wecoza_agents_loaded',
            'wecoza_theme_agents_init' => 'wecoza_agents_init',
            'wecoza_theme_agents_assets_enqueued' => 'wecoza_agents_assets_enqueued'
        );
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Register function fallbacks
        add_action('init', array($this, 'register_function_fallbacks'), 5);
        
        // Register constant fallbacks
        add_action('init', array($this, 'register_constant_fallbacks'), 5);
        
        // Register hook compatibility
        add_action('init', array($this, 'register_hook_compatibility'), 5);
        
        // Handle asset URL compatibility
        add_filter('wp_enqueue_scripts', array($this, 'handle_asset_compatibility'), 5);
        
        // Handle database compatibility
        add_action('plugins_loaded', array($this, 'handle_database_compatibility'), 5);
    }

    /**
     * Register function fallbacks
     *
     * @since 1.0.0
     */
    public function register_function_fallbacks() {
        foreach ($this->function_mappings as $function_name => $callback) {
            if (!function_exists($function_name)) {
                // Create fallback function dynamically
                $this->create_fallback_function($function_name, $callback);
            }
        }
    }

    /**
     * Create fallback function
     *
     * @since 1.0.0
     * @param string $function_name Function name
     * @param callable $callback Callback function
     */
    private function create_fallback_function($function_name, $callback) {
        if (!function_exists($function_name)) {
            eval("function $function_name() {
                \$args = func_get_args();
                return call_user_func_array(" . var_export($callback, true) . ", \$args);
            }");
        }
    }

    /**
     * Register constant fallbacks
     *
     * @since 1.0.0
     */
    public function register_constant_fallbacks() {
        foreach ($this->theme_constants as $constant => $value) {
            if (!defined($constant)) {
                define($constant, $value);
            }
        }
    }

    /**
     * Register hook compatibility
     *
     * @since 1.0.0
     */
    public function register_hook_compatibility() {
        foreach ($this->hook_mappings as $old_hook => $new_hook) {
            // Forward old hooks to new hooks
            add_action($old_hook, function() use ($new_hook) {
                $args = func_get_args();
                do_action_ref_array($new_hook, $args);
            }, 10, 99);
        }
    }

    /**
     * Handle asset compatibility
     *
     * @since 1.0.0
     */
    public function handle_asset_compatibility() {
        // Check if theme is trying to load agent assets
        global $wp_scripts, $wp_styles;
        
        if (isset($wp_scripts->registered['agents-app'])) {
            // Theme is trying to load agent script - redirect to plugin version
            wp_deregister_script('agents-app');
            wp_enqueue_script(
                'wecoza-agents', 
                WECOZA_AGENTS_JS_URL . 'agents-app.js', 
                array('jquery', 'select2'), 
                WECOZA_AGENTS_VERSION, 
                true
            );
        }
    }

    /**
     * Handle database compatibility
     *
     * @since 1.0.0
     */
    public function handle_database_compatibility() {
        // Ensure database service is available for legacy code
        if (class_exists('learner_DB')) {
            // Map legacy database calls to plugin equivalents if needed
            add_filter('wecoza_agents_database_service', function($service) {
                if (empty($service) && class_exists('learner_DB')) {
                    // Return legacy database service as fallback
                    return new learner_DB();
                }
                return $service;
            });
        }
    }

    /**
     * Fallback for load_agents_files function
     *
     * @since 1.0.0
     */
    public function load_agents_files_fallback() {
        _deprecated_function(__FUNCTION__, '1.0.0', 'WeCoza Agents Plugin auto-loads all files');
        
        // Log the deprecated function call
        if (WP_DEBUG) {
            error_log('[WeCoza Agents] Deprecated function call: load_agents_files() - Plugin handles file loading automatically');
        }
        
        // No action needed - plugin handles file loading
        return true;
    }

    /**
     * Fallback for enqueue_agents_assets function
     *
     * @since 1.0.0
     */
    public function enqueue_agents_assets_fallback() {
        _deprecated_function(__FUNCTION__, '1.0.0', 'WeCoza Agents Plugin handles asset loading automatically');
        
        // Log the deprecated function call
        if (WP_DEBUG) {
            error_log('[WeCoza Agents] Deprecated function call: enqueue_agents_assets() - Plugin handles asset loading automatically');
        }
        
        // No action needed - plugin handles asset loading
        return true;
    }

    /**
     * Fallback for agents_capture_shortcode function
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function agents_capture_shortcode_fallback($atts = array()) {
        _deprecated_function(__FUNCTION__, '1.0.0', 'WeCoza Agents Plugin [wecoza_capture_agents] shortcode');
        
        // Log the deprecated function call
        if (WP_DEBUG) {
            error_log('[WeCoza Agents] Deprecated function call: agents_capture_shortcode() - Use [wecoza_capture_agents] shortcode instead');
        }
        
        // Redirect to plugin shortcode
        return do_shortcode('[wecoza_capture_agents]');
    }

    /**
     * Fallback for wecoza_display_agents_shortcode function
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function wecoza_display_agents_shortcode_fallback($atts = array()) {
        _deprecated_function(__FUNCTION__, '1.0.0', 'WeCoza Agents Plugin [wecoza_display_agents] shortcode');
        
        // Log the deprecated function call
        if (WP_DEBUG) {
            error_log('[WeCoza Agents] Deprecated function call: wecoza_display_agents_shortcode() - Use [wecoza_display_agents] shortcode instead');
        }
        
        // Redirect to plugin shortcode
        return do_shortcode('[wecoza_display_agents]');
    }

    /**
     * Get theme asset URL with fallback
     *
     * @since 1.0.0
     * @param string $asset_path Asset path
     * @return string Asset URL
     */
    public function get_asset_url($asset_path) {
        // Check if it's an agent asset
        if (strpos($asset_path, 'agents') !== false) {
            // Redirect to plugin asset
            $plugin_asset = str_replace('/assets/agents/', '/assets/', $asset_path);
            return WECOZA_AGENTS_PLUGIN_URL . $plugin_asset;
        }
        
        // Return theme asset
        return get_stylesheet_directory_uri() . $asset_path;
    }

    /**
     * Handle legacy database queries
     *
     * @since 1.0.0
     * @param string $query Query type
     * @param array $args Query arguments
     * @return mixed Query results
     */
    public function handle_legacy_database_query($query, $args = array()) {
        // Get plugin database service
        $plugin = Plugin::get_instance();
        $db_service = $plugin->get_component('database');
        
        if (!$db_service) {
            // Fallback to legacy database
            if (class_exists('learner_DB')) {
                $db_service = new learner_DB();
            }
        }
        
        // Handle common query types
        switch ($query) {
            case 'get_agents':
                if (method_exists($db_service, 'get_agents')) {
                    return $db_service->get_agents($args);
                }
                break;
            case 'insert_agent':
                if (method_exists($db_service, 'insert_agent')) {
                    return $db_service->insert_agent($args);
                }
                break;
            case 'update_agent':
                if (method_exists($db_service, 'update_agent')) {
                    return $db_service->update_agent($args);
                }
                break;
            case 'delete_agent':
                if (method_exists($db_service, 'delete_agent')) {
                    return $db_service->delete_agent($args);
                }
                break;
        }
        
        return false;
    }

    /**
     * Check if breaking change occurred
     *
     * @since 1.0.0
     * @param string $function_name Function name
     * @param string $version Version when function was deprecated
     * @return bool
     */
    public function is_breaking_change($function_name, $version) {
        $breaking_changes = array(
            'load_agents_files' => '1.0.0',
            'enqueue_agents_assets' => '1.0.0',
            'agents_capture_shortcode' => '1.0.0',
            'wecoza_display_agents_shortcode' => '1.0.0'
        );
        
        return isset($breaking_changes[$function_name]) && 
               version_compare($version, $breaking_changes[$function_name], '>=');
    }

    /**
     * Get compatibility status
     *
     * @since 1.0.0
     * @return array Compatibility status
     */
    public function get_compatibility_status() {
        return array(
            'constants_defined' => $this->check_constants_defined(),
            'functions_available' => $this->check_functions_available(),
            'hooks_registered' => $this->check_hooks_registered(),
            'assets_compatible' => $this->check_assets_compatible(),
            'database_compatible' => $this->check_database_compatible()
        );
    }

    /**
     * Check if constants are defined
     *
     * @since 1.0.0
     * @return bool
     */
    private function check_constants_defined() {
        foreach ($this->theme_constants as $constant => $value) {
            if (!defined($constant)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if functions are available
     *
     * @since 1.0.0
     * @return bool
     */
    private function check_functions_available() {
        foreach ($this->function_mappings as $function_name => $callback) {
            if (!function_exists($function_name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if hooks are registered
     *
     * @since 1.0.0
     * @return bool
     */
    private function check_hooks_registered() {
        foreach ($this->hook_mappings as $old_hook => $new_hook) {
            if (!has_action($old_hook)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if assets are compatible
     *
     * @since 1.0.0
     * @return bool
     */
    private function check_assets_compatible() {
        // Check if plugin assets are available
        return file_exists(WECOZA_AGENTS_PLUGIN_DIR . 'assets/js/agents-app.js') &&
               file_exists(WECOZA_AGENTS_PLUGIN_DIR . 'assets/css/agents-extracted.css');
    }

    /**
     * Check if database is compatible
     *
     * @since 1.0.0
     * @return bool
     */
    private function check_database_compatible() {
        $plugin = Plugin::get_instance();
        $db_service = $plugin->get_component('database');
        
        return !empty($db_service) || class_exists('learner_DB');
    }

    /**
     * Generate compatibility report
     *
     * @since 1.0.0
     * @return array Compatibility report
     */
    public function generate_compatibility_report() {
        $status = $this->get_compatibility_status();
        
        return array(
            'status' => $status,
            'overall_compatible' => !in_array(false, $status, true),
            'breaking_changes' => $this->get_breaking_changes(),
            'recommendations' => $this->get_recommendations($status)
        );
    }

    /**
     * Get breaking changes
     *
     * @since 1.0.0
     * @return array Breaking changes
     */
    private function get_breaking_changes() {
        return array(
            'Deprecated theme functions replaced with plugin methods',
            'Asset loading moved from theme to plugin',
            'Database service namespace changed',
            'Hook names updated for consistency'
        );
    }

    /**
     * Get recommendations
     *
     * @since 1.0.0
     * @param array $status Compatibility status
     * @return array Recommendations
     */
    private function get_recommendations($status) {
        $recommendations = array();
        
        if (!$status['constants_defined']) {
            $recommendations[] = 'Update code to use plugin constants instead of theme constants';
        }
        
        if (!$status['functions_available']) {
            $recommendations[] = 'Replace deprecated function calls with plugin equivalents';
        }
        
        if (!$status['hooks_registered']) {
            $recommendations[] = 'Update hook names to use plugin action/filter names';
        }
        
        if (!$status['assets_compatible']) {
            $recommendations[] = 'Update asset URLs to reference plugin assets';
        }
        
        if (!$status['database_compatible']) {
            $recommendations[] = 'Update database calls to use plugin database service';
        }
        
        return $recommendations;
    }
}

// Initialize backwards compatibility
BackwardsCompatibility::get_instance();