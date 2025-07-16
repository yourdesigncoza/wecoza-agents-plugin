<?php
/**
 * Plugin Deactivator
 *
 * Handles plugin deactivation tasks including cleanup and data preservation.
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
 * Deactivator class
 *
 * @since 1.0.0
 */
class Deactivator {

    /**
     * Deactivate the plugin
     *
     * Performs cleanup tasks but preserves user data and settings.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Clear transients
        self::clear_transients();
        
        // Clear cache
        self::clear_cache();
        
        // Clear rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        self::log_deactivation();
        
        // Note: We do NOT remove:
        // - Database tables (user data)
        // - Options (user settings)
        // - Capabilities (may be used by other plugins)
        // - Upload directories (user files)
        // These are only removed during uninstall
    }

    /**
     * Clear scheduled events
     *
     * @since 1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear daily cleanup
        $timestamp = wp_next_scheduled('wecoza_agents_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wecoza_agents_daily_cleanup');
        }
        
        // Clear weekly report
        $timestamp = wp_next_scheduled('wecoza_agents_weekly_report');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wecoza_agents_weekly_report');
        }
        
        // Clear any custom scheduled events
        self::clear_custom_events();
    }

    /**
     * Clear custom scheduled events
     *
     * @since 1.0.0
     */
    private static function clear_custom_events() {
        global $wpdb;
        
        // Get all cron events
        $cron = get_option('cron');
        
        if (!is_array($cron)) {
            return;
        }
        
        // Remove all events that start with 'wecoza_agents_'
        foreach ($cron as $timestamp => $events) {
            if (!is_array($events)) {
                continue;
            }
            
            foreach ($events as $hook => $event_data) {
                if (strpos($hook, 'wecoza_agents_') === 0) {
                    wp_unschedule_event($timestamp, $hook);
                }
            }
        }
    }

    /**
     * Clear transients
     *
     * @since 1.0.0
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Delete all transients with our prefix
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                OR option_name LIKE %s",
                $wpdb->esc_like('_transient_wecoza_agents_') . '%',
                $wpdb->esc_like('_transient_timeout_wecoza_agents_') . '%'
            )
        );
        
        // Delete activation flag
        delete_transient('wecoza_agents_activated');
        
        // Delete any update transients
        delete_transient('wecoza_agents_update_check');
        delete_transient('wecoza_agents_license_check');
    }

    /**
     * Clear cache
     *
     * @since 1.0.0
     */
    private static function clear_cache() {
        // Clear object cache if available
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group(WECOZA_AGENTS_CACHE_GROUP);
        } else {
            // Clear all cache as fallback
            wp_cache_flush();
        }
        
        // Clear any file-based cache
        self::clear_file_cache();
        
        // Clear third-party cache
        self::clear_third_party_cache();
    }

    /**
     * Clear file-based cache
     *
     * @since 1.0.0
     */
    private static function clear_file_cache() {
        $cache_dir = WECOZA_AGENTS_PLUGIN_DIR . 'cache/';
        
        if (!is_dir($cache_dir)) {
            return;
        }
        
        // Remove all files from cache directory
        $files = glob($cache_dir . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && $file !== $cache_dir . 'index.php') {
                @unlink($file);
            }
        }
    }

    /**
     * Clear third-party cache
     *
     * @since 1.0.0
     */
    private static function clear_third_party_cache() {
        // WP Super Cache
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        
        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        
        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
        
        // WP Fastest Cache
        if (class_exists('WpFastestCache') && method_exists('WpFastestCache', 'deleteCache')) {
            $wpfc = new \WpFastestCache();
            $wpfc->deleteCache();
        }
        
        // LiteSpeed Cache
        if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
            \LiteSpeed_Cache_API::purge_all();
        }
        
        // Autoptimize
        if (class_exists('autoptimizeCache') && method_exists('autoptimizeCache', 'clearall')) {
            \autoptimizeCache::clearall();
        }
    }

    /**
     * Log deactivation
     *
     * @since 1.0.0
     */
    private static function log_deactivation() {
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'version' => WECOZA_AGENTS_VERSION,
            'user_id' => get_current_user_id(),
            'reason' => 'manual_deactivation',
        );
        
        // Store deactivation data for potential debugging
        update_option('wecoza_agents_last_deactivation', $log_data);
        
        // Log to file if debug is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = sprintf(
                '[%s] Plugin deactivated - Version: %s, User: %d',
                $log_data['timestamp'],
                $log_data['version'],
                $log_data['user_id']
            );
            
            error_log($log_message);
        }
    }

    /**
     * Show deactivation notice
     *
     * Can be hooked to admin_notices to show a message after deactivation.
     *
     * @since 1.0.0
     */
    public static function deactivation_notice() {
        $screen = get_current_screen();
        
        if ($screen->id !== 'plugins') {
            return;
        }
        
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php
                printf(
                    __('WeCoza Agents Plugin has been deactivated. Your data has been preserved and will be available when you reactivate the plugin. %sLeave feedback%s', 'wecoza-agents-plugin'),
                    '<a href="https://wecoza.co.za/feedback" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}