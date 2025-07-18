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
        
        // Connect to PostgreSQL (required)
        if (!$this->connect_postgresql()) {
            throw new Exception('PostgreSQL connection is required but failed. Please check your PostgreSQL credentials.');
        }
        $this->connected = true;
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
     * @return string Always returns 'postgresql'
     */
    public function get_db_type() {
        return 'postgresql';
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
            // PostgreSQL query only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $this->log_query($sql, $params, microtime(true) - $start_time);
            return $stmt;
            
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
            // PostgreSQL insert only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            
            $fields = array_keys($data);
            $values = array_map(function($field) { return ':' . $field; }, $fields);
            
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s) RETURNING agent_id',
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
            return $result['agent_id'] ?? false;
            
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
            // PostgreSQL update only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            
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
            // PostgreSQL delete only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            
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
            // PostgreSQL transaction only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            return $this->pdo->beginTransaction();
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
            // PostgreSQL transaction only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            return $this->pdo->commit();
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
            // PostgreSQL transaction only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            return $this->pdo->rollBack();
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
        if (!$this->pdo) {
            return false;
        }
        return $this->pdo->inTransaction();
    }

    /**
     * Get last insert ID
     *
     * @since 1.0.0
     * @return string|int
     */
    public function last_insert_id() {
        if (!$this->pdo) {
            throw new Exception('PostgreSQL connection not available');
        }
        return $this->pdo->lastInsertId();
    }

    /**
     * Escape string for SQL
     *
     * @since 1.0.0
     * @param string $string String to escape
     * @return string
     */
    public function escape($string) {
        if (!$this->pdo) {
            throw new Exception('PostgreSQL connection not available');
        }
        return $this->pdo->quote($string);
    }

    /**
     * Get table prefix
     *
     * @since 1.0.0
     * @return string
     */
    public function get_table_prefix() {
        return ''; // PostgreSQL uses schemas instead of prefixes
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
            'db_type' => 'postgresql',
            'message' => '',
            'details' => array()
        );

        try {
            // Test PostgreSQL only
            if (!$this->pdo) {
                throw new Exception('PostgreSQL connection not available');
            }
            
            $stmt = $this->pdo->query('SELECT version()');
            $version = $stmt->fetchColumn();
            
            $results['connected'] = true;
            $results['message'] = __('PostgreSQL connection successful', 'wecoza-agents-plugin');
            $results['details']['version'] = $version;
            
        } catch (Exception $e) {
            $results['message'] = $e->getMessage();
        }

        return $results;
    }
}