<?php
/**
 * Main Plugin Class
 *
 * The core plugin class that handles initialization, hooks, and loading of all components.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents;

use WeCoza\Agents\Includes\Constants;
use WeCoza\Agents\Includes\Activator;
use WeCoza\Agents\Includes\Deactivator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 *
 * @since 1.0.0
 */
class Plugin {

    /**
     * The single instance of the class
     *
     * @since 1.0.0
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Plugin version
     *
     * @since 1.0.0
     * @var string
     */
    private $version;

    /**
     * Plugin slug
     *
     * @since 1.0.0
     * @var string
     */
    private $plugin_slug;

    /**
     * Text domain
     *
     * @since 1.0.0
     * @var string
     */
    private $text_domain;

    /**
     * Plugin components
     *
     * @since 1.0.0
     * @var array
     */
    private $components = array();

    /**
     * Main Plugin Instance
     *
     * Ensures only one instance of the plugin is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return Plugin Main instance
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
        $this->version = WECOZA_AGENTS_VERSION;
        $this->plugin_slug = 'wecoza-agents';
        $this->text_domain = 'wecoza-agents-plugin';
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_shortcodes();
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     */
    private function __clone() {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cloning is forbidden.', 'wecoza-agents-plugin'), '1.0.0');
    }

    /**
     * Prevent unserializing
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, esc_html__('Unserializing instances of this class is forbidden.', 'wecoza-agents-plugin'), '1.0.0');
    }

    /**
     * Load required dependencies
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Load core functionality
        $this->load_core_classes();
        
        // Load database classes
        $this->load_database_classes();
        
        // Load shortcode classes
        $this->load_shortcode_classes();
        
        // Load helper classes
        $this->load_helper_classes();
        
        // Load form classes
        $this->load_form_classes();
        
        // Load model classes
        $this->load_model_classes();
    }

    /**
     * Load core classes
     *
     * @since 1.0.0
     */
    private function load_core_classes() {
        // Constants are already loaded from main plugin file
        
        // Load functions
        require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/functions.php';
        
        // Load migration handler
        if (file_exists(WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-migration.php')) {
            require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-migration.php';
        }
        
        // Load deprecation logger
        if (file_exists(WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-deprecation-logger.php')) {
            require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-deprecation-logger.php';
        }
        
        // Load backwards compatibility handler
        if (file_exists(WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-backwards-compatibility.php')) {
            require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-backwards-compatibility.php';
        }
    }

    /**
     * Load database classes
     *
     * @since 1.0.0
     */
    private function load_database_classes() {
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Database/DatabaseService.php')) {
            $this->components['database'] = new \WeCoza\Agents\Database\DatabaseService();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Database/AgentQueries.php')) {
            $this->components['agent_queries'] = new \WeCoza\Agents\Database\AgentQueries();
        }
    }

    /**
     * Load shortcode classes
     *
     * @since 1.0.0
     */
    private function load_shortcode_classes() {
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Shortcodes/CaptureAgentShortcode.php')) {
            $this->components['capture_shortcode'] = new \WeCoza\Agents\Shortcodes\CaptureAgentShortcode();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Shortcodes/DisplayAgentShortcode.php')) {
            $this->components['display_shortcode'] = new \WeCoza\Agents\Shortcodes\DisplayAgentShortcode();
        }
    }

    /**
     * Load helper classes
     *
     * @since 1.0.0
     */
    private function load_helper_classes() {
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Helpers/ValidationHelper.php')) {
            $this->components['validation_helper'] = new \WeCoza\Agents\Helpers\ValidationHelper();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Helpers/ArrayHelper.php')) {
            $this->components['array_helper'] = new \WeCoza\Agents\Helpers\ArrayHelper();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Helpers/StringHelper.php')) {
            $this->components['string_helper'] = new \WeCoza\Agents\Helpers\StringHelper();
        }
    }

