<?php
/**
 * Global functions for WeCoza Agents Plugin
 *
 * This file contains helper functions that can be used throughout the plugin
 * and by other themes/plugins if needed.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the plugin version
 *
 * @since 1.0.0
 * @return string Plugin version
 */
function wecoza_agents_get_version() {
    return WECOZA_AGENTS_VERSION;
}

/**
 * Get the plugin directory path
 *
 * @since 1.0.0
 * @param string $path Optional path to append
 * @return string Plugin directory path
 */
function wecoza_agents_get_plugin_dir($path = '') {
    return WECOZA_AGENTS_PLUGIN_DIR . ltrim($path, '/');
}

/**
 * Get the plugin URL
 *
 * @since 1.0.0
 * @param string $path Optional path to append
 * @return string Plugin URL
 */
function wecoza_agents_get_plugin_url($path = '') {
    return WECOZA_AGENTS_PLUGIN_URL . ltrim($path, '/');
}

/**
 * Check if current user can manage agents
 *
 * @since 1.0.0
 * @return bool True if user has permission
 */
function wecoza_agents_can_manage() {
    return current_user_can('edit_others_posts'); // Editors and above
}

/**
 * Log debug messages
 *
 * @since 1.0.0
 * @param mixed $message Message to log
 * @param string $level Log level (info, warning, error)
 */
