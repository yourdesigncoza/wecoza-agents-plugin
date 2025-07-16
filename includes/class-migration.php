<?php
/**
 * Migration Handler
 *
 * Handles migration from theme to plugin and provides backward compatibility.
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
 * Migration class
 *
 * @since 1.0.0
 */
class Migration {

    /**
     * Instance
     *
     * @var Migration
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @since 1.0.0
     * @return Migration
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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Hook into theme functions to provide deprecation notices
        add_action('after_setup_theme', array($this, 'setup_deprecation_notices'), 20);
        
        // Provide backward compatibility
        add_action('init', array($this, 'setup_compatibility'), 5);
        
        // Admin notices
        add_action('admin_notices', array($this, 'migration_notices'));
    }

    /**
     * Setup deprecation notices
     *
     * @since 1.0.0
     */
    public function setup_deprecation_notices() {
        // Check if theme functions exist and wrap them
        if (function_exists('load_agents_files')) {
            // The function exists, so theme hasn't been updated yet
            add_action('init', array($this, 'deprecate_theme_functions'), 1);
        }
    }

    /**
     * Deprecate theme functions
     *
     * @since 1.0.0
     */
    public function deprecate_theme_functions() {
        // Log deprecation when theme functions are called
        if (did_action('load_agents_files')) {
            _deprecated_function('load_agents_files', '1.0.0', 'WeCoza Agents Plugin auto-loads all files');
        }
        
        if (has_action('wp_enqueue_scripts', 'enqueue_agents_assets')) {
            _deprecated_function('enqueue_agents_assets', '1.0.0', 'WeCoza Agents Plugin handles asset loading automatically');
        }
    }

    /**
     * Setup backward compatibility
     *
     * @since 1.0.0
     */
    public function setup_compatibility() {
        // Create compatibility functions if they don't exist
        $this->create_compat_functions();
        
        // Remove theme hooks if plugin is active
        $this->remove_theme_hooks();
    }

    /**
     * Create compatibility functions
     *
     * @since 1.0.0
     */
    private function create_compat_functions() {
        // Define theme functions if they don't exist (for backward compatibility)
        if (!function_exists('wecoza_agents_validate_sa_id')) {
            /**
             * Backward compatibility wrapper
             * @deprecated 1.0.0 Use wecoza_agents_validate_sa_id() from plugin
             */
            function wecoza_agents_validate_sa_id($id_number) {
                _deprecated_function(__FUNCTION__, '1.0.0', 'wecoza_agents_validate_sa_id (provided by WeCoza Agents Plugin)');
                if (function_exists('wecoza_agents_validate_sa_id')) {
                    return wecoza_agents_validate_sa_id($id_number);
                }
                return array('valid' => false, 'message' => 'Validation function not available');
            }
        }
        
        if (!function_exists('wecoza_agents_validate_passport')) {
            /**
             * Backward compatibility wrapper
             * @deprecated 1.0.0 Use wecoza_agents_validate_passport() from plugin
             */
            function wecoza_agents_validate_passport($passport) {
                _deprecated_function(__FUNCTION__, '1.0.0', 'wecoza_agents_validate_passport (provided by WeCoza Agents Plugin)');
                if (function_exists('wecoza_agents_validate_passport')) {
                    return wecoza_agents_validate_passport($passport);
                }
                return array('valid' => false, 'message' => 'Validation function not available');
            }
        }
    }

    /**
     * Remove theme hooks
     *
     * @since 1.0.0
     */
    private function remove_theme_hooks() {
        // Remove theme asset loading if plugin is handling it
        if (has_action('wp_enqueue_scripts', 'enqueue_agents_assets')) {
            remove_action('wp_enqueue_scripts', 'enqueue_agents_assets');
            
            // Add our own hook with higher priority to ensure assets are loaded
            add_action('wp_enqueue_scripts', array($this, 'enqueue_migrated_assets'), 5);
        }
    }

    /**
     * Enqueue migrated assets
     *
     * @since 1.0.0
     */
    public function enqueue_migrated_assets() {
        // This is handled by the shortcode classes now
        // But we'll add a notice for developers
        if (WP_DEBUG) {
            add_action('wp_footer', function() {
                echo '<!-- WeCoza Agents: Assets are now loaded conditionally by shortcodes -->';
            });
        }
    }

    /**
     * Display migration notices
     *
     * @since 1.0.0
     */
    public function migration_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if theme files still exist
        $theme_path = get_stylesheet_directory();
        $theme_files = array(
            $theme_path . '/assets/agents/agents-capture-shortcode.php',
            $theme_path . '/assets/agents/agents-display-shortcode.php',
            $theme_path . '/assets/agents/agents-functions.php',
        );
        
        $existing_files = array_filter($theme_files, 'file_exists');
        
        if (!empty($existing_files) && !get_option('wecoza_agents_migration_notice_dismissed')) {
            ?>
            <div class="notice notice-warning is-dismissible" data-notice="wecoza-agents-migration">
                <p>
                    <strong><?php esc_html_e('WeCoza Agents Plugin Migration', 'wecoza-agents-plugin'); ?></strong>
                </p>
                <p>
                    <?php esc_html_e('The WeCoza Agents functionality has been moved to a plugin. The following theme files can be safely removed:', 'wecoza-agents-plugin'); ?>
                </p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <?php foreach ($existing_files as $file) : ?>
                        <li><code><?php echo esc_html(str_replace(ABSPATH, '', $file)); ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <p>
                    <?php esc_html_e('All functionality is preserved in the plugin. Please update your theme to remove these files.', 'wecoza-agents-plugin'); ?>
                </p>
                <p>
                    <a href="#" class="button button-primary" onclick="dismissWeCozaAgentsMigrationNotice(); return false;">
                        <?php esc_html_e('Dismiss this notice', 'wecoza-agents-plugin'); ?>
                    </a>
                </p>
            </div>
            <script>
            function dismissWeCozaAgentsMigrationNotice() {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_wecoza_agents_migration_notice',
                    nonce: '<?php echo wp_create_nonce('dismiss_migration_notice'); ?>'
                });
                jQuery('[data-notice="wecoza-agents-migration"]').fadeOut();
            }
            </script>
            <?php
        }
    }

    /**
     * Check if migration is needed
     *
     * @since 1.0.0
     * @return bool Whether migration is needed
     */
    public function needs_migration() {
        // Check if theme files exist
        $theme_path = get_stylesheet_directory();
        $theme_files = array(
            '/assets/agents/agents-capture-shortcode.php',
            '/assets/agents/agents-display-shortcode.php',
        );
        
        foreach ($theme_files as $file) {
            if (file_exists($theme_path . $file)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get migration status
     *
     * @since 1.0.0
     * @return array Migration status
     */
    public function get_migration_status() {
        $status = array(
            'plugin_active' => true,
            'theme_files_exist' => $this->needs_migration(),
            'database_compatible' => true, // We're using the same database
            'shortcodes_registered' => shortcode_exists('wecoza_capture_agents') && shortcode_exists('wecoza_display_agents'),
            'assets_loaded' => wp_script_is('wecoza-agents', 'registered'),
        );
        
        $status['fully_migrated'] = !$status['theme_files_exist'];
        
        return $status;
    }

    /**
     * Log migration event
     *
     * @since 1.0.0
     * @param string $event Event description
     * @param array $data Event data
     */
    public function log_migration_event($event, $data = array()) {
        if (!WP_DEBUG_LOG) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'data' => $data,
        );
        
        error_log('[WeCoza Agents Migration] ' . json_encode($log_entry));
    }
}

// Initialize migration handler
Migration::get_instance();