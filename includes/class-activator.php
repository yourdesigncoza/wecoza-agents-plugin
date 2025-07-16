<?php
/**
 * Plugin Activator
 *
 * Handles plugin activation tasks including database setup and initial configuration.
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
 * Activator class
 *
 * @since 1.0.0
 */
class Activator {

    /**
     * Activate the plugin
     *
     * @since 1.0.0
     */
    public static function activate() {
        // Check requirements
        self::check_requirements();
        
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Create necessary directories
        self::create_directories();
        
        // Add capabilities
        self::add_capabilities();
        
        // Schedule events
        self::schedule_events();
        
        // Clear rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        set_transient('wecoza_agents_activated', true, 60);
    }

    /**
     * Check plugin requirements
     *
     * @since 1.0.0
     */
    private static function check_requirements() {
        // PHP version check
        if (version_compare(PHP_VERSION, WECOZA_AGENTS_MIN_PHP_VERSION, '<')) {
            deactivate_plugins(plugin_basename(WECOZA_AGENTS_PLUGIN_FILE));
            wp_die(
                sprintf(
                    __('WeCoza Agents Plugin requires PHP %s or higher. You are running PHP %s.', 'wecoza-agents-plugin'),
                    WECOZA_AGENTS_MIN_PHP_VERSION,
                    PHP_VERSION
                ),
                __('Plugin Activation Error', 'wecoza-agents-plugin'),
                array('back_link' => true)
            );
        }

        // WordPress version check
        global $wp_version;
        if (version_compare($wp_version, WECOZA_AGENTS_MIN_WP_VERSION, '<')) {
            deactivate_plugins(plugin_basename(WECOZA_AGENTS_PLUGIN_FILE));
            wp_die(
                sprintf(
                    __('WeCoza Agents Plugin requires WordPress %s or higher. You are running WordPress %s.', 'wecoza-agents-plugin'),
                    WECOZA_AGENTS_MIN_WP_VERSION,
                    $wp_version
                ),
                __('Plugin Activation Error', 'wecoza-agents-plugin'),
                array('back_link' => true)
            );
        }
    }

    /**
     * Create database tables
     *
     * @since 1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Get database type
        $db_type = self::get_database_type();
        
        if ($db_type === 'postgresql') {
            self::create_postgresql_tables();
        } else {
            self::create_mysql_tables($charset_collate);
        }
        
        // Update database version
        update_option(WECOZA_AGENTS_DB_VERSION_OPTION, WECOZA_AGENTS_DB_VERSION);
    }

    /**
     * Get database type
     *
     * @since 1.0.0
     * @return string 'postgresql' or 'mysql'
     */
    private static function get_database_type() {
        // Check if PostgreSQL settings exist
        $pg_host = get_option('wecoza_postgres_host');
        $pg_pass = get_option('wecoza_postgres_password');
        
        if ($pg_host && $pg_pass) {
            return 'postgresql';
        }
        
        return 'mysql';
    }

    /**
     * Create PostgreSQL tables
     *
     * @since 1.0.0
     */
    private static function create_postgresql_tables() {
        // PostgreSQL table creation SQL
        $sql = "
        CREATE TABLE IF NOT EXISTS agents (
            id SERIAL PRIMARY KEY,
            title VARCHAR(50),
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            known_as VARCHAR(255),
            gender VARCHAR(20),
            race VARCHAR(50),
            id_number VARCHAR(20),
            passport_number VARCHAR(50),
            phone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            street_address TEXT,
            city VARCHAR(255),
            province VARCHAR(255),
            postal_code VARCHAR(20),
            sace_number VARCHAR(100),
            phase_registered VARCHAR(100),
            subjects_registered TEXT,
            quantum_maths_passed BOOLEAN DEFAULT FALSE,
            quantum_science_passed BOOLEAN DEFAULT FALSE,
            criminal_record_checked BOOLEAN DEFAULT FALSE,
            criminal_record_date DATE,
            signed_agreement BOOLEAN DEFAULT FALSE,
            agreement_file_path VARCHAR(500),
            bank_name VARCHAR(255),
            account_holder VARCHAR(255),
            account_number VARCHAR(50),
            branch_code VARCHAR(20),
            account_type VARCHAR(50),
            preferred_areas TEXT,
            status VARCHAR(50) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            updated_by INT,
            CONSTRAINT email_unique UNIQUE (email),
            CONSTRAINT id_number_unique UNIQUE (id_number)
        );

        CREATE TABLE IF NOT EXISTS agent_meta (
            id SERIAL PRIMARY KEY,
            agent_id INT NOT NULL REFERENCES agents(id) ON DELETE CASCADE,
            meta_key VARCHAR(255) NOT NULL,
            meta_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT agent_meta_unique UNIQUE (agent_id, meta_key)
        );

        CREATE TABLE IF NOT EXISTS agent_notes (
            id SERIAL PRIMARY KEY,
            agent_id INT NOT NULL REFERENCES agents(id) ON DELETE CASCADE,
            note TEXT NOT NULL,
            note_type VARCHAR(50),
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS agent_absences (
            id SERIAL PRIMARY KEY,
            agent_id INT NOT NULL REFERENCES agents(id) ON DELETE CASCADE,
            absence_date DATE NOT NULL,
            reason TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Create indexes
        CREATE INDEX IF NOT EXISTS idx_agents_email ON agents(email);
        CREATE INDEX IF NOT EXISTS idx_agents_status ON agents(status);
        CREATE INDEX IF NOT EXISTS idx_agents_created_at ON agents(created_at);
        CREATE INDEX IF NOT EXISTS idx_agent_meta_agent_id ON agent_meta(agent_id);
        CREATE INDEX IF NOT EXISTS idx_agent_notes_agent_id ON agent_notes(agent_id);
        CREATE INDEX IF NOT EXISTS idx_agent_absences_agent_id ON agent_absences(agent_id);
        CREATE INDEX IF NOT EXISTS idx_agent_absences_date ON agent_absences(absence_date);
        ";
        
        // Execute PostgreSQL queries
        self::execute_postgresql_query($sql);
    }

    /**
     * Create MySQL tables
     *
     * @since 1.0.0
     * @param string $charset_collate Character set and collation
     */
    private static function create_mysql_tables($charset_collate) {
        global $wpdb;
        
        // Agents table
        $agents_table = $wpdb->prefix . 'wecoza_agents';
        $agents_sql = "CREATE TABLE IF NOT EXISTS $agents_table (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(50),
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            known_as VARCHAR(255),
            gender VARCHAR(20),
            race VARCHAR(50),
            id_number VARCHAR(20),
            passport_number VARCHAR(50),
            phone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            street_address TEXT,
            city VARCHAR(255),
            province VARCHAR(255),
            postal_code VARCHAR(20),
            sace_number VARCHAR(100),
            phase_registered VARCHAR(100),
            subjects_registered TEXT,
            quantum_maths_passed TINYINT(1) DEFAULT 0,
            quantum_science_passed TINYINT(1) DEFAULT 0,
            criminal_record_checked TINYINT(1) DEFAULT 0,
            criminal_record_date DATE,
            signed_agreement TINYINT(1) DEFAULT 0,
            agreement_file_path VARCHAR(500),
            bank_name VARCHAR(255),
            account_holder VARCHAR(255),
            account_number VARCHAR(50),
            branch_code VARCHAR(20),
            account_type VARCHAR(50),
            preferred_areas TEXT,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT(11),
            updated_by INT(11),
            PRIMARY KEY (id),
            UNIQUE KEY email_unique (email),
            UNIQUE KEY id_number_unique (id_number),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

        // Agent meta table
        $agent_meta_table = $wpdb->prefix . 'wecoza_agent_meta';
        $agent_meta_sql = "CREATE TABLE IF NOT EXISTS $agent_meta_table (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            agent_id INT(11) UNSIGNED NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY agent_meta_unique (agent_id, meta_key),
            KEY idx_agent_id (agent_id)
        ) $charset_collate;";

        // Agent notes table
        $agent_notes_table = $wpdb->prefix . 'wecoza_agent_notes';
        $agent_notes_sql = "CREATE TABLE IF NOT EXISTS $agent_notes_table (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            agent_id INT(11) UNSIGNED NOT NULL,
            note TEXT NOT NULL,
            note_type VARCHAR(50),
            created_by INT(11),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_agent_id (agent_id),
            KEY idx_created_at (created_at)
        ) $charset_collate;";

        // Agent absences table
        $agent_absences_table = $wpdb->prefix . 'wecoza_agent_absences';
        $agent_absences_sql = "CREATE TABLE IF NOT EXISTS $agent_absences_table (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            agent_id INT(11) UNSIGNED NOT NULL,
            absence_date DATE NOT NULL,
            reason TEXT,
            created_by INT(11),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_agent_id (agent_id),
            KEY idx_absence_date (absence_date)
        ) $charset_collate;";

        // Execute queries
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($agents_sql);
        dbDelta($agent_meta_sql);
        dbDelta($agent_notes_sql);
        dbDelta($agent_absences_sql);
    }

    /**
     * Execute PostgreSQL query
     *
     * @since 1.0.0
     * @param string $sql SQL query
     */
    private static function execute_postgresql_query($sql) {
        try {
            // Get PostgreSQL credentials
            $pg_host = get_option('wecoza_postgres_host', 'localhost');
            $pg_port = get_option('wecoza_postgres_port', '5432');
            $pg_dbname = get_option('wecoza_postgres_dbname', 'wecoza');
            $pg_user = get_option('wecoza_postgres_user', 'postgres');
            $pg_pass = get_option('wecoza_postgres_password', '');
            
            // Create PDO connection
            $dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname";
            $pdo = new \PDO($dsn, $pg_user, $pg_pass, array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ));
            
            // Execute query
            $pdo->exec($sql);
            
        } catch (\Exception $e) {
            error_log('WeCoza Agents: PostgreSQL table creation failed - ' . $e->getMessage());
            // Fall back to MySQL
            self::create_mysql_tables($GLOBALS['wpdb']->get_charset_collate());
        }
    }

    /**
     * Set default options
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        // Plugin version
        add_option(WECOZA_AGENTS_VERSION_OPTION, WECOZA_AGENTS_VERSION);
        
        // Plugin settings
        $default_settings = array(
            'enable_debug' => false,
            'items_per_page' => 25,
            'enable_notifications' => true,
            'notification_email' => get_option('admin_email'),
            'enable_file_uploads' => true,
            'max_file_size' => 5, // MB
            'allowed_file_types' => array('pdf', 'doc', 'docx'),
            'enable_caching' => true,
            'cache_expiration' => 3600, // 1 hour
        );
        
        add_option(WECOZA_AGENTS_SETTINGS_OPTION, $default_settings);
        
        // Database settings (if not already set)
        if (!get_option('wecoza_postgres_host')) {
            add_option('wecoza_postgres_host', '');
            add_option('wecoza_postgres_port', '5432');
            add_option('wecoza_postgres_dbname', '');
            add_option('wecoza_postgres_user', '');
            add_option('wecoza_postgres_password', '');
        }
    }

    /**
     * Create necessary directories
     *
     * @since 1.0.0
     */
    private static function create_directories() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        
        // Create plugin upload directory
        $plugin_upload_dir = $base_dir . '/wecoza-agents';
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
            
            // Create subdirectories
            wp_mkdir_p($plugin_upload_dir . '/agreements');
            wp_mkdir_p($plugin_upload_dir . '/documents');
            wp_mkdir_p($plugin_upload_dir . '/temp');
            
            // Add .htaccess for security
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<FilesMatch '\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$'>\n";
            $htaccess_content .= "    Order deny,allow\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            
            file_put_contents($plugin_upload_dir . '/.htaccess', $htaccess_content);
        }
        
        // Ensure logs directory is writable
        $logs_dir = WECOZA_AGENTS_LOGS_DIR;
        if (!is_writable($logs_dir)) {
            @chmod($logs_dir, 0755);
        }
    }

    /**
     * Add capabilities
     *
     * @since 1.0.0
     */
    private static function add_capabilities() {
        // Get administrator role
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_wecoza_agents');
            $admin->add_cap('view_wecoza_agents');
            $admin->add_cap('edit_wecoza_agents');
            $admin->add_cap('delete_wecoza_agents');
        }
        
        // Get editor role
        $editor = get_role('editor');
        if ($editor) {
            $editor->add_cap('manage_wecoza_agents');
            $editor->add_cap('view_wecoza_agents');
            $editor->add_cap('edit_wecoza_agents');
        }
    }

    /**
     * Schedule events
     *
     * @since 1.0.0
     */
    private static function schedule_events() {
        // Schedule daily cleanup
        if (!wp_next_scheduled('wecoza_agents_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'wecoza_agents_daily_cleanup');
        }
        
        // Schedule weekly reports
        if (!wp_next_scheduled('wecoza_agents_weekly_report')) {
            wp_schedule_event(time(), 'weekly', 'wecoza_agents_weekly_report');
        }
    }
}