<?php
/**
 * Plugin Constants
 *
 * Defines all constants used throughout the plugin.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Includes;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Constants class
 *
 * @since 1.0.0
 */
class Constants {

    /**
     * Define plugin constants
     *
     * @since 1.0.0
     */
    public static function define_constants() {
        // Version constants
        self::define('WECOZA_AGENTS_VERSION', '1.0.0');
        self::define('WECOZA_AGENTS_DB_VERSION', '1.0.0');
        
        // Path constants
        self::define('WECOZA_AGENTS_PLUGIN_FILE', dirname(dirname(__FILE__)) . '/wecoza-agents-plugin.php');
        self::define('WECOZA_AGENTS_PLUGIN_DIR', plugin_dir_path(self::get_constant('WECOZA_AGENTS_PLUGIN_FILE')));
        self::define('WECOZA_AGENTS_PLUGIN_URL', plugin_dir_url(self::get_constant('WECOZA_AGENTS_PLUGIN_FILE')));
        self::define('WECOZA_AGENTS_PLUGIN_BASENAME', plugin_basename(self::get_constant('WECOZA_AGENTS_PLUGIN_FILE')));
        
        // Directory constants
        self::define('WECOZA_AGENTS_INCLUDES_DIR', self::get_constant('WECOZA_AGENTS_PLUGIN_DIR') . 'includes/');
        self::define('WECOZA_AGENTS_SRC_DIR', self::get_constant('WECOZA_AGENTS_PLUGIN_DIR') . 'src/');
        self::define('WECOZA_AGENTS_TEMPLATES_DIR', self::get_constant('WECOZA_AGENTS_PLUGIN_DIR') . 'templates/');
        self::define('WECOZA_AGENTS_ASSETS_DIR', self::get_constant('WECOZA_AGENTS_PLUGIN_DIR') . 'assets/');
        self::define('WECOZA_AGENTS_LOGS_DIR', self::get_constant('WECOZA_AGENTS_PLUGIN_DIR') . 'logs/');
        
        // URL constants
        self::define('WECOZA_AGENTS_ASSETS_URL', self::get_constant('WECOZA_AGENTS_PLUGIN_URL') . 'assets/');
        self::define('WECOZA_AGENTS_CSS_URL', self::get_constant('WECOZA_AGENTS_ASSETS_URL') . 'css/');
        self::define('WECOZA_AGENTS_JS_URL', self::get_constant('WECOZA_AGENTS_ASSETS_URL') . 'js/');
        self::define('WECOZA_AGENTS_IMAGES_URL', self::get_constant('WECOZA_AGENTS_ASSETS_URL') . 'images/');
        
        // Requirement constants
        self::define('WECOZA_AGENTS_MIN_PHP_VERSION', '7.4');
        self::define('WECOZA_AGENTS_MIN_WP_VERSION', '6.0');
        
        // Database constants
        self::define('WECOZA_AGENTS_TABLE_PREFIX', 'wecoza_');
        self::define('WECOZA_AGENTS_TABLE_AGENTS', self::get_constant('WECOZA_AGENTS_TABLE_PREFIX') . 'agents');
        self::define('WECOZA_AGENTS_TABLE_AGENT_META', self::get_constant('WECOZA_AGENTS_TABLE_PREFIX') . 'agent_meta');
        self::define('WECOZA_AGENTS_TABLE_AGENT_NOTES', self::get_constant('WECOZA_AGENTS_TABLE_PREFIX') . 'agent_notes');
        self::define('WECOZA_AGENTS_TABLE_AGENT_ABSENCES', self::get_constant('WECOZA_AGENTS_TABLE_PREFIX') . 'agent_absences');
        
        // Option name constants
        self::define('WECOZA_AGENTS_OPTIONS_PREFIX', 'wecoza_agents_');
        self::define('WECOZA_AGENTS_VERSION_OPTION', self::get_constant('WECOZA_AGENTS_OPTIONS_PREFIX') . 'version');
        self::define('WECOZA_AGENTS_DB_VERSION_OPTION', self::get_constant('WECOZA_AGENTS_OPTIONS_PREFIX') . 'db_version');
        self::define('WECOZA_AGENTS_SETTINGS_OPTION', self::get_constant('WECOZA_AGENTS_OPTIONS_PREFIX') . 'settings');
        
        // Capability constants
        self::define('WECOZA_AGENTS_CAPABILITY_MANAGE', 'manage_wecoza_agents');
        self::define('WECOZA_AGENTS_CAPABILITY_VIEW', 'view_wecoza_agents');
        self::define('WECOZA_AGENTS_CAPABILITY_EDIT', 'edit_wecoza_agents');
        self::define('WECOZA_AGENTS_CAPABILITY_DELETE', 'delete_wecoza_agents');
        
        // Cache constants
        self::define('WECOZA_AGENTS_CACHE_GROUP', 'wecoza_agents');
        self::define('WECOZA_AGENTS_CACHE_EXPIRATION', 3600); // 1 hour
        
        // Debug constants
        self::define('WECOZA_AGENTS_DEBUG', WP_DEBUG);
        self::define('WECOZA_AGENTS_DEBUG_LOG', WP_DEBUG_LOG);
    }
    
    /**
     * Define constant if not already defined
     *
     * @since 1.0.0
     * @param string $name Constant name
     * @param mixed $value Constant value
     */
    private static function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
    
    /**
     * Get constant value
     *
     * @since 1.0.0
     * @param string $name Constant name
     * @return mixed Constant value or null if not defined
     */
    public static function get_constant($name) {
        return defined($name) ? constant($name) : null;
    }
    
    /**
     * Check if constant is defined
     *
     * @since 1.0.0
     * @param string $name Constant name
     * @return bool True if defined
     */
    public static function has_constant($name) {
        return defined($name);
    }
    
    /**
     * Get all plugin constants
     *
     * @since 1.0.0
     * @return array Array of constant names and values
     */
    public static function get_all_constants() {
        $constants = array();
        $all_constants = get_defined_constants(true);
        
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $value) {
                if (strpos($name, 'WECOZA_AGENTS_') === 0) {
                    $constants[$name] = $value;
                }
            }
        }
        
        return $constants;
    }
}