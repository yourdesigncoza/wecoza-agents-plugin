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
            // Load DatabaseLogger first if it exists (dependency of DatabaseService)
            if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Database/DatabaseLogger.php')) {
                require_once WECOZA_AGENTS_SRC_DIR . 'Database/DatabaseLogger.php';
            }
            require_once WECOZA_AGENTS_SRC_DIR . 'Database/DatabaseService.php';
            $this->components['database'] = \WeCoza\Agents\Database\DatabaseService::getInstance();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Database/AgentQueries.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Database/AgentQueries.php';
            $this->components['agent_queries'] = new \WeCoza\Agents\Database\AgentQueries();
        }
    }

    /**
     * Load shortcode classes
     *
     * @since 1.0.0
     */
    private function load_shortcode_classes() {
        // Load AbstractShortcode first (base class)
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Shortcodes/AbstractShortcode.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Shortcodes/AbstractShortcode.php';
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Shortcodes/CaptureAgentShortcode.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Shortcodes/CaptureAgentShortcode.php';
            $this->components['capture_shortcode'] = new \WeCoza\Agents\Shortcodes\CaptureAgentShortcode();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Shortcodes/DisplayAgentShortcode.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Shortcodes/DisplayAgentShortcode.php';
            $this->components['display_shortcode'] = new \WeCoza\Agents\Shortcodes\DisplayAgentShortcode();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Shortcodes/SingleAgentShortcode.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Shortcodes/SingleAgentShortcode.php';
            $this->components['single_agent_shortcode'] = new \WeCoza\Agents\Shortcodes\SingleAgentShortcode();
        }
    }

    /**
     * Load helper classes
     *
     * @since 1.0.0
     */
    private function load_helper_classes() {
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Helpers/ValidationHelper.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Helpers/ValidationHelper.php';
            $this->components['validation_helper'] = new \WeCoza\Agents\Helpers\ValidationHelper();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Helpers/ArrayHelper.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Helpers/ArrayHelper.php';
            $this->components['array_helper'] = new \WeCoza\Agents\Helpers\ArrayHelper();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Helpers/StringHelper.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Helpers/StringHelper.php';
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
            require_once WECOZA_AGENTS_SRC_DIR . 'Forms/AgentCaptureForm.php';
            $this->components['capture_form'] = new \WeCoza\Agents\Forms\AgentCaptureForm();
        }
        
        if (file_exists(WECOZA_AGENTS_SRC_DIR . 'Forms/FormValidator.php')) {
            require_once WECOZA_AGENTS_SRC_DIR . 'Forms/FormValidator.php';
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
            require_once WECOZA_AGENTS_SRC_DIR . 'Models/Agent.php';
            $this->components['agent_model'] = new \WeCoza\Agents\Models\Agent();
        }
    }

    /**
     * Define locale for internationalization
     *
     * @since 1.0.0
     */
    private function set_locale() {
        // Textdomain loading is now handled in main plugin file
        // to ensure it loads before any translation functions are called
    }


    /**
     * Register all public hooks
     *
     * @since 1.0.0
     */
    private function define_public_hooks() {
       // add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
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
        
        // Register single agent shortcode
        if (isset($this->components['single_agent_shortcode'])) {
            add_shortcode('wecoza_single_agent', array($this->components['single_agent_shortcode'], 'render'));
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
        
        // Check for Bootstrap availability
        $this->check_bootstrap_availability();
    }

    /**
     * Check if Bootstrap 5 is available
     *
     * @since 1.0.0
     */
    private function check_bootstrap_availability() {
        add_action('wp_enqueue_scripts', array($this, 'bootstrap_check_callback'), 999);
    }

    /**
     * Bootstrap check callback
     *
     * @since 1.0.0
     */
    public function bootstrap_check_callback() {
        if (!is_admin()) {
            return;
        }
        
        global $wp_styles;
        $bootstrap_found = false;
        
        if (isset($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                if (strpos($handle, 'bootstrap') !== false || 
                    (isset($style->src) && strpos($style->src, 'bootstrap') !== false)) {
                    $bootstrap_found = true;
                    break;
                }
            }
        }
        
        if (!$bootstrap_found) {
            add_action('admin_notices', array($this, 'bootstrap_admin_notice'));
        }
    }

    /**
     * Bootstrap admin notice
     *
     * @since 1.0.0
     */
    public function bootstrap_admin_notice() {
        ?>
        <div class="notice notice-warning">
            <p><?php echo esc_html__('WeCoza Agents Plugin: Bootstrap 5 CSS framework not detected. The plugin requires Bootstrap 5 for proper styling.', 'wecoza-agents-plugin'); ?></p>
        </div>
        <?php
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
     * Enqueue public styles
     *
     * @since 1.0.0
     */
    // public function enqueue_public_styles() {
    //     // Check if our shortcodes are present
    //     if (!$this->has_shortcode()) {
    //         return;
    //     }
        
    //     // Enqueue main styles
    //     wp_enqueue_style(
    //         'wecoza-agents',
    //         WECOZA_AGENTS_CSS_URL . 'agents-extracted.css',
    //         array(),
    //         $this->version
    //     );
    // }

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
        
        // Enqueue main script
        wp_enqueue_script(
            'wecoza-agents',
            WECOZA_AGENTS_JS_URL . 'agents-app.js',
            array('jquery'),
            WECOZA_AGENTS_VERSION,
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
               has_shortcode($post->post_content, 'wecoza_display_agents') ||
               has_shortcode($post->post_content, 'wecoza_single_agent');
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