<?php
/**
 * Database Logger
 *
 * Handles logging of database queries, errors, and performance metrics.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Database;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Logger class
 *
 * @since 1.0.0
 */
class DatabaseLogger {

    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;

    /**
     * Enable query logging
     *
     * @var bool
     */
    private $enable_query_log;

    /**
     * Enable error logging
     *
     * @var bool
     */
    private $enable_error_log;

    /**
     * Enable performance logging
     *
     * @var bool
     */
    private $enable_performance_log;

    /**
     * Query log entries
     *
     * @var array
     */
    private $query_log = array();

    /**
     * Performance thresholds
     *
     * @var array
     */
    private $thresholds = array(
        'slow_query' => 1.0, // Seconds
        'memory_limit' => 10485760, // 10MB
    );

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_settings();
        $this->init_log_file();
        
        // Hook into shutdown to save logs
        add_action('shutdown', array($this, 'save_query_log'));
    }

    /**
     * Initialize settings
     *
     * @since 1.0.0
     */
    private function init_settings() {
        $settings = get_option('wecoza_agents_settings', array());
        
        $this->enable_query_log = !empty($settings['enable_query_log']) && WP_DEBUG;
        $this->enable_error_log = !empty($settings['enable_error_log']) || WP_DEBUG;
        $this->enable_performance_log = !empty($settings['enable_performance_log']) && WP_DEBUG;
        
        // Allow filtering of settings
        $this->enable_query_log = apply_filters('wecoza_agents_enable_query_log', $this->enable_query_log);
        $this->enable_error_log = apply_filters('wecoza_agents_enable_error_log', $this->enable_error_log);
        $this->enable_performance_log = apply_filters('wecoza_agents_enable_performance_log', $this->enable_performance_log);
    }

    /**
     * Initialize log file
     *
     * @since 1.0.0
     */
    private function init_log_file() {
        $log_dir = WECOZA_AGENTS_LOGS_DIR;
        
        // Create logs directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // Set log file path with date
        $this->log_file = $log_dir . 'database-' . date('Y-m-d') . '.log';
        
        // Rotate old logs
        $this->rotate_logs();
    }

    /**
     * Log a message
     *
     * @since 1.0.0
     * @param string $level Log level (info, warning, error, debug)
     * @param string $message Message to log
     * @param array $context Additional context
     */
    public function log($level, $message, $context = array()) {
        if (!$this->should_log($level)) {
            return;
        }
        
        $entry = $this->format_log_entry($level, $message, $context);
        
        // Write to file
        $this->write_to_file($entry);
        
        // Also send to error log if error level
        if ($level === 'error' && WP_DEBUG) {
            error_log('[WeCoza Agents DB] ' . $message);
        }
    }

    /**
     * Log a database query
     *
     * @since 1.0.0
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param float $execution_time Execution time in seconds
     */
    public function log_query($sql, $params = array(), $execution_time = 0) {
        if (!$this->enable_query_log) {
            return;
        }
        
        $entry = array(
            'timestamp' => current_time('mysql'),
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $execution_time,
            'memory_usage' => memory_get_usage(),
            'peak_memory' => memory_get_peak_usage(),
            'backtrace' => $this->get_backtrace(),
        );
        
        // Check if slow query
        if ($execution_time > $this->thresholds['slow_query']) {
            $entry['is_slow'] = true;
            $this->log('warning', 'Slow query detected', $entry);
        }
        
        // Add to query log
        $this->query_log[] = $entry;
        
        // Log immediately if in debug mode
        if (defined('WECOZA_AGENTS_DEBUG_QUERIES') && WECOZA_AGENTS_DEBUG_QUERIES) {
            $this->write_query_to_file($entry);
        }
    }

    /**
     * Log an error
     *
     * @since 1.0.0
     * @param string $message Error message
     * @param array $context Error context
     */
    public function log_error($message, $context = array()) {
        $this->log('error', $message, $context);
    }

    /**
     * Log a warning
     *
     * @since 1.0.0
     * @param string $message Warning message
     * @param array $context Warning context
     */
    public function log_warning($message, $context = array()) {
        $this->log('warning', $message, $context);
    }

    /**
     * Log debug information
     *
     * @since 1.0.0
     * @param string $message Debug message
     * @param array $context Debug context
     */
    public function log_debug($message, $context = array()) {
        if (WP_DEBUG) {
            $this->log('debug', $message, $context);
        }
    }

    /**
     * Should log this level
     *
     * @since 1.0.0
     * @param string $level Log level
     * @return bool
     */
    private function should_log($level) {
        switch ($level) {
            case 'error':
                return $this->enable_error_log;
            case 'warning':
            case 'info':
                return $this->enable_query_log || $this->enable_performance_log;
            case 'debug':
                return WP_DEBUG && ($this->enable_query_log || $this->enable_performance_log);
            default:
                return false;
        }
    }

    /**
     * Format log entry
     *
     * @since 1.0.0
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @return string Formatted entry
     */
    private function format_log_entry($level, $message, $context = array()) {
        $timestamp = current_time('Y-m-d H:i:s');
        $level = strtoupper($level);
        
        $entry = "[$timestamp] [$level] $message";
        
        if (!empty($context)) {
            $entry .= " | Context: " . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        return $entry;
    }

    /**
     * Write to log file
     *
     * @since 1.0.0
     * @param string $entry Log entry
     */
    private function write_to_file($entry) {
        if (!is_writable(dirname($this->log_file))) {
            return;
        }
        
        file_put_contents($this->log_file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Write query to file
     *
     * @since 1.0.0
     * @param array $query Query data
     */
    private function write_query_to_file($query) {
        $entry = sprintf(
            "[%s] [QUERY] SQL: %s | Params: %s | Time: %.4fs | Memory: %s",
            $query['timestamp'],
            $query['sql'],
            json_encode($query['params']),
            $query['execution_time'],
            size_format($query['memory_usage'])
        );
        
        $this->write_to_file($entry);
    }

    /**
     * Save query log
     *
     * @since 1.0.0
     */
    public function save_query_log() {
        if (empty($this->query_log) || !$this->enable_query_log) {
            return;
        }
        
        // Write summary
        $summary = $this->get_query_summary();
        $this->write_to_file($this->format_log_entry('info', 'Query Summary', $summary));
        
        // Write individual queries if debug mode
        if (WP_DEBUG_LOG) {
            foreach ($this->query_log as $query) {
                $this->write_query_to_file($query);
            }
        }
    }

    /**
     * Get query summary
     *
     * @since 1.0.0
     * @return array Summary data
     */
    private function get_query_summary() {
        $total_queries = count($this->query_log);
        $total_time = 0;
        $slow_queries = 0;
        $query_types = array();
        
        foreach ($this->query_log as $query) {
            $total_time += $query['execution_time'];
            
            if (!empty($query['is_slow'])) {
                $slow_queries++;
            }
            
            // Extract query type
            $type = $this->get_query_type($query['sql']);
            if (!isset($query_types[$type])) {
                $query_types[$type] = 0;
            }
            $query_types[$type]++;
        }
        
        return array(
            'total_queries' => $total_queries,
            'total_time' => round($total_time, 4),
            'average_time' => $total_queries > 0 ? round($total_time / $total_queries, 4) : 0,
            'slow_queries' => $slow_queries,
            'query_types' => $query_types,
            'peak_memory' => size_format(memory_get_peak_usage()),
        );
    }

    /**
     * Get query type from SQL
     *
     * @since 1.0.0
     * @param string $sql SQL query
     * @return string Query type
     */
    private function get_query_type($sql) {
        $sql = trim($sql);
        $first_word = strtoupper(substr($sql, 0, strpos($sql . ' ', ' ')));
        
        switch ($first_word) {
            case 'SELECT':
            case 'INSERT':
            case 'UPDATE':
            case 'DELETE':
            case 'CREATE':
            case 'DROP':
            case 'ALTER':
            case 'TRUNCATE':
                return $first_word;
            default:
                return 'OTHER';
        }
    }

    /**
     * Get backtrace
     *
     * @since 1.0.0
     * @return array Backtrace
     */
    private function get_backtrace() {
        if (!WP_DEBUG) {
            return array();
        }
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $filtered = array();
        
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && strpos($trace['file'], 'wecoza-agents-plugin') !== false) {
                $filtered[] = array(
                    'file' => str_replace(ABSPATH, '', $trace['file']),
                    'line' => $trace['line'] ?? 0,
                    'function' => $trace['function'] ?? '',
                    'class' => $trace['class'] ?? '',
                );
            }
        }
        
        return $filtered;
    }

    /**
     * Rotate logs
     *
     * @since 1.0.0
     */
    private function rotate_logs() {
        $log_dir = WECOZA_AGENTS_LOGS_DIR;
        $max_age = 7; // Days
        $max_size = 10485760; // 10MB
        
        // Get all log files
        $files = glob($log_dir . 'database-*.log');
        
        if (empty($files)) {
            return;
        }
        
        foreach ($files as $file) {
            // Check age
            $file_time = filemtime($file);
            if ($file_time && (time() - $file_time) > ($max_age * DAY_IN_SECONDS)) {
                @unlink($file);
                continue;
            }
            
            // Check size
            $file_size = filesize($file);
            if ($file_size && $file_size > $max_size) {
                // Archive large file
                $archive_name = str_replace('.log', '-' . time() . '.log.gz', $file);
                $fp_in = fopen($file, 'rb');
                $fp_out = gzopen($archive_name, 'wb9');
                
                if ($fp_in && $fp_out) {
                    while (!feof($fp_in)) {
                        gzwrite($fp_out, fread($fp_in, 1024 * 512));
                    }
                    fclose($fp_in);
                    gzclose($fp_out);
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Clear logs
     *
     * @since 1.0.0
     */
    public function clear_logs() {
        $log_dir = WECOZA_AGENTS_LOGS_DIR;
        $files = glob($log_dir . 'database-*.log*');
        
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * Get log entries
     *
     * @since 1.0.0
     * @param string $date Date (Y-m-d format)
     * @param int $limit Number of entries to retrieve
     * @return array Log entries
     */
    public function get_log_entries($date = '', $limit = 100) {
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        
        $log_file = WECOZA_AGENTS_LOGS_DIR . 'database-' . $date . '.log';
        
        if (!file_exists($log_file)) {
            return array();
        }
        
        $entries = array();
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            return array();
        }
        
        // Get last $limit lines
        $lines = array_slice($lines, -$limit);
        
        foreach ($lines as $line) {
            // Parse log entry
            if (preg_match('/^\[(.*?)\] \[(.*?)\] (.*)$/', $line, $matches)) {
                $entries[] = array(
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => $matches[3],
                );
            }
        }
        
        return array_reverse($entries);
    }

    /**
     * Export logs
     *
     * @since 1.0.0
     * @param string $format Export format (csv, json)
     * @param string $date Date to export
     * @return string|false Export data or false on failure
     */
    public function export_logs($format = 'csv', $date = '') {
        $entries = $this->get_log_entries($date, 0);
        
        if (empty($entries)) {
            return false;
        }
        
        switch ($format) {
            case 'csv':
                return $this->export_as_csv($entries);
            case 'json':
                return json_encode($entries, JSON_PRETTY_PRINT);
            default:
                return false;
        }
    }

    /**
     * Export as CSV
     *
     * @since 1.0.0
     * @param array $entries Log entries
     * @return string CSV data
     */
    private function export_as_csv($entries) {
        $csv = "Timestamp,Level,Message\n";
        
        foreach ($entries as $entry) {
            $csv .= sprintf(
                '"%s","%s","%s"' . "\n",
                $entry['timestamp'],
                $entry['level'],
                str_replace('"', '""', $entry['message'])
            );
        }
        
        return $csv;
    }
}