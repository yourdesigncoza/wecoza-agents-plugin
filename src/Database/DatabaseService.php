<?php
/**
 * Database Service
 *
 * Handles database connections and operations with support for both PostgreSQL and MySQL.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Database;

use PDO;
use PDOException;
use Exception;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Service class
 *
 * @since 1.0.0
 */
class DatabaseService {

    /**
     * Singleton instance
     *
     * @var DatabaseService|null
     */
    private static $instance = null;

    /**
     * PDO instance
     *
     * @var PDO|null
     */
    private $pdo = null;

    /**
     * WordPress database instance
     *
     * @var \wpdb|null
     */
    private $wpdb = null;

    /**
     * Database type
     *
     * @var string 'postgresql' or 'mysql'
     */
    private $db_type = 'mysql';

    /**
     * Connection status
     *
     * @var bool
     */
    private $connected = false;

    /**
     * Logger instance
     *
     * @var DatabaseLogger|null
     */
    private $logger = null;

    /**
     * Constructor - private to enforce singleton
     *
     * @since 1.0.0
     */
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Initialize logger if available
        if (class_exists('WeCoza\Agents\Database\DatabaseLogger')) {
            $this->logger = new DatabaseLogger();
        }
        
        // Attempt PostgreSQL connection first
        if ($this->connect_postgresql()) {
            $this->db_type = 'postgresql';
            $this->connected = true;
        } else {
            // Fall back to MySQL/WordPress database
            $this->db_type = 'mysql';
            $this->connected = true; // WordPress database is always available
        }
    }

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return DatabaseService
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     */
    private function __clone() {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cloning is forbidden.', 'wecoza-agents-plugin'), '1.0.0');
    }

    /**
     * Prevent unserializing
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, esc_html__('Unserializing instances of this class is forbidden.', 'wecoza-agents-plugin'), '1.0.0');
    }

    /**
     * Connect to PostgreSQL
     *
     * @since 1.0.0
     * @return bool Success status
     */
    private function connect_postgresql() {
        // Get PostgreSQL credentials
        $pg_host = get_option('wecoza_postgres_host');
        $pg_port = get_option('wecoza_postgres_port', '5432');
        $pg_dbname = get_option('wecoza_postgres_dbname');
        $pg_user = get_option('wecoza_postgres_user');
        $pg_pass = get_option('wecoza_postgres_password');

        // Check if credentials exist
        if (empty($pg_host) || empty($pg_dbname) || empty($pg_user) || empty($pg_pass)) {
            return false;
        }

        try {
            // Create PDO instance for PostgreSQL
            $dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname";
            $this->pdo = new PDO($dsn, $pg_user, $pg_pass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_TIMEOUT => 5
            ));

            // Test connection
            $this->pdo->query('SELECT 1');
            
            $this->log('info', 'PostgreSQL connection established');
            return true;

        } catch (PDOException $e) {
            $this->log('error', 'PostgreSQL connection failed: ' . $e->getMessage());
            $this->pdo = null;
            return false;
        }
    }

    /**
     * Get database type
     *
     * @since 1.0.0
     * @return string 'postgresql' or 'mysql'
     */
    public function get_db_type() {
        return $this->db_type;
    }

    /**
     * Check if connected
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_connected() {
        return $this->connected;
    }

    /**
     * Get PDO instance
     *
     * @since 1.0.0
     * @return PDO|null
     */
    public function get_pdo() {
        return $this->pdo;
    }

    /**
     * Get WordPress database instance
     *
     * @since 1.0.0
     * @return \wpdb
     */
    public function get_wpdb() {
        return $this->wpdb;
    }

    /**
     * Execute a query
     *
     * @since 1.0.0
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return \PDOStatement|array|false
     */
    public function query($sql, $params = array()) {
        $start_time = microtime(true);
        
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                // PostgreSQL query
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                
                $this->log_query($sql, $params, microtime(true) - $start_time);
                return $stmt;
                
            } else {
                // MySQL/WordPress query
                if (!empty($params)) {
                    $sql = $this->wpdb->prepare($sql, $params);
                }
                
                $results = $this->wpdb->get_results($sql, ARRAY_A);
                
                $this->log_query($sql, $params, microtime(true) - $start_time);
                return $results;
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Query error: ' . $e->getMessage(), array(
                'sql' => $sql,
                'params' => $params
            ));
            return false;
        }
    }

    /**
     * Execute an insert query
     *
     * @since 1.0.0
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int|false Insert ID or false on failure
     */
    public function insert($table, $data) {
        $start_time = microtime(true);
        
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                // PostgreSQL insert
                $fields = array_keys($data);
                $values = array_map(function($field) { return ':' . $field; }, $fields);
                
                $sql = sprintf(
                    'INSERT INTO %s (%s) VALUES (%s) RETURNING id',
                    $table,
                    implode(', ', $fields),
                    implode(', ', $values)
                );
                
                $stmt = $this->pdo->prepare($sql);
                
                foreach ($data as $field => $value) {
                    $stmt->bindValue(':' . $field, $value);
                }
                
                $stmt->execute();
                $result = $stmt->fetch();
                
                $this->log_query($sql, $data, microtime(true) - $start_time);
                return $result['id'] ?? false;
                
            } else {
                // MySQL/WordPress insert
                $result = $this->wpdb->insert($table, $data);
                
                if ($result !== false) {
                    $this->log_query("INSERT INTO $table", $data, microtime(true) - $start_time);
                    return $this->wpdb->insert_id;
                }
                
                return false;
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Insert error: ' . $e->getMessage(), array(
                'table' => $table,
                'data' => $data
            ));
            return false;
        }
    }

    /**
     * Execute an update query
     *
     * @since 1.0.0
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where Where conditions
     * @return int|false Number of affected rows or false on failure
     */
    public function update($table, $data, $where) {
        $start_time = microtime(true);
        
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                // PostgreSQL update
                $set_parts = array();
                $where_parts = array();
                $params = array();
                
                foreach ($data as $field => $value) {
                    $set_parts[] = "$field = :set_$field";
                    $params["set_$field"] = $value;
                }
                
                foreach ($where as $field => $value) {
                    $where_parts[] = "$field = :where_$field";
                    $params["where_$field"] = $value;
                }
                
                $sql = sprintf(
                    'UPDATE %s SET %s WHERE %s',
                    $table,
                    implode(', ', $set_parts),
                    implode(' AND ', $where_parts)
                );
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                
                $this->log_query($sql, $params, microtime(true) - $start_time);
                return $stmt->rowCount();
                
            } else {
                // MySQL/WordPress update
                $result = $this->wpdb->update($table, $data, $where);
                
                $this->log_query("UPDATE $table", array_merge($data, $where), microtime(true) - $start_time);
                return $result;
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Update error: ' . $e->getMessage(), array(
                'table' => $table,
                'data' => $data,
                'where' => $where
            ));
            return false;
        }
    }

    /**
     * Execute a delete query
     *
     * @since 1.0.0
     * @param string $table Table name
     * @param array $where Where conditions
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($table, $where) {
        $start_time = microtime(true);
        
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                // PostgreSQL delete
                $where_parts = array();
                $params = array();
                
                foreach ($where as $field => $value) {
                    $where_parts[] = "$field = :$field";
                    $params[$field] = $value;
                }
                
                $sql = sprintf(
                    'DELETE FROM %s WHERE %s',
                    $table,
                    implode(' AND ', $where_parts)
                );
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                
                $this->log_query($sql, $params, microtime(true) - $start_time);
                return $stmt->rowCount();
                
            } else {
                // MySQL/WordPress delete
                $result = $this->wpdb->delete($table, $where);
                
                $this->log_query("DELETE FROM $table", $where, microtime(true) - $start_time);
                return $result;
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Delete error: ' . $e->getMessage(), array(
                'table' => $table,
                'where' => $where
            ));
            return false;
        }
    }

    /**
     * Begin a transaction
     *
     * @since 1.0.0
     * @return bool
     */
    public function begin_transaction() {
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                return $this->pdo->beginTransaction();
            } else {
                // MySQL/WordPress doesn't support transactions in most cases
                $this->wpdb->query('START TRANSACTION');
                return true;
            }
        } catch (Exception $e) {
            $this->log('error', 'Transaction begin error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Commit a transaction
     *
     * @since 1.0.0
     * @return bool
     */
    public function commit() {
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                return $this->pdo->commit();
            } else {
                $this->wpdb->query('COMMIT');
                return true;
            }
        } catch (Exception $e) {
            $this->log('error', 'Transaction commit error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rollback a transaction
     *
     * @since 1.0.0
     * @return bool
     */
    public function rollback() {
        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                return $this->pdo->rollBack();
            } else {
                $this->wpdb->query('ROLLBACK');
                return true;
            }
        } catch (Exception $e) {
            $this->log('error', 'Transaction rollback error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if in transaction
     *
     * @since 1.0.0
     * @return bool
     */
    public function in_transaction() {
        if ($this->db_type === 'postgresql' && $this->pdo) {
            return $this->pdo->inTransaction();
        }
        // WordPress doesn't provide transaction status
        return false;
    }

    /**
     * Get last insert ID
     *
     * @since 1.0.0
     * @return string|int
     */
    public function last_insert_id() {
        if ($this->db_type === 'postgresql' && $this->pdo) {
            return $this->pdo->lastInsertId();
        } else {
            return $this->wpdb->insert_id;
        }
    }

    /**
     * Escape string for SQL
     *
     * @since 1.0.0
     * @param string $string String to escape
     * @return string
     */
    public function escape($string) {
        if ($this->db_type === 'postgresql' && $this->pdo) {
            return $this->pdo->quote($string);
        } else {
            return $this->wpdb->esc_like($string);
        }
    }

    /**
     * Get table prefix
     *
     * @since 1.0.0
     * @return string
     */
    public function get_table_prefix() {
        if ($this->db_type === 'postgresql') {
            return ''; // PostgreSQL uses schemas instead of prefixes
        } else {
            return $this->wpdb->prefix;
        }
    }

    /**
     * Get full table name with prefix
     *
     * @since 1.0.0
     * @param string $table Base table name
     * @return string
     */
    public function get_table_name($table) {
        $prefix = $this->get_table_prefix();
        return $prefix . $table;
    }

    /**
     * Log message
     *
     * @since 1.0.0
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context data
     */
    private function log($level, $message, $context = array()) {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        } else {
            wecoza_agents_log($message, $level);
        }
    }

    /**
     * Log query
     *
     * @since 1.0.0
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param float $execution_time Execution time
     */
    private function log_query($sql, $params, $execution_time) {
        if ($this->logger) {
            $this->logger->log_query($sql, $params, $execution_time);
        }
    }

    /**
     * Test database connection
     *
     * @since 1.0.0
     * @return array Test results
     */
    public function test_connection() {
        $results = array(
            'connected' => false,
            'db_type' => $this->db_type,
            'message' => '',
            'details' => array()
        );

        try {
            if ($this->db_type === 'postgresql' && $this->pdo) {
                // Test PostgreSQL
                $stmt = $this->pdo->query('SELECT version()');
                $version = $stmt->fetchColumn();
                
                $results['connected'] = true;
                $results['message'] = __('PostgreSQL connection successful', 'wecoza-agents-plugin');
                $results['details']['version'] = $version;
                
            } else {
                // Test MySQL/WordPress
                $version = $this->wpdb->get_var('SELECT VERSION()');
                
                $results['connected'] = true;
                $results['message'] = __('MySQL connection successful', 'wecoza-agents-plugin');
                $results['details']['version'] = $version;
            }
            
        } catch (Exception $e) {
            $results['message'] = $e->getMessage();
        }

        return $results;
    }
}