    /**
     * Load form classes
     *
     * @since 1.0.0
     */
    private function load_form_classes() {
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Forms/AgentCaptureForm.php')) {
            $this->components['capture_form'] = new \WeCoza\Agents\Forms\AgentCaptureForm();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Forms/FormValidator.php')) {
            $this->components['form_validator'] = new \WeCoza\Agents\Forms\FormValidator();
        }
    }

    /**
     * Load model classes
     *
     * @since 1.0.0
     */
    private function load_model_classes() {
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Models/Agent.php')) {
            $this->components['agent_model'] = new \WeCoza\Agents\Models\Agent();
        }
    }

    /**
     * Define locale for internationalization
     *
     * @since 1.0.0
     */
    private function set_locale() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Load the plugin text domain
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname(plugin_basename(WECOZA_AGENTS_PLUGIN_FILE)) . '/languages/'
        );
    }

    /**
     * Register all admin hooks
     *
     * @since 1.0.0
     */
    private function define_admin_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }

    /**
     * Register all public hooks
     *
     * @since 1.0.0
     */
    private function define_public_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_action('init', array($this, 'init'));
    }

    /**
     * Register shortcodes
     *
     * @since 1.0.0
     */
    private function register_shortcodes() {
        add_action('init', array($this, 'add_shortcodes'));
    }

    /**
     * Add shortcodes
     *
     * @since 1.0.0
     */
    public function add_shortcodes() {
        // Register capture shortcode
        if (isset($this->components['capture_shortcode'])) {
            add_shortcode('wecoza_capture_agents', array($this->components['capture_shortcode'], 'render'));
        }
        
        // Register display shortcode
        if (isset($this->components['display_shortcode'])) {
            add_shortcode('wecoza_display_agents', array($this->components['display_shortcode'], 'render'));
        }
    }

    /**
     * Initialize plugin
     *
     * @since 1.0.0
     */
    public function init() {
        // Initialize components that need WordPress to be loaded
        do_action('wecoza_agents_init');
    }

    /**
     * Admin initialization
     *
     * @since 1.0.0
     */
    public function admin_init() {
        // Register settings
        $this->register_settings();
        
        // Check for updates
        $this->check_version();
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     */
    private function register_settings() {
        register_setting(
            'wecoza_agents_settings_group',
            'wecoza_agents_settings',
            array($this, 'validate_settings')
        );
    }

    /**
     * Validate settings
     *
     * @since 1.0.0
     * @param array $input Input data
     * @return array Validated data
     */
    public function validate_settings($input) {
        $validated = array();
        
        // Validate each setting
        if (isset($input['enable_debug'])) {
            $validated['enable_debug'] = (bool) $input['enable_debug'];
        }
        
        return $validated;
    }

    /**
     * Check plugin version and run upgrades if needed
     *
     * @since 1.0.0
     */
    private function check_version() {
        $current_version = get_option('wecoza_agents_version', '0.0.0');
        
        if (version_compare($current_version, $this->version, '<')) {
            $this->upgrade($current_version, $this->version);
            update_option('wecoza_agents_version', $this->version);
        }
    }

    /**
     * Run upgrade routines
     *
     * @since 1.0.0
     * @param string $old_version Previous version
     * @param string $new_version New version
     */
    private function upgrade($old_version, $new_version) {
        // Run version-specific upgrades
        do_action('wecoza_agents_upgrade', $old_version, $new_version);
    }

    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WeCoza Agents', 'wecoza-agents-plugin'),
            __('Agents', 'wecoza-agents-plugin'),
            'manage_options',
            'wecoza-agents',
            array($this, 'admin_page'),
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'wecoza-agents',
            __('All Agents', 'wecoza-agents-plugin'),
            __('All Agents', 'wecoza-agents-plugin'),
            'manage_options',
            'wecoza-agents',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'wecoza-agents',
            __('Add New Agent', 'wecoza-agents-plugin'),
            __('Add New', 'wecoza-agents-plugin'),
            'manage_options',
            'wecoza-agents-add',
            array($this, 'admin_add_page')
        );
        
        add_submenu_page(
            'wecoza-agents',
            __('Settings', 'wecoza-agents-plugin'),
            __('Settings', 'wecoza-agents-plugin'),
            'manage_options',
            'wecoza-agents-settings',
            array($this, 'admin_settings_page')
        );
    }

    /**
     * Admin page content
     *
     * @since 1.0.0
     */
    public function admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('WeCoza Agents', 'wecoza-agents-plugin') . '</h1>';
        echo '<p>' . esc_html__('Manage your agents here.', 'wecoza-agents-plugin') . '</p>';
        echo '</div>';
    }

    /**
     * Admin add page content
     *
     * @since 1.0.0
     */
    public function admin_add_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Add New Agent', 'wecoza-agents-plugin') . '</h1>';
        echo do_shortcode('[wecoza_capture_agents]');
        echo '</div>';
    }

    /**
     * Admin settings page content
     *
     * @since 1.0.0
     */
    public function admin_settings_page() {
        include WECOZA_AGENTS_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Enqueue admin styles
     *
     * @since 1.0.0
     * @param string $hook Page hook
     */
    public function enqueue_admin_styles($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'wecoza-agents') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wecoza-agents-admin',
            WECOZA_AGENTS_CSS_URL . 'admin.css',
            array(),
            $this->version
        );
    }

    /**
     * Enqueue admin scripts
     *
     * @since 1.0.0
     * @param string $hook Page hook
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'wecoza-agents') === false) {
            return;
        }
        
        wp_enqueue_script(
            'wecoza-agents-admin',
            WECOZA_AGENTS_JS_URL . 'admin.js',
            array('jquery'),
            $this->version,
            true
        );
        
        wp_localize_script(
            'wecoza-agents-admin',
            'wecoza_agents_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wecoza_agents_admin'),
            )
        );
    }

    /**
     * Enqueue public styles
     *
     * @since 1.0.0
     */
    public function enqueue_public_styles() {
        // Check if our shortcodes are present
        if (!$this->has_shortcode()) {
            return;
        }
        
        // Enqueue main styles
        wp_enqueue_style(
            'wecoza-agents',
            WECOZA_AGENTS_CSS_URL . 'agents-extracted.css',
            array(),
            $this->version
        );
        
        // Enqueue Select2 if needed
        wp_enqueue_style(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            array(),
            '4.1.0-rc.0'
        );
    }

    /**
     * Enqueue public scripts
     *
     * @since 1.0.0
     */
    public function enqueue_public_scripts() {
        // Check if our shortcodes are present
        if (!$this->has_shortcode()) {
            return;
        }
        
        // Enqueue Select2
        wp_enqueue_script(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            array('jquery'),
            '4.1.0-rc.0',
            true
        );
        
        // Enqueue main script
        wp_enqueue_script(
            'wecoza-agents',
            WECOZA_AGENTS_JS_URL . 'agents-app.js',
            array('jquery', 'select2'),
            $this->version,
            true
        );
        
        // Localize script
        wp_localize_script(
            'wecoza-agents',
            'agents_nonce',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('agents_nonce'),
                'uploads_url' => wp_upload_dir()['baseurl'],
                'is_admin' => current_user_can('manage_options'),
            )
        );
    }

    /**
     * Check if page has our shortcodes
     *
     * @since 1.0.0
     * @return bool
     */
    private function has_shortcode() {
        global $post;
        
        if (!is_a($post, 'WP_Post')) {
            return false;
        }
        
        return has_shortcode($post->post_content, 'wecoza_capture_agents') || 
               has_shortcode($post->post_content, 'wecoza_display_agents');
    }

    /**
     * Run the plugin
     *
     * @since 1.0.0
     */
    public function run() {
        // Plugin is initialized and ready to run
        do_action('wecoza_agents_loaded');
    }

    /**
     * Get plugin version
     *
     * @since 1.0.0
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get plugin slug
     *
     * @since 1.0.0
     * @return string
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    /**
     * Get text domain
     *
     * @since 1.0.0
     * @return string
     */
    public function get_text_domain() {
        return $this->text_domain;
    }

    /**
     * Get component
     *
     * @since 1.0.0
     * @param string $name Component name
     * @return mixed Component instance or null
     */
    public function get_component($name) {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }
}