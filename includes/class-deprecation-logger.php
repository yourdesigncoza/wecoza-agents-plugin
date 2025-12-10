<?php
/**
 * Deprecation Logger
 *
 * Tracks usage of deprecated theme agent functionality and logs to file.
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
 * Deprecation Logger class
 *
 * @since 1.0.0
 */
class DeprecationLogger {

    /**
     * Instance
     *
     * @var DeprecationLogger
     */
    private static $instance = null;

    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;

    /**
     * Maximum log file size (in bytes)
     *
     * @var int
     */
    private $max_log_size = 10485760; // 10MB

    /**
     * Whether logging is enabled
     *
     * @var bool
     */
    private $logging_enabled = false;

    /**
     * Get instance
     *
     * @since 1.0.0
     * @return DeprecationLogger
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
        $this->log_file = wecoza_agents_get_plugin_dir('logs/deprecation.log');
        $this->logging_enabled = (defined('WP_DEBUG') && WP_DEBUG) || get_option('wecoza_agents_deprecation_logging', false);
        
        $this->init_hooks();
        $this->ensure_log_directory();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Hook into WordPress deprecation notices
        add_action('deprecated_file_included', array($this, 'log_deprecated_file'), 10, 4);
        add_action('deprecated_function_run', array($this, 'log_deprecated_function'), 10, 3);
        add_action('deprecated_argument_run', array($this, 'log_deprecated_argument'), 10, 3);
        add_action('deprecated_hook_run', array($this, 'log_deprecated_hook'), 10, 4);
        
        // Admin notices
        add_action('admin_notices', array($this, 'show_deprecation_notices'));
        
        // AJAX handler for dismissing notices
        add_action('wp_ajax_dismiss_deprecation_notice', array($this, 'dismiss_deprecation_notice'));
    }

    /**
     * Ensure log directory exists
     *
     * @since 1.0.0
     */
    private function ensure_log_directory() {
        $log_dir = dirname($this->log_file);
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // Create .htaccess to protect log files
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "deny from all\n");
        }
    }

    /**
     * Log deprecated file usage
     *
     * @since 1.0.0
     * @param string $file The file that was included
     * @param string $version The version of WordPress that deprecated the file
     * @param string $replacement The replacement file that should be used
     * @param string $message A message regarding the deprecated file
     */
    public function log_deprecated_file($file, $version, $replacement, $message) {
        if (!$this->is_agent_related($file)) {
            return;
        }
        
        $this->write_log_entry('deprecated_file', array(
            'file' => $file,
            'version' => $version,
            'replacement' => $replacement,
            'message' => $message,
            'backtrace' => $this->get_filtered_backtrace()
        ));
    }

    /**
     * Log deprecated function usage
     *
     * @since 1.0.0
     * @param string $function The function that was called
     * @param string $version The version of WordPress that deprecated the function
     * @param string $replacement The replacement function that should be used
     */
    public function log_deprecated_function($function, $version, $replacement) {
        if (!$this->is_agent_related_function($function)) {
            return;
        }
        
        $this->write_log_entry('deprecated_function', array(
            'function' => $function,
            'version' => $version,
            'replacement' => $replacement,
            'backtrace' => $this->get_filtered_backtrace()
        ));
    }

    /**
     * Log deprecated argument usage
     *
     * @since 1.0.0
     * @param string $function The function that was called
     * @param string $version The version of WordPress that deprecated the argument
     * @param string $message A message regarding the deprecated argument
     */
    public function log_deprecated_argument($function, $version, $message) {
        if (!$this->is_agent_related_function($function)) {
            return;
        }
        
        $this->write_log_entry('deprecated_argument', array(
            'function' => $function,
            'version' => $version,
            'message' => $message,
            'backtrace' => $this->get_filtered_backtrace()
        ));
    }

    /**
     * Log deprecated hook usage
     *
     * @since 1.0.0
     * @param string $hook The hook that was used
     * @param string $version The version of WordPress that deprecated the hook
     * @param string $replacement The replacement hook that should be used
     * @param string $message A message regarding the deprecated hook
     */
    public function log_deprecated_hook($hook, $version, $replacement, $message) {
        if (!$this->is_agent_related_hook($hook)) {
            return;
        }
        
        $this->write_log_entry('deprecated_hook', array(
            'hook' => $hook,
            'version' => $version,
            'replacement' => $replacement,
            'message' => $message,
            'backtrace' => $this->get_filtered_backtrace()
        ));
    }

    /**
     * Write log entry
     *
     * @since 1.0.0
     * @param string $type Entry type
     * @param array $data Entry data
     */
    private function write_log_entry($type, $data) {
        if (!$this->logging_enabled) {
            return;
        }
        
        // Check log file size and rotate if necessary
        $this->rotate_log_if_needed();
        
        $entry = array(
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'data' => $data,
            'user' => get_current_user_id(),
            'ip' => $this->get_client_ip(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        
        $log_line = json_encode($entry) . "\n";
        
        // Write to log file
        file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Update last activity timestamp
        update_option('wecoza_agents_last_deprecation_activity', time());
    }

    /**
     * Check if file is agent-related
     *
     * @since 1.0.0
     * @param string $file File path
     * @return bool Whether file is agent-related
     */
    private function is_agent_related($file) {
        $agent_files = array(
            'agents-capture-shortcode.php',
            'agents-display-shortcode.php',
            'agents-functions.php',
            'AgentController.php'
        );
        
        return in_array(basename($file), $agent_files);
    }

    /**
     * Check if function is agent-related
     *
     * @since 1.0.0
     * @param string $function Function name
     * @return bool Whether function is agent-related
     */
    private function is_agent_related_function($function) {
        $agent_functions = array(
            'agents_capture_shortcode',
            'wecoza_display_agents_shortcode',
            'load_agents_files',
            'enqueue_agents_assets',
            'wecoza_agents_validate_sa_id',
            'wecoza_agents_validate_passport'
        );
        
        return in_array($function, $agent_functions) || 
               strpos($function, 'agent') !== false ||
               strpos($function, 'wecoza_agent') !== false;
    }

    /**
     * Check if hook is agent-related
     *
     * @since 1.0.0
     * @param string $hook Hook name
     * @return bool Whether hook is agent-related
     */
    private function is_agent_related_hook($hook) {
        return strpos($hook, 'agent') !== false ||
               strpos($hook, 'wecoza_agent') !== false;
    }

    /**
     * Get filtered backtrace
     *
     * @since 1.0.0
     * @return array Filtered backtrace
     */
    private function get_filtered_backtrace() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        
        // Filter out plugin files from backtrace
        $filtered = array();
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && strpos($trace['file'], 'wecoza-agents-plugin') === false) {
                $filtered[] = array(
                    'file' => $trace['file'],
                    'line' => $trace['line'] ?? 0,
                    'function' => $trace['function'] ?? 'unknown'
                );
            }
        }
        
        return array_slice($filtered, 0, 5); // Limit to 5 entries
    }

    /**
     * Get client IP address
     *
     * @since 1.0.0
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Rotate log file if it's too large
     *
     * @since 1.0.0
     */
    private function rotate_log_if_needed() {
        if (!file_exists($this->log_file)) {
            return;
        }
        
        if (filesize($this->log_file) > $this->max_log_size) {
            $backup_file = $this->log_file . '.old';
            
            // Remove old backup if it exists
            if (file_exists($backup_file)) {
                unlink($backup_file);
            }
            
            // Rename current log to backup
            rename($this->log_file, $backup_file);
        }
    }

    /**
     * Get deprecation statistics
     *
     * @since 1.0.0
     * @return array Deprecation statistics
     */
    public function get_deprecation_stats() {
        if (!file_exists($this->log_file)) {
            return array();
        }
        
        $stats = array(
            'total_entries' => 0,
            'types' => array(),
            'files' => array(),
            'functions' => array(),
            'last_activity' => get_option('wecoza_agents_last_deprecation_activity', 0)
        );
        
        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry) continue;
            
            $stats['total_entries']++;
            
            $type = $entry['type'] ?? 'unknown';
            $stats['types'][$type] = ($stats['types'][$type] ?? 0) + 1;
            
            if ($type === 'deprecated_file' && isset($entry['data']['file'])) {
                $file = basename($entry['data']['file']);
                $stats['files'][$file] = ($stats['files'][$file] ?? 0) + 1;
            }
            
            if ($type === 'deprecated_function' && isset($entry['data']['function'])) {
                $function = $entry['data']['function'];
                $stats['functions'][$function] = ($stats['functions'][$function] ?? 0) + 1;
            }
        }
        
        return $stats;
    }

    /**
     * Show deprecation notices in admin
     *
     * @since 1.0.0
     */
    public function show_deprecation_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $stats = $this->get_deprecation_stats();
        
        if (empty($stats['total_entries'])) {
            return;
        }
        
        if (get_option('wecoza_agents_deprecation_notice_dismissed', false)) {
            return;
        }
        
        $last_activity = $stats['last_activity'];
        if ($last_activity && (time() - $last_activity) < 86400) { // Within last 24 hours
            ?>
            <div class="notice notice-warning is-dismissible" data-notice="deprecation">
                <p>
                    <strong><?php esc_html_e('WeCoza Agents Plugin', 'wecoza-agents-plugin'); ?></strong>
                </p>
                <p>
                    <?php
                    printf(
                        esc_html__('Deprecated agent functionality is still being used on your site. %d deprecation events have been logged.', 'wecoza-agents-plugin'),
                        $stats['total_entries']
                    );
                    ?>
                </p>
                <p>
                    <?php esc_html_e('Please remove the deprecated theme files to complete the migration to the plugin.', 'wecoza-agents-plugin'); ?>
                </p>
                <p>
                    <a href="#" class="button" onclick="dismissDeprecationNotice(); return false;">
                        <?php esc_html_e('Dismiss this notice', 'wecoza-agents-plugin'); ?>
                    </a>
                </p>
            </div>
            <script>
            function dismissDeprecationNotice() {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_deprecation_notice',
                    nonce: '<?php echo wp_create_nonce('dismiss_deprecation_notice'); ?>'
                });
                jQuery('[data-notice="deprecation"]').fadeOut();
            }
            </script>
            <?php
        }
    }

    /**
     * Handle AJAX request to dismiss deprecation notice
     *
     * @since 1.0.0
     */
    public function dismiss_deprecation_notice() {
        if (!wp_verify_nonce($_POST['nonce'], 'dismiss_deprecation_notice')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        update_option('wecoza_agents_deprecation_notice_dismissed', true);
        wp_send_json_success();
    }

    /**
     * Clear deprecation log
     *
     * @since 1.0.0
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        
        if (file_exists($this->log_file . '.old')) {
            unlink($this->log_file . '.old');
        }
        
        delete_option('wecoza_agents_last_deprecation_activity');
        delete_option('wecoza_agents_deprecation_notice_dismissed');
    }

    /**
     * Enable deprecation logging
     *
     * @since 1.0.0
     */
    public function enable_logging() {
        $this->logging_enabled = true;
        update_option('wecoza_agents_deprecation_logging', true);
    }

    /**
     * Disable deprecation logging
     *
     * @since 1.0.0
     */
    public function disable_logging() {
        $this->logging_enabled = false;
        update_option('wecoza_agents_deprecation_logging', false);
    }
}

// Initialize deprecation logger
DeprecationLogger::get_instance();