function wecoza_agents_log($message, $level = 'info') {
    if (!WP_DEBUG) {
        return;
    }

    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    }

    $timestamp = current_time('Y-m-d H:i:s');
    $log_message = sprintf('[%s] [%s] %s', $timestamp, strtoupper($level), $message);
    
    error_log($log_message);
    
    // Also log to custom file if writable
    $log_file = wecoza_agents_get_plugin_dir('logs/debug.log');
    if (is_writable(dirname($log_file))) {
        file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Sanitize and validate SA ID number
 *
 * @since 1.0.0
 * @param string $id_number SA ID number to validate
 * @return array Array with 'valid' bool and 'message' string
 */
function wecoza_agents_validate_sa_id($id_number) {
    $id_number = preg_replace('/[^0-9]/', '', $id_number);
    
    // Check length
    if (strlen($id_number) !== 13) {
        return array(
            'valid' => false,
            'message' => __('ID number must be 13 digits', 'wecoza-agents-plugin')
        );
    }
    
    // Extract date components
    $year = substr($id_number, 0, 2);
    $month = substr($id_number, 2, 2);
    $day = substr($id_number, 4, 2);
    
    // Determine century
    $current_year = date('y');
    $full_year = ($year <= $current_year) ? '20' . $year : '19' . $year;
    
    // Validate date
    if (!checkdate((int)$month, (int)$day, (int)$full_year)) {
        return array(
            'valid' => false,
            'message' => __('Invalid date in ID number', 'wecoza-agents-plugin')
        );
    }
    
    // Validate checksum using Luhn algorithm
    $sum = 0;
    $alternate = false;
    
    for ($i = strlen($id_number) - 1; $i >= 0; $i--) {
        $digit = (int)$id_number[$i];
        
        if ($alternate) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        $sum += $digit;
        $alternate = !$alternate;
    }
    
    if ($sum % 10 !== 0) {
        return array(
            'valid' => false,
            'message' => __('Invalid ID number checksum', 'wecoza-agents-plugin')
        );
    }
    
    return array('valid' => true);
}

/**
 * Sanitize and validate passport number
 *
 * @since 1.0.0
 * @param string $passport_number Passport number to validate
 * @return array Array with 'valid' bool and 'message' string
 */
function wecoza_agents_validate_passport($passport_number) {
    $passport_number = trim($passport_number);
    
    // Check format: 6-12 alphanumeric characters
    if (!preg_match('/^[A-Z0-9]{6,12}$/i', $passport_number)) {
        return array(
            'valid' => false,
            'message' => __('Passport number must be 6-12 characters (letters and numbers only)', 'wecoza-agents-plugin')
        );
    }
    
    return array('valid' => true);
}

/**
 * Get template path with theme override support
 *
 * @since 1.0.0
 * @param string $template Template name
 * @param string $type Template type (forms, display, partials)
 * @return string Full template path
 */
function wecoza_agents_get_template_path($template, $type = '') {
    $template_dir = 'wecoza-agents/';
    
    if ($type) {
        $template_dir .= $type . '/';
    }
    
    $template_file = $template_dir . $template;
    
    // Check theme override locations in order of priority
    $locations = array(
        get_stylesheet_directory() . '/' . $template_file,  // Child theme
        get_template_directory() . '/' . $template_file,    // Parent theme
        wecoza_agents_get_plugin_dir('templates/' . ($type ? $type . '/' : '') . $template) // Plugin
    );
    
    foreach ($locations as $location) {
        if (file_exists($location)) {
            return $location;
        }
    }
    
    // Return plugin path as fallback (even if it doesn't exist)
    return wecoza_agents_get_plugin_dir('templates/' . ($type ? $type . '/' : '') . $template);
}

/**
 * Load a template with theme override support
 *
 * @since 1.0.0
 * @param string $template Template name
 * @param array $args Arguments to pass to template
 * @param string $type Template type
 * @param bool $echo Whether to echo or return
 * @return string|void Template content if $echo is false
 */
function wecoza_agents_load_template($template, $args = array(), $type = '', $echo = true) {
    $template_path = wecoza_agents_get_template_path($template, $type);
    
    if (!file_exists($template_path)) {
        wecoza_agents_log("Template not found: {$template_path}", 'error');
        return '';
    }
    
    // Log template override usage for debugging
    $plugin_path = wecoza_agents_get_plugin_dir('templates/' . ($type ? $type . '/' : '') . $template);
    if ($template_path !== $plugin_path && WP_DEBUG) {
        wecoza_agents_log("Template override used: {$template_path} (original: {$plugin_path})", 'info');
    }
    
    // Allow filters to modify template args
    $args = apply_filters('wecoza_agents_template_args', $args, $template, $type);
    $args = apply_filters('wecoza_agents_template_args_' . $template, $args, $type);
    
    // Extract args to variables
    if (!empty($args)) {
        extract($args);
    }
    
    // Action hook before template loads
    do_action('wecoza_agents_before_template_load', $template, $type, $args);
    
    if (!$echo) {
        ob_start();
    }
    
    include $template_path;
    
    if (!$echo) {
        return ob_get_clean();
    }
    
    // Action hook after template loads
    do_action('wecoza_agents_after_template_load', $template, $type, $args);
}

/**
 * Check if template exists and get override information
 *
 * @since 1.0.0
 * @param string $template Template name
 * @param string $type Template type
 * @return array Template information
 */
function wecoza_agents_get_template_info($template, $type = '') {
    $template_dir = 'wecoza-agents/';
    
    if ($type) {
        $template_dir .= $type . '/';
    }
    
    $template_file = $template_dir . $template;
    
    $locations = array(
        'child_theme' => get_stylesheet_directory() . '/' . $template_file,
        'parent_theme' => get_template_directory() . '/' . $template_file,
        'plugin' => wecoza_agents_get_plugin_dir('templates/' . ($type ? $type . '/' : '') . $template)
    );
    
    $info = array(
        'template' => $template,
        'type' => $type,
        'exists' => false,
        'location' => '',
        'path' => '',
        'overridden' => false,
        'all_locations' => $locations
    );
    
    foreach ($locations as $location_type => $location_path) {
        if (file_exists($location_path)) {
            $info['exists'] = true;
            $info['location'] = $location_type;
            $info['path'] = $location_path;
            $info['overridden'] = ($location_type !== 'plugin');
            break;
        }
    }
    
    return $info;
}

/**
 * Get training modules list
 *
 * @since 1.0.0
 * @return array Training modules
 */
function wecoza_agents_get_training_modules() {
    return array(
        'AET Communication level 1 Basic' => 'AET Communication level 1 Basic',
        'AET Communication level 1' => 'AET Communication level 1',
        'AET Communication level 2' => 'AET Communication level 2',
        'AET Communication level 3' => 'AET Communication level 3',
        'AET Communication level 4' => 'AET Communication level 4',
        'AET Numeracy level 1 Basic' => 'AET Numeracy level 1 Basic',
        'AET Numeracy level 1' => 'AET Numeracy level 1',
        'AET Numeracy level 2' => 'AET Numeracy level 2',
        'AET Numeracy level 3' => 'AET Numeracy level 3',
        'AET Numeracy level 4' => 'AET Numeracy level 4',
        'AET level 4 Life Orientation' => 'AET level 4 Life Orientation',
        'AET level 4 Human & Social Sciences' => 'AET level 4 Human & Social Sciences',
        'AET level 4 Economic & Management Sciences' => 'AET level 4 Economic & Management Sciences',
        'AET level 4 Natural Sciences' => 'AET level 4 Natural Sciences',
        'AET level 4 Small Micro Medium Enterprises' => 'AET level 4 Small Micro Medium Enterprises',
        'REALLL Communication' => 'REALLL Communication',
        'REALLL Numeracy' => 'REALLL Numeracy',
        'REALLL Finance' => 'REALLL Finance',
        'Business Admin NQF 2 - LP1' => 'Business Admin NQF 2 - LP1',
        'Business Admin NQF 2 - LP2' => 'Business Admin NQF 2 - LP2',
        'Business Admin NQF 2 - LP3' => 'Business Admin NQF 2 - LP3',
        'Business Admin NQF 2 - LP4' => 'Business Admin NQF 2 - LP4',
        'Business Admin NQF 2 - LP5' => 'Business Admin NQF 2 - LP5',
        'Business Admin NQF 2 - LP6' => 'Business Admin NQF 2 - LP6',
        'Business Admin NQF 2 - LP7' => 'Business Admin NQF 2 - LP7',
        'Business Admin NQF 2 - LP8' => 'Business Admin NQF 2 - LP8',
        'Business Admin NQF 2 - LP9' => 'Business Admin NQF 2 - LP9',
        'Business Admin NQF 2 - LP10' => 'Business Admin NQF 2 - LP10',
        'Business Admin NQF 3 - LP1' => 'Business Admin NQF 3 - LP1',
        'Business Admin NQF 3 - LP2' => 'Business Admin NQF 3 - LP2',
        'Business Admin NQF 3 - LP3' => 'Business Admin NQF 3 - LP3',
        'Business Admin NQF 3 - LP4' => 'Business Admin NQF 3 - LP4',
        'Business Admin NQF 3 - LP5' => 'Business Admin NQF 3 - LP5',
        'Business Admin NQF 3 - LP6' => 'Business Admin NQF 3 - LP6',
        'Business Admin NQF 3 - LP7' => 'Business Admin NQF 3 - LP7',
        'Business Admin NQF 3 - LP8' => 'Business Admin NQF 3 - LP8',
        'Business Admin NQF 3 - LP9' => 'Business Admin NQF 3 - LP9',
        'Business Admin NQF 3 - LP10' => 'Business Admin NQF 3 - LP10',
        'Business Admin NQF 3 - LP11' => 'Business Admin NQF 3 - LP11',
        'Business Admin NQF 4 - LP1' => 'Business Admin NQF 4 - LP1',
        'Business Admin NQF 4 - LP2' => 'Business Admin NQF 4 - LP2',
        'Business Admin NQF 4 - LP3' => 'Business Admin NQF 4 - LP3',
        'Business Admin NQF 4 - LP4' => 'Business Admin NQF 4 - LP4',
        'Business Admin NQF 4 - LP5' => 'Business Admin NQF 4 - LP5',
        'Business Admin NQF 4 - LP6' => 'Business Admin NQF 4 - LP6',
        'Business Admin NQF 4 - LP7' => 'Business Admin NQF 4 - LP7',
        'Introduction to Computers' => 'Introduction to Computers',
        'Email Etiquette' => 'Email Etiquette',
        'Time Management' => 'Time Management',
        'Supervisory Skills' => 'Supervisory Skills',
    );
}

/**
 * Handle AJAX request to dismiss migration notice
 *
 * @since 1.0.0
 */
function wecoza_agents_dismiss_migration_notice() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dismiss_migration_notice')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    // Update option to dismiss notice
    update_option('wecoza_agents_migration_notice_dismissed', true);
    
    wp_send_json_success();
}
add_action('wp_ajax_dismiss_wecoza_agents_migration_notice', 'wecoza_agents_dismiss_migration_notice');