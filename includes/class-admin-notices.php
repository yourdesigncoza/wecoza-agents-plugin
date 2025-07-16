<?php
/**
 * Admin Notices
 *
 * Handles all admin notices for the WeCoza Agents Plugin including deprecation warnings.
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
 * Admin Notices class
 *
 * @since 1.0.0
 */
class AdminNotices {

    /**
     * Instance
     *
     * @var AdminNotices
     */
    private static $instance = null;

    /**
     * Notice types
     *
     * @var array
     */
    private $notice_types = array(
        'success' => 'notice-success',
        'error' => 'notice-error',
        'warning' => 'notice-warning',
        'info' => 'notice-info'
    );

    /**
     * Get instance
     *
     * @since 1.0.0
     * @return AdminNotices
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
        add_action('admin_notices', array($this, 'display_admin_notices'));
        add_action('wp_ajax_wecoza_agents_dismiss_notice', array($this, 'dismiss_notice'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Display admin notices
     *
     * @since 1.0.0
     */
    public function display_admin_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Plugin activation notice
        $this->show_plugin_activation_notice();
        
        // Theme deprecation notice
        $this->show_theme_deprecation_notice();
        
        // File cleanup notice
        $this->show_file_cleanup_notice();
        
        // Performance optimization notice
        $this->show_performance_notice();
    }

    /**
     * Show plugin activation notice
     *
     * @since 1.0.0
     */
    private function show_plugin_activation_notice() {
        if (get_option('wecoza_agents_activation_notice_dismissed')) {
            return;
        }

        $message = sprintf(
            '<strong>%s</strong><br>%s<br><a href="%s" class="button button-primary">%s</a> <a href="#" class="wecoza-agents-dismiss-notice" data-notice="activation">%s</a>',
            __('WeCoza Agents Plugin Activated!', 'wecoza-agents-plugin'),
            __('The agent management functionality has been successfully migrated to a plugin. All existing shortcodes and functionality remain the same.', 'wecoza-agents-plugin'),
            admin_url('admin.php?page=wecoza-agents-settings'),
            __('Configure Plugin', 'wecoza-agents-plugin'),
            __('Dismiss', 'wecoza-agents-plugin')
        );

        $this->display_notice($message, 'success', true);
    }

    /**
     * Show theme deprecation notice
     *
     * @since 1.0.0
     */
    private function show_theme_deprecation_notice() {
        if (get_option('wecoza_agents_theme_deprecation_dismissed')) {
            return;
        }

        $theme_files = $this->get_deprecated_theme_files();
        
        if (empty($theme_files)) {
            return;
        }

        $message = sprintf(
            '<strong>%s</strong><br>%s<br><ul style="margin-left: 20px;">%s</ul>%s<br><a href="#" class="wecoza-agents-dismiss-notice" data-notice="theme_deprecation">%s</a>',
            __('Theme Agent Files Deprecated', 'wecoza-agents-plugin'),
            __('The following theme files are deprecated and can be safely removed:', 'wecoza-agents-plugin'),
            implode('', array_map(function($file) {
                return '<li><code>' . esc_html($file) . '</code></li>';
            }, $theme_files)),
            __('These files are no longer needed as the functionality has been moved to the plugin.', 'wecoza-agents-plugin'),
            __('Dismiss', 'wecoza-agents-plugin')
        );

        $this->display_notice($message, 'warning', true);
    }

    /**
     * Show file cleanup notice
     *
     * @since 1.0.0
     */
    private function show_file_cleanup_notice() {
        if (get_option('wecoza_agents_cleanup_notice_dismissed')) {
            return;
        }

        $deprecation_stats = DeprecationLogger::get_instance()->get_deprecation_stats();
        
        if (empty($deprecation_stats['total_entries'])) {
            return;
        }

        $message = sprintf(
            '<strong>%s</strong><br>%s<br>%s<br><a href="#" class="wecoza-agents-dismiss-notice" data-notice="cleanup">%s</a>',
            __('Agent File Cleanup Recommended', 'wecoza-agents-plugin'),
            sprintf(
                __('We\'ve detected %d deprecated file access events. This suggests theme files are still being loaded.', 'wecoza-agents-plugin'),
                $deprecation_stats['total_entries']
            ),
            __('For optimal performance, please remove the deprecated theme files listed in the previous notice.', 'wecoza-agents-plugin'),
            __('Dismiss', 'wecoza-agents-plugin')
        );

        $this->display_notice($message, 'info', true);
    }

    /**
     * Show performance optimization notice
     *
     * @since 1.0.0
     */
    private function show_performance_notice() {
        if (get_option('wecoza_agents_performance_notice_dismissed')) {
            return;
        }

        // Only show after plugin has been active for a while
        $activation_time = get_option('wecoza_agents_activation_time', time());
        if ((time() - $activation_time) < 604800) { // 7 days
            return;
        }

        $message = sprintf(
            '<strong>%s</strong><br>%s<br><strong>%s:</strong><ul style="margin-left: 20px;"><li>%s</li><li>%s</li><li>%s</li></ul><a href="#" class="wecoza-agents-dismiss-notice" data-notice="performance">%s</a>',
            __('Performance Optimization Available', 'wecoza-agents-plugin'),
            __('Now that the WeCoza Agents Plugin has been active for a week, consider these optimizations:', 'wecoza-agents-plugin'),
            __('Recommended actions', 'wecoza-agents-plugin'),
            __('Remove deprecated theme files to reduce file system overhead', 'wecoza-agents-plugin'),
            __('Enable object caching for better database performance', 'wecoza-agents-plugin'),
            __('Consider using the plugin\'s conditional asset loading feature', 'wecoza-agents-plugin'),
            __('Dismiss', 'wecoza-agents-plugin')
        );

        $this->display_notice($message, 'info', true);
    }

    /**
     * Display a notice
     *
     * @since 1.0.0
     * @param string $message Notice message
     * @param string $type Notice type (success, error, warning, info)
     * @param bool $dismissible Whether notice is dismissible
     */
    private function display_notice($message, $type = 'info', $dismissible = false) {
        $class = $this->notice_types[$type] ?? 'notice-info';
        $dismissible_class = $dismissible ? ' is-dismissible' : '';
        
        printf(
            '<div class="notice %s%s"><p>%s</p></div>',
            esc_attr($class),
            esc_attr($dismissible_class),
            $message
        );
    }

    /**
     * Get deprecated theme files
     *
     * @since 1.0.0
     * @return array List of deprecated theme files
     */
    private function get_deprecated_theme_files() {
        $theme_path = get_stylesheet_directory();
        $deprecated_files = array();
        
        $potential_files = array(
            '/assets/agents/agents-capture-shortcode.php',
            '/assets/agents/agents-display-shortcode.php', 
            '/assets/agents/agents-functions.php',
            '/assets/agents/js/agents-app.js',
            '/assets/agents/agents-extracted.css',
            '/app/Controllers/AgentController.php'
        );
        
        foreach ($potential_files as $file) {
            if (file_exists($theme_path . $file)) {
                $deprecated_files[] = str_replace($theme_path, '', $theme_path . $file);
            }
        }
        
        return $deprecated_files;
    }

    /**
     * Handle AJAX notice dismissal
     *
     * @since 1.0.0
     */
    public function dismiss_notice() {
        check_ajax_referer('wecoza_agents_dismiss_notice', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wecoza-agents-plugin'));
        }
        
        $notice_type = sanitize_text_field($_POST['notice_type'] ?? '');
        
        switch ($notice_type) {
            case 'activation':
                update_option('wecoza_agents_activation_notice_dismissed', true);
                break;
            case 'theme_deprecation':
                update_option('wecoza_agents_theme_deprecation_dismissed', true);
                break;
            case 'cleanup':
                update_option('wecoza_agents_cleanup_notice_dismissed', true);
                break;
            case 'performance':
                update_option('wecoza_agents_performance_notice_dismissed', true);
                break;
        }
        
        wp_send_json_success();
    }

    /**
     * Enqueue admin scripts
     *
     * @since 1.0.0
     */
    public function enqueue_admin_scripts() {
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                $(".wecoza-agents-dismiss-notice").on("click", function(e) {
                    e.preventDefault();
                    var notice = $(this).data("notice");
                    var $notice = $(this).closest(".notice");
                    
                    $.post(ajaxurl, {
                        action: "wecoza_agents_dismiss_notice",
                        notice_type: notice,
                        nonce: "' . wp_create_nonce('wecoza_agents_dismiss_notice') . '"
                    }, function(response) {
                        if (response.success) {
                            $notice.fadeOut();
                        }
                    });
                });
            });
        ');
    }

    /**
     * Add a custom notice
     *
     * @since 1.0.0
     * @param string $message Notice message
     * @param string $type Notice type
     * @param bool $dismissible Whether notice is dismissible
     */
    public function add_notice($message, $type = 'info', $dismissible = false) {
        add_action('admin_notices', function() use ($message, $type, $dismissible) {
            $this->display_notice($message, $type, $dismissible);
        });
    }

    /**
     * Add a transient notice
     *
     * @since 1.0.0
     * @param string $message Notice message
     * @param string $type Notice type
     * @param int $expiry Expiry time in seconds
     */
    public function add_transient_notice($message, $type = 'info', $expiry = 300) {
        set_transient('wecoza_agents_notice_' . md5($message), array(
            'message' => $message,
            'type' => $type,
            'expiry' => time() + $expiry
        ), $expiry);
    }

    /**
     * Display transient notices
     *
     * @since 1.0.0
     */
    public function display_transient_notices() {
        global $wpdb;
        
        $transients = $wpdb->get_results(
            "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_transient_wecoza_agents_notice_%'"
        );
        
        foreach ($transients as $transient) {
            $notice_data = maybe_unserialize($transient->option_value);
            
            if (is_array($notice_data) && isset($notice_data['message'])) {
                $this->display_notice($notice_data['message'], $notice_data['type']);
                
                // Delete expired transients
                if (time() > $notice_data['expiry']) {
                    $transient_name = str_replace('_transient_', '', $transient->option_name);
                    delete_transient($transient_name);
                }
            }
        }
    }

    /**
     * Reset all notices
     *
     * @since 1.0.0
     */
    public function reset_all_notices() {
        delete_option('wecoza_agents_activation_notice_dismissed');
        delete_option('wecoza_agents_theme_deprecation_dismissed');
        delete_option('wecoza_agents_cleanup_notice_dismissed');
        delete_option('wecoza_agents_performance_notice_dismissed');
        delete_option('wecoza_agents_migration_notice_dismissed');
        delete_option('wecoza_agents_deprecation_notice_dismissed');
    }
}

// Initialize admin notices
AdminNotices::get_instance();