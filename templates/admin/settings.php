<?php
/**
 * Admin Settings Page Template
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['wecoza_agents_settings_nonce'], 'wecoza_agents_settings')) {
    // Process form data
    $settings = array(
        'enable_debug' => isset($_POST['enable_debug']) ? (bool) $_POST['enable_debug'] : false,
        'enable_deprecation_logging' => isset($_POST['enable_deprecation_logging']) ? (bool) $_POST['enable_deprecation_logging'] : true,
        'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? (bool) $_POST['enable_email_notifications'] : false,
        'notification_email' => sanitize_email($_POST['notification_email'] ?? ''),
        'enable_asset_minification' => isset($_POST['enable_asset_minification']) ? (bool) $_POST['enable_asset_minification'] : false,
        'enable_conditional_loading' => isset($_POST['enable_conditional_loading']) ? (bool) $_POST['enable_conditional_loading'] : true,
        'cache_expiry_hours' => intval($_POST['cache_expiry_hours'] ?? 24),
        'max_log_file_size' => intval($_POST['max_log_file_size'] ?? 10),
    );
    
    // Update settings
    update_option('wecoza_agents_settings', $settings);
    
    // Show success message
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'wecoza-agents-plugin') . '</p></div>';
}

// Get current settings
$settings = get_option('wecoza_agents_settings', array());
$defaults = array(
    'enable_debug' => false,
    'enable_deprecation_logging' => true,
    'enable_email_notifications' => false,
    'notification_email' => get_option('admin_email'),
    'enable_asset_minification' => false,
    'enable_conditional_loading' => true,
    'cache_expiry_hours' => 24,
    'max_log_file_size' => 10,
);
$settings = wp_parse_args($settings, $defaults);
?>

<div class="wrap">
    <h1><?php esc_html_e('WeCoza Agents Settings', 'wecoza-agents-plugin'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wecoza_agents_settings', 'wecoza_agents_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Debug Mode', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_debug" value="1" <?php checked($settings['enable_debug']); ?>>
                        <?php esc_html_e('Enable debug logging', 'wecoza-agents-plugin'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Enable detailed debug logging for troubleshooting.', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Deprecation Logging', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_deprecation_logging" value="1" <?php checked($settings['enable_deprecation_logging']); ?>>
                        <?php esc_html_e('Enable deprecation logging', 'wecoza-agents-plugin'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Log usage of deprecated theme functions during migration.', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Email Notifications', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_email_notifications" value="1" <?php checked($settings['enable_email_notifications']); ?>>
                        <?php esc_html_e('Enable email notifications', 'wecoza-agents-plugin'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Send email notifications for important events.', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Notification Email', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <input type="email" name="notification_email" value="<?php echo esc_attr($settings['notification_email']); ?>" class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Email address to receive notifications.', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Asset Optimization', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_asset_minification" value="1" <?php checked($settings['enable_asset_minification']); ?>>
                        <?php esc_html_e('Enable asset minification', 'wecoza-agents-plugin'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Use minified versions of CSS and JavaScript files.', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Conditional Loading', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_conditional_loading" value="1" <?php checked($settings['enable_conditional_loading']); ?>>
                        <?php esc_html_e('Enable conditional asset loading', 'wecoza-agents-plugin'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Only load assets when agent shortcodes are present on the page.', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Cache Expiry', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <input type="number" name="cache_expiry_hours" value="<?php echo esc_attr($settings['cache_expiry_hours']); ?>" min="1" max="168" class="small-text">
                    <?php esc_html_e('hours', 'wecoza-agents-plugin'); ?>
                    <p class="description">
                        <?php esc_html_e('How long to cache frequently accessed data (1-168 hours).', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Log File Size', 'wecoza-agents-plugin'); ?></th>
                <td>
                    <input type="number" name="max_log_file_size" value="<?php echo esc_attr($settings['max_log_file_size']); ?>" min="1" max="100" class="small-text">
                    <?php esc_html_e('MB', 'wecoza-agents-plugin'); ?>
                    <p class="description">
                        <?php esc_html_e('Maximum size for log files before rotation (1-100 MB).', 'wecoza-agents-plugin'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr>
    
    <h2><?php esc_html_e('Migration Status', 'wecoza-agents-plugin'); ?></h2>
    
    <?php
    // Get migration status
    $theme_files_exist = array();
    $theme_files = array(
        'agents-functions.php',
        'agents-capture-shortcode.php',
        'agents-display-shortcode.php',
        'js/agents-app.js'
        // 'agents-extracted.css'
    );
    
    $theme_agent_dir = get_stylesheet_directory() . '/assets/agents/';
    foreach ($theme_files as $file) {
        if (file_exists($theme_agent_dir . $file)) {
            $theme_files_exist[] = $file;
        }
    }
    
    $deprecation_logger = \WeCoza\Agents\DeprecationLogger::get_instance();
    $deprecation_stats = $deprecation_logger->get_deprecation_stats();
    ?>
    
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Migration Item', 'wecoza-agents-plugin'); ?></th>
                <th><?php esc_html_e('Status', 'wecoza-agents-plugin'); ?></th>
                <th><?php esc_html_e('Details', 'wecoza-agents-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php esc_html_e('Plugin Active', 'wecoza-agents-plugin'); ?></td>
                <td><span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php esc_html_e('Active', 'wecoza-agents-plugin'); ?></td>
                <td><?php esc_html_e('Plugin is active and functioning.', 'wecoza-agents-plugin'); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e('Theme File Cleanup', 'wecoza-agents-plugin'); ?></td>
                <td>
                    <?php if (empty($theme_files_exist)) : ?>
                        <span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php esc_html_e('Complete', 'wecoza-agents-plugin'); ?>
                    <?php else : ?>
                        <span class="dashicons dashicons-warning" style="color: orange;"></span> <?php esc_html_e('Pending', 'wecoza-agents-plugin'); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (empty($theme_files_exist)) : ?>
                        <?php esc_html_e('All deprecated theme files have been removed.', 'wecoza-agents-plugin'); ?>
                    <?php else : ?>
                        <?php 
                        printf(
                            esc_html__('%d deprecated files still exist: %s', 'wecoza-agents-plugin'),
                            count($theme_files_exist),
                            '<code>' . implode(', ', $theme_files_exist) . '</code>'
                        );
                        ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?php esc_html_e('Deprecation Logging', 'wecoza-agents-plugin'); ?></td>
                <td>
                    <?php if ($deprecation_stats['total_entries'] > 0) : ?>
                        <span class="dashicons dashicons-info" style="color: blue;"></span> <?php esc_html_e('Active', 'wecoza-agents-plugin'); ?>
                    <?php else : ?>
                        <span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php esc_html_e('Clean', 'wecoza-agents-plugin'); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($deprecation_stats['total_entries'] > 0) : ?>
                        <?php 
                        printf(
                            esc_html__('%d deprecation events logged. Most recent: %s', 'wecoza-agents-plugin'),
                            $deprecation_stats['total_entries'],
                            $deprecation_stats['most_recent']
                        );
                        ?>
                    <?php else : ?>
                        <?php esc_html_e('No deprecated function calls detected.', 'wecoza-agents-plugin'); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
    
    <hr>
    
    <h2><?php esc_html_e('System Information', 'wecoza-agents-plugin'); ?></h2>
    
    <table class="widefat">
        <tbody>
            <tr>
                <td><strong><?php esc_html_e('Plugin Version', 'wecoza-agents-plugin'); ?></strong></td>
                <td><?php echo esc_html(WECOZA_AGENTS_VERSION); ?></td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('WordPress Version', 'wecoza-agents-plugin'); ?></strong></td>
                <td><?php echo esc_html(get_bloginfo('version')); ?></td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('PHP Version', 'wecoza-agents-plugin'); ?></strong></td>
                <td><?php echo esc_html(PHP_VERSION); ?></td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('Database Type', 'wecoza-agents-plugin'); ?></strong></td>
                <td><?php echo esc_html(get_option('wecoza_agents_db_type', 'PostgreSQL')); ?></td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('Theme', 'wecoza-agents-plugin'); ?></strong></td>
                <td><?php echo esc_html(get_stylesheet()); ?></td>
            </tr>
        </tbody>
    </table>
</div>