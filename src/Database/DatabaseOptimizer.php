<?php
/**
 * Database Optimizer
 *
 * Handles database optimization including indexes, query optimization, and performance monitoring.
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
 * Database Optimizer class
 *
 * @since 1.0.0
 */
class DatabaseOptimizer {

    /**
     * Database service instance
     *
     * @var DatabaseService
     */
    private $db;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->db = DatabaseService::getInstance();
    }

    /**
     * Optimize database tables
     *
     * @since 1.0.0
     */
    public function optimize_tables() {
        // Create all necessary indexes
        $this->create_indexes();
        
        // Optimize table structure
        $this->optimize_table_structure();
        
        // Update table statistics
        $this->update_table_statistics();
        
        // Log optimization
        $this->log_optimization();
    }

    /**
     * Create database indexes
     *
     * @since 1.0.0
     */
    private function create_indexes() {
        $indexes = $this->get_required_indexes();
        
        foreach ($indexes as $table => $table_indexes) {
            foreach ($table_indexes as $index) {
                $this->create_index($table, $index);
            }
        }
    }

    /**
     * Get required indexes for all tables
     *
     * @since 1.0.0
     * @return array Array of indexes grouped by table
     */
    private function get_required_indexes() {
        return array(
            'agents' => $this->get_agents_table_indexes(),
            'agent_meta' => $this->get_agent_meta_table_indexes(),
            'agent_notes' => $this->get_agent_notes_table_indexes(),
            'agent_absences' => $this->get_agent_absences_table_indexes(),
        );
    }

    /**
     * Get indexes for agents table
     *
     * @since 1.0.0
     * @return array Array of index definitions
     */
    private function get_agents_table_indexes() {
        return array(
            // Single column indexes
            array(
                'name' => 'idx_agents_status',
                'columns' => array('status'),
                'type' => 'btree',
                'purpose' => 'Fast filtering by status'
            ),
            array(
                'name' => 'idx_agents_email',
                'columns' => array('email'),
                'type' => 'btree',
                'unique' => true,
                'purpose' => 'Fast email lookups and uniqueness'
            ),
            array(
                'name' => 'idx_agents_id_number',
                'columns' => array('id_number'),
                'type' => 'btree',
                'unique' => true,
                'purpose' => 'Fast ID number lookups and uniqueness'
            ),
            array(
                'name' => 'idx_agents_created_at',
                'columns' => array('created_at'),
                'type' => 'btree',
                'purpose' => 'Fast ordering by creation date'
            ),
            array(
                'name' => 'idx_agents_updated_at',
                'columns' => array('updated_at'),
                'type' => 'btree',
                'purpose' => 'Fast ordering by update date'
            ),
            
            // Composite indexes
            array(
                'name' => 'idx_agents_status_created',
                'columns' => array('status', 'created_at'),
                'type' => 'btree',
                'purpose' => 'Fast filtering by status with date ordering'
            ),
            array(
                'name' => 'idx_agents_status_updated',
                'columns' => array('status', 'updated_at'),
                'type' => 'btree',
                'purpose' => 'Fast filtering by status with update ordering'
            ),
            
            // Search indexes
            array(
                'name' => 'idx_agents_name_search',
                'columns' => array('first_name', 'last_name'),
                'type' => 'btree',
                'purpose' => 'Fast name-based searches'
            ),
            array(
                'name' => 'idx_agents_phone',
                'columns' => array('phone'),
                'type' => 'btree',
                'purpose' => 'Fast phone number searches'
            ),
            
            // Full-text search indexes (PostgreSQL only)
            array(
                'name' => 'idx_agents_fulltext',
                'columns' => array('first_name', 'last_name', 'email'),
                'type' => 'gin',
                'postgresql_only' => true,
                'purpose' => 'Full-text search optimization'
            ),
        );
    }

    /**
     * Get indexes for agent_meta table
     *
     * @since 1.0.0
     * @return array Array of index definitions
     */
    private function get_agent_meta_table_indexes() {
        return array(
            array(
                'name' => 'idx_agent_meta_agent_id',
                'columns' => array('agent_id'),
                'type' => 'btree',
                'purpose' => 'Fast agent meta lookups'
            ),
            array(
                'name' => 'idx_agent_meta_key',
                'columns' => array('meta_key'),
                'type' => 'btree',
                'purpose' => 'Fast meta key lookups'
            ),
            array(
                'name' => 'idx_agent_meta_agent_key',
                'columns' => array('agent_id', 'meta_key'),
                'type' => 'btree',
                'unique' => true,
                'purpose' => 'Fast agent-specific meta lookups'
            ),
        );
    }

    /**
     * Get indexes for agent_notes table
     *
     * @since 1.0.0
     * @return array Array of index definitions
     */
    private function get_agent_notes_table_indexes() {
        return array(
            array(
                'name' => 'idx_agent_notes_agent_id',
                'columns' => array('agent_id'),
                'type' => 'btree',
                'purpose' => 'Fast agent notes lookups'
            ),
            array(
                'name' => 'idx_agent_notes_created',
                'columns' => array('created_at'),
                'type' => 'btree',
                'purpose' => 'Fast note ordering'
            ),
            array(
                'name' => 'idx_agent_notes_agent_created',
                'columns' => array('agent_id', 'created_at'),
                'type' => 'btree',
                'purpose' => 'Fast agent-specific note ordering'
            ),
        );
    }

    /**
     * Get indexes for agent_absences table
     *
     * @since 1.0.0
     * @return array Array of index definitions
     */
    private function get_agent_absences_table_indexes() {
        return array(
            array(
                'name' => 'idx_agent_absences_agent_id',
                'columns' => array('agent_id'),
                'type' => 'btree',
                'purpose' => 'Fast agent absence lookups'
            ),
            array(
                'name' => 'idx_agent_absences_start_date',
                'columns' => array('start_date'),
                'type' => 'btree',
                'purpose' => 'Fast date range queries'
            ),
            array(
                'name' => 'idx_agent_absences_end_date',
                'columns' => array('end_date'),
                'type' => 'btree',
                'purpose' => 'Fast date range queries'
            ),
            array(
                'name' => 'idx_agent_absences_date_range',
                'columns' => array('start_date', 'end_date'),
                'type' => 'btree',
                'purpose' => 'Fast date range overlap queries'
            ),
        );
    }

    /**
     * Create a single index
     *
     * @since 1.0.0
     * @param string $table Table name
     * @param array $index Index definition
     */
    private function create_index($table, $index) {
        $table_name = $this->get_table_name($table);
        
        // Skip PostgreSQL-only indexes on MySQL
        if (isset($index['postgresql_only']) && $index['postgresql_only'] && $this->db->get_db_type() !== 'postgresql') {
            return;
        }
        
        // Check if index already exists
        if ($this->index_exists($table_name, $index['name'])) {
            return;
        }
        
        // Build index SQL
        $sql = $this->build_index_sql($table_name, $index);
        
        if ($sql) {
            try {
                $this->db->query($sql);
                $this->log_index_creation($table, $index);
            } catch (Exception $e) {
                $this->log_index_error($table, $index, $e->getMessage());
            }
        }
    }

    /**
     * Build index SQL statement
     *
     * @since 1.0.0
     * @param string $table_name Full table name
     * @param array $index Index definition
     * @return string SQL statement
     */
    private function build_index_sql($table_name, $index) {
        $columns = implode(', ', $index['columns']);
        $unique = isset($index['unique']) && $index['unique'] ? 'UNIQUE ' : '';
        
        if ($this->db->get_db_type() === 'postgresql') {
            $type = isset($index['type']) ? " USING {$index['type']}" : '';
            $sql = "CREATE {$unique}INDEX IF NOT EXISTS {$index['name']} ON {$table_name}{$type} ({$columns})";
        } else {
            // MySQL
            $type = isset($index['type']) && $index['type'] === 'fulltext' ? 'FULLTEXT ' : '';
            $sql = "CREATE {$unique}{$type}INDEX {$index['name']} ON {$table_name} ({$columns})";
        }
        
        return $sql;
    }

    /**
     * Check if index exists
     *
     * @since 1.0.0
     * @param string $table_name Table name
     * @param string $index_name Index name
     * @return bool Whether index exists
     */
    private function index_exists($table_name, $index_name) {
        if ($this->db->get_db_type() === 'postgresql') {
            $sql = "SELECT 1 FROM pg_indexes WHERE tablename = :table AND indexname = :index";
            $params = array('table' => $table_name, 'index' => $index_name);
        } else {
            $sql = "SHOW INDEX FROM {$table_name} WHERE Key_name = :index";
            $params = array('index' => $index_name);
        }
        
        $result = $this->db->query($sql, $params);
        return !empty($result);
    }

    /**
     * Get full table name
     *
     * @since 1.0.0
     * @param string $table Short table name
     * @return string Full table name
     */
    private function get_table_name($table) {
        $tables = array(
            'agents' => $this->db->get_agents_table(),
            'agent_meta' => $this->db->get_agent_meta_table(),
            'agent_notes' => $this->db->get_agent_notes_table(),
            'agent_absences' => $this->db->get_agent_absences_table(),
        );
        
        return isset($tables[$table]) ? $tables[$table] : $table;
    }

    /**
     * Optimize table structure
     *
     * @since 1.0.0
     */
    private function optimize_table_structure() {
        $tables = array(
            $this->db->get_agents_table(),
            $this->db->get_agent_meta_table(),
            $this->db->get_agent_notes_table(),
            $this->db->get_agent_absences_table(),
        );
        
        foreach ($tables as $table) {
            $this->optimize_single_table($table);
        }
    }

    /**
     * Optimize a single table
     *
     * @since 1.0.0
     * @param string $table Table name
     */
    private function optimize_single_table($table) {
        if ($this->db->get_db_type() === 'postgresql') {
            // PostgreSQL: Analyze table to update statistics
            $sql = "ANALYZE {$table}";
        } else {
            // MySQL: Optimize table
            $sql = "OPTIMIZE TABLE {$table}";
        }
        
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log("WeCoza Agents: Failed to optimize table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Update table statistics
     *
     * @since 1.0.0
     */
    private function update_table_statistics() {
        if ($this->db->get_db_type() === 'postgresql') {
            // Update PostgreSQL statistics
            $sql = "SELECT pg_stat_reset()";
            try {
                $this->db->query($sql);
            } catch (Exception $e) {
                error_log("WeCoza Agents: Failed to reset PostgreSQL statistics: " . $e->getMessage());
            }
        }
    }

    /**
     * Get database performance metrics
     *
     * @since 1.0.0
     * @return array Performance metrics
     */
    public function get_performance_metrics() {
        $metrics = array(
            'index_usage' => $this->get_index_usage_stats(),
            'query_performance' => $this->get_query_performance_stats(),
            'table_sizes' => $this->get_table_sizes(),
            'optimization_status' => $this->get_optimization_status(),
        );
        
        return $metrics;
    }

    /**
     * Get index usage statistics
     *
     * @since 1.0.0
     * @return array Index usage stats
     */
    private function get_index_usage_stats() {
        if ($this->db->get_db_type() === 'postgresql') {
            $sql = "SELECT 
                schemaname, 
                tablename, 
                indexname, 
                idx_scan, 
                idx_tup_read, 
                idx_tup_fetch 
            FROM pg_stat_user_indexes 
            WHERE schemaname = 'public' 
            AND tablename LIKE '%agents%'";
        } else {
            $sql = "SELECT 
                TABLE_NAME, 
                INDEX_NAME, 
                SEQ_IN_INDEX, 
                COLUMN_NAME, 
                CARDINALITY 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME LIKE '%agents%'";
        }
        
        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Get query performance statistics
     *
     * @since 1.0.0
     * @return array Query performance stats
     */
    private function get_query_performance_stats() {
        // This would typically integrate with query monitoring
        // For now, return basic stats
        return array(
            'avg_query_time' => 0,
            'slow_queries' => 0,
            'total_queries' => 0,
        );
    }

    /**
     * Get table sizes
     *
     * @since 1.0.0
     * @return array Table sizes
     */
    private function get_table_sizes() {
        if ($this->db->get_db_type() === 'postgresql') {
            $sql = "SELECT 
                tablename, 
                pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size 
            FROM pg_tables 
            WHERE schemaname = 'public' 
            AND tablename LIKE '%agents%'";
        } else {
            $sql = "SELECT 
                TABLE_NAME, 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME LIKE '%agents%'";
        }
        
        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Get optimization status
     *
     * @since 1.0.0
     * @return array Optimization status
     */
    private function get_optimization_status() {
        return array(
            'indexes_created' => $this->count_created_indexes(),
            'last_optimization' => get_option('wecoza_agents_last_optimization', 'Never'),
            'optimization_needed' => $this->is_optimization_needed(),
        );
    }

    /**
     * Count created indexes
     *
     * @since 1.0.0
     * @return int Number of created indexes
     */
    private function count_created_indexes() {
        $count = 0;
        $indexes = $this->get_required_indexes();
        
        foreach ($indexes as $table => $table_indexes) {
            foreach ($table_indexes as $index) {
                if ($this->index_exists($this->get_table_name($table), $index['name'])) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * Check if optimization is needed
     *
     * @since 1.0.0
     * @return bool Whether optimization is needed
     */
    private function is_optimization_needed() {
        $last_optimization = get_option('wecoza_agents_last_optimization', 0);
        $optimization_interval = 7 * 24 * 60 * 60; // 7 days
        
        return (time() - $last_optimization) > $optimization_interval;
    }

    /**
     * Log optimization completion
     *
     * @since 1.0.0
     */
    private function log_optimization() {
        update_option('wecoza_agents_last_optimization', time());
        
        if (WP_DEBUG) {
            error_log('WeCoza Agents: Database optimization completed');
        }
    }

    /**
     * Log index creation
     *
     * @since 1.0.0
     * @param string $table Table name
     * @param array $index Index definition
     */
    private function log_index_creation($table, $index) {
        if (WP_DEBUG) {
            error_log("WeCoza Agents: Created index {$index['name']} on table {$table}");
        }
    }

    /**
     * Log index creation error
     *
     * @since 1.0.0
     * @param string $table Table name
     * @param array $index Index definition
     * @param string $error Error message
     */
    private function log_index_error($table, $index, $error) {
        error_log("WeCoza Agents: Failed to create index {$index['name']} on table {$table}: {$error}");
    }
}