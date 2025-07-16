<?php
/**
 * Plugin Name: WeCoza Agents Plugin
 * Plugin URI: https://wecoza.co.za/plugins/agents
 * Description: Comprehensive agent management system for WeCoza - manage agent profiles, qualifications, and assignments
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: WeCoza Development Team
 * Author URI: https://wecoza.co.za
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wecoza-agents-plugin
 * Domain Path: /languages
 *
 * @package WeCoza\Agents
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WECOZA_AGENTS_VERSION', '1.0.0');
define('WECOZA_AGENTS_PLUGIN_FILE', __FILE__);
define('WECOZA_AGENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOZA_AGENTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOZA_AGENTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Asset URL constants
define('WECOZA_AGENTS_ASSETS_URL', WECOZA_AGENTS_PLUGIN_URL . 'assets/');
define('WECOZA_AGENTS_CSS_URL', WECOZA_AGENTS_ASSETS_URL . 'css/');
define('WECOZA_AGENTS_JS_URL', WECOZA_AGENTS_ASSETS_URL . 'js/');
define('WECOZA_AGENTS_IMAGES_URL', WECOZA_AGENTS_ASSETS_URL . 'images/');

// Minimum requirements
define('WECOZA_AGENTS_MIN_PHP_VERSION', '7.4');
define('WECOZA_AGENTS_MIN_WP_VERSION', '6.0');

/**
 * Check minimum requirements before loading plugin
 */
function wecoza_agents_check_requirements() {
    $errors = array();

    // Check PHP version
    if (version_compare(PHP_VERSION, WECOZA_AGENTS_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            __('WeCoza Agents Plugin requires PHP %s or higher. You are running PHP %s.', 'wecoza-agents-plugin'),
            WECOZA_AGENTS_MIN_PHP_VERSION,
            PHP_VERSION
        );
    }

    // Check WordPress version
    global $wp_version;
    if (version_compare($wp_version, WECOZA_AGENTS_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            __('WeCoza Agents Plugin requires WordPress %s or higher. You are running WordPress %s.', 'wecoza-agents-plugin'),
            WECOZA_AGENTS_MIN_WP_VERSION,
            $wp_version
        );
    }

    // Check if Bootstrap 5 is available (parent theme dependency)
    add_action('wp_enqueue_scripts', function() use (&$errors) {
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
        
        if (!$bootstrap_found && is_admin()) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning">
                    <p><?php _e('WeCoza Agents Plugin: Bootstrap 5 CSS framework not detected. The plugin requires Bootstrap 5 for proper styling.', 'wecoza-agents-plugin'); ?></p>
                </div>
                <?php
            });
        }
    }, 999);

    return $errors;
}

/**
 * Display admin notice if requirements are not met
 */
function wecoza_agents_requirements_notice() {
    $errors = wecoza_agents_check_requirements();
    
    if (!empty($errors)) {
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e('WeCoza Agents Plugin cannot be activated:', 'wecoza-agents-plugin'); ?></strong></p>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        
        // Deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));
        
        // Hide the "Plugin activated" notice
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}
add_action('admin_notices', 'wecoza_agents_requirements_notice');

/**
 * Load plugin textdomain
 */
function wecoza_agents_load_textdomain() {
    load_plugin_textdomain(
        'wecoza-agents-plugin',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'wecoza_agents_load_textdomain');

/**
 * Initialize the plugin
 */
function wecoza_agents_init() {
    // Check requirements
    $errors = wecoza_agents_check_requirements();
    if (!empty($errors)) {
        return; // Don't initialize if requirements not met
    }

    // Load Composer autoloader if available
    if (file_exists(WECOZA_AGENTS_PLUGIN_DIR . 'vendor/autoload.php')) {
        require_once WECOZA_AGENTS_PLUGIN_DIR . 'vendor/autoload.php';
    }

    // Load core plugin files
    require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-plugin.php';

    // Initialize the main plugin class
    $plugin = \WeCoza\Agents\Plugin::get_instance();
    $plugin->run();
}
add_action('plugins_loaded', 'wecoza_agents_init', 20);

/**
 * Activation hook
 */
function wecoza_agents_activate() {
    // Check requirements before activation
    $errors = wecoza_agents_check_requirements();
    if (!empty($errors)) {
        wp_die(
            implode('<br>', $errors),
            __('Plugin Activation Error', 'wecoza-agents-plugin'),
            array('back_link' => true)
        );
    }

    require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-activator.php';
    \WeCoza\Agents\Activator::activate();
}
register_activation_hook(__FILE__, 'wecoza_agents_activate');

/**
 * Deactivation hook
 */
function wecoza_agents_deactivate() {
    require_once WECOZA_AGENTS_PLUGIN_DIR . 'includes/class-deactivator.php';
    \WeCoza\Agents\Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'wecoza_agents_deactivate');

/**
 * Add action links to plugin page
 */
function wecoza_agents_plugin_action_links($links) {
    $action_links = array(
        '<a href="' . admin_url('admin.php?page=wecoza-agents-settings') . '">' . __('Settings', 'wecoza-agents-plugin') . '</a>',
    );
    
    return array_merge($action_links, $links);
}
add_filter('plugin_action_links_' . WECOZA_AGENTS_PLUGIN_BASENAME, 'wecoza_agents_plugin_action_links');

/**
 * Add meta links to plugin page
 */
function wecoza_agents_plugin_meta_links($links, $file) {
    if ($file === WECOZA_AGENTS_PLUGIN_BASENAME) {
        $meta_links = array(
            '<a href="https://wecoza.co.za/docs/agents-plugin" target="_blank">' . __('Documentation', 'wecoza-agents-plugin') . '</a>',
            '<a href="https://wecoza.co.za/support" target="_blank">' . __('Support', 'wecoza-agents-plugin') . '</a>',
        );
        
        return array_merge($links, $meta_links);
    }
    
    return $links;
}
add_filter('plugin_row_meta', 'wecoza_agents_plugin_meta_links', 10, 2);