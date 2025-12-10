<?php
/**
 * Uninstall Script
 *
 * This file is executed when the plugin is deleted via WordPress admin.
 * It performs complete cleanup including removal of all plugin data.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load plugin constants if not already loaded
if (!defined('WECOZA_AGENTS_VERSION')) {
    define('WECOZA_AGENTS_VERSION', '1.0.0');
    define('WECOZA_AGENTS_OPTIONS_PREFIX', 'wecoza_agents_');
    define('WECOZA_AGENTS_TABLE_PREFIX', 'wecoza_');
    define('WECOZA_AGENTS_CACHE_GROUP', 'wecoza_agents');
}

/**
 * Main uninstall function
 */
function wecoza_agents_uninstall() {
    // Check user capabilities
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    // Get uninstall option
    $uninstall_data = get_option('wecoza_agents_uninstall_settings', array());
    $remove_all_data = isset($uninstall_data['remove_all_data']) ? $uninstall_data['remove_all_data'] : true;
    
    if (!$remove_all_data) {
        // User chose to preserve data
        return;
    }
    
    // Remove database tables
    wecoza_agents_remove_tables();
    
    // Remove options
    wecoza_agents_remove_options();
    
    // Remove user capabilities
    wecoza_agents_remove_capabilities();
    
    // Remove uploaded files
    wecoza_agents_remove_uploads();
    
    // Clear all caches
    wecoza_agents_clear_all_caches();
    
    // Remove scheduled events
    wecoza_agents_remove_scheduled_events();
    
    // Clean up user meta
    wecoza_agents_clean_user_meta();
    
    // Clean up post meta
    wecoza_agents_clean_post_meta();
    
    // Remove transients
    wecoza_agents_remove_transients();
    
    // Log uninstall
    wecoza_agents_log_uninstall();
}

/**
 * Remove database tables
 */
function wecoza_agents_remove_tables() {
    global $wpdb;
    
    // Check if using PostgreSQL
    $db_type = wecoza_agents_get_database_type();
    
    if ($db_type === 'postgresql') {
        wecoza_agents_remove_postgresql_tables();
    } else {
        wecoza_agents_remove_mysql_tables();
    }
}

/**
 * Get database type
 */
function wecoza_agents_get_database_type() {
    $pg_host = get_option('wecoza_postgres_host');
    $pg_pass = get_option('wecoza_postgres_password');
    
    if ($pg_host && $pg_pass) {
        return 'postgresql';
    }
    
    return 'mysql';
}

/**
 * Remove PostgreSQL tables
 */
function wecoza_agents_remove_postgresql_tables() {
    try {
        // Get PostgreSQL credentials
        $pg_host = get_option('wecoza_postgres_host', '');
        $pg_port = get_option('wecoza_postgres_port', '');
        $pg_dbname = get_option('wecoza_postgres_dbname', '');
        $pg_user = get_option('wecoza_postgres_user', '');
        $pg_pass = get_option('wecoza_postgres_password', '');
        
        // Create PDO connection
        $dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname";
        $pdo = new PDO($dsn, $pg_user, $pg_pass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ));
        
        // Drop tables in correct order (due to foreign keys)
        $tables = array(
            'agent_absences',
            'agent_notes',
            'agent_meta',
            'agents'
        );
        
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS $table CASCADE");
        }
        
    } catch (Exception $e) {
        error_log('WeCoza Agents Uninstall: Failed to remove PostgreSQL tables - ' . $e->getMessage());
    }
}

/**
 * Remove MySQL tables
 */
function wecoza_agents_remove_mysql_tables() {
    global $wpdb;
    
    // Tables to remove
    $tables = array(
        $wpdb->prefix . 'wecoza_agent_absences',
        $wpdb->prefix . 'wecoza_agent_notes',
        $wpdb->prefix . 'wecoza_agent_meta',
        $wpdb->prefix . 'wecoza_agents'
    );
    
    // Drop each table
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}

/**
 * Remove all plugin options
 */
function wecoza_agents_remove_options() {
    global $wpdb;
    
    // Remove all options with our prefix
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like(WECOZA_AGENTS_OPTIONS_PREFIX) . '%'
        )
    );
    
    // Remove specific options that don't use prefix
    $specific_options = array(
        'wecoza_postgres_host',
        'wecoza_postgres_port',
        'wecoza_postgres_dbname',
        'wecoza_postgres_user',
        'wecoza_postgres_password',
    );
    
    foreach ($specific_options as $option) {
        delete_option($option);
    }
}

/**
 * Remove capabilities
 */
function wecoza_agents_remove_capabilities() {
    // Get all roles
    global $wp_roles;
    
    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }
    
    // Capabilities to remove
    $capabilities = array(
        'manage_wecoza_agents',
        'view_wecoza_agents',
        'edit_wecoza_agents',
        'delete_wecoza_agents'
    );
    
    // Remove from all roles
    foreach ($wp_roles->roles as $role_name => $role_info) {
        $role = get_role($role_name);
        
        if (!$role) {
            continue;
        }
        
        foreach ($capabilities as $cap) {
            $role->remove_cap($cap);
        }
    }
}

/**
 * Remove uploaded files
 */
function wecoza_agents_remove_uploads() {
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/wecoza-agents';
    
    if (is_dir($plugin_upload_dir)) {
        wecoza_agents_recursive_rmdir($plugin_upload_dir);
    }
}

/**
 * Recursively remove directory
 */
function wecoza_agents_recursive_rmdir($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            wecoza_agents_recursive_rmdir($path);
        } else {
            @unlink($path);
        }
    }
    
    @rmdir($dir);
}

/**
 * Clear all caches
 */
function wecoza_agents_clear_all_caches() {
    // WordPress object cache
    wp_cache_flush();
    
    // Delete cache group if supported
    if (function_exists('wp_cache_delete_group')) {
        wp_cache_delete_group(WECOZA_AGENTS_CACHE_GROUP);
    }
}

/**
 * Remove scheduled events
 */
function wecoza_agents_remove_scheduled_events() {
    $cron = get_option('cron');
    
    if (!is_array($cron)) {
        return;
    }
    
    // Remove all events with our prefix
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
 * Clean user meta
 */
function wecoza_agents_clean_user_meta() {
    global $wpdb;
    
    // Remove all user meta with our prefix
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like('wecoza_agents_') . '%'
        )
    );
}

/**
 * Clean post meta
 */
function wecoza_agents_clean_post_meta() {
    global $wpdb;
    
    // Remove all post meta with our prefix
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like('_wecoza_agents_') . '%'
        )
    );
}

/**
 * Remove transients
 */
function wecoza_agents_remove_transients() {
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
}

/**
 * Log uninstall
 */
function wecoza_agents_log_uninstall() {
    // Send anonymous uninstall notice if allowed
    if (get_option('wecoza_agents_allow_tracking', false)) {
        $data = array(
            'url' => home_url(),
            'version' => WECOZA_AGENTS_VERSION,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'multisite' => is_multisite(),
            'timestamp' => current_time('timestamp')
        );
        
        wp_remote_post('https://wecoza.co.za/api/plugin-uninstall', array(
            'body' => $data,
            'timeout' => 5,
            'blocking' => false
        ));
    }
}

// Execute uninstall
wecoza_agents_uninstall();