# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## WordPress Plugin Development

### Plugin Structure
- **Main Plugin File**: `wecoza-agents-plugin.php` - Entry point with plugin headers, constants, and initialization
- **Core Classes**: `includes/class-plugin.php` (main orchestrator), `includes/class-activator.php` (setup), `includes/class-deactivator.php` (cleanup)
- **PSR-4 Autoloaded Classes**: `src/` directory with Database, Models, Shortcodes, Forms, Helpers, and Cache namespaces
- **Templates**: `templates/` directory supporting theme overrides following WordPress conventions

### Key Development Patterns
- **Singleton Pattern**: Main plugin class uses singleton for single instance
- **Component-Based Architecture**: Modular components loaded by main plugin class
- **Component Loading Order**: Core classes → Database → Shortcodes → Helpers → Forms → Models
- **WordPress Hooks**: Actions and filters for WordPress integration
- **Template Override System**: Templates can be overridden in active theme

### Database Architecture
- **Dual Database Support**: PostgreSQL (primary) with MySQL fallback via `DatabaseService`
- **Main Tables**: `wecoza_agents`, `wecoza_agent_meta`, `wecoza_agent_notes`, `wecoza_agent_absences`
- **Model Pattern**: Active Record pattern with built-in validation and CRUD operations
- **Connection Management**: Automatic failover from PostgreSQL to MySQL if connection fails
- **Field Mapping**: Form fields to database columns via `FormHelpers::getFieldMapping()`
- **Validation**: SA ID checksum validation, passport format validation, comprehensive field validation

### WordPress Integration Points
- **Shortcodes**: `[wecoza_capture_agents]` (form), `[wecoza_display_agents]` (table), `[wecoza_single_agent]` (single view)
- **Admin Integration**: Settings page, plugin action links
- **Asset Management**: CSS/JS enqueuing with Bootstrap 5 and jQuery dependencies
- **Hooks**: Custom actions and filters for extensibility

### Development Workflow
- **No Build Process**: Direct file loading, no compilation required
- **Manual Asset Management**: Unminified files for development, minified versions for production
- **WordPress Standards**: Follows WordPress coding standards and security practices
- **Template Development**: Templates in `templates/` directory with theme override support

### Development Commands
- **Code Standards**: `composer check-cs` (referenced in README, requires composer setup)
- **No Build Process**: Direct file editing workflow - no npm, webpack, or compilation required
- **Asset Loading**: Unminified files for development, minified versions for production
- **Database Setup**: Automatic via plugin activation through WordPress admin
- **Debug Logging**: Plugin logs to `/logs/` directory and WordPress debug log

### Security Features
- **Nonce Verification**: All forms use WordPress nonces
- **Capability Checks**: Proper permission verification
- **Input Sanitization**: Comprehensive data sanitization
- **SQL Injection Prevention**: Prepared statements throughout

### Key Files to Understand
- `src/Database/DatabaseService.php` - Database abstraction layer with PostgreSQL/MySQL failover
- `src/Database/AgentQueries.php` - Agent-specific database queries and operations
- `src/Models/Agent.php` - Core agent model with Active Record pattern and validation
- `src/Shortcodes/CaptureAgentShortcode.php` - Form handling and agent creation/editing
- `src/Shortcodes/DisplayAgentShortcode.php` - Table display with search and filtering
- `src/Shortcodes/SingleAgentShortcode.php` - Single agent display pages
- `src/Helpers/FormHelpers.php` - Form field mapping and rendering utilities
- `src/Helpers/ValidationHelper.php` - SA ID checksum and validation logic
- `templates/forms/agent-capture-form.php` - Main form template
- `templates/display/agent-display-table.php` - Agents table with search/filter
- `templates/display/agent-single-display.php` - Single agent page template

### Agent Search and Statistics System
- **Real-time Search**: JavaScript-based filtering across all agent fields
- **Statistics Display**: Dynamic counters for total agents, active/inactive counts
- **Field-specific Filtering**: Search by name, email, phone, SA ID, area preferences
- **Page Navigation**: Click-to-view agent details on dedicated pages
- **Data Attributes**: Searchable data stored in HTML data attributes for client-side filtering

### Common Development Tasks
- **Adding Fields**: Modify agent model, form template, validation rules, and field mapping
- **Template Modifications**: Edit files in `templates/` directory
- **Database Changes**: Update `DatabaseService` queries, model validation, and field mapping
- **Search Enhancement**: Add new fields to searchable data attributes in templates
- **Styling**: Add CSS to main theme's `ydcoza-styles.css` file (per global instructions)

### Plugin Configuration
- **Database Settings**: WordPress options for PostgreSQL connection:
  - `wecoza_postgres_host`, `wecoza_postgres_port`, `wecoza_postgres_dbname`
  - `wecoza_postgres_user`, `wecoza_postgres_password`
- **Plugin Options**: `wecoza_agents_settings` for plugin configuration
- **Constants**: Defined in main plugin file and `includes/class-constants.php`
- **Requirements**: WordPress 6.0+, PHP 7.4+, Bootstrap 5 (from parent theme)

### File Upload Handling
- **Agent Agreements**: File upload support with validation
- **Storage**: WordPress uploads directory with security measures
- **Validation**: File type and size validation

### Frontend Features
- **Responsive Design**: Bootstrap 5 integration with Phoenix design system
- **Interactive Elements**: jQuery-based interactions and form validation
- **Page Display**: Agent details on dedicated responsive pages
- **Search/Filter**: Real-time table filtering, search, and statistics
- **Google Maps**: Address autocomplete integration
- **File Uploads**: Agent agreement document handling with validation

### Testing and Validation
- **No Test Framework**: Manual testing through WordPress admin and frontend
- **SA ID Validation**: Built-in South African ID checksum validation
- **Form Validation**: Client-side and server-side validation
- **Data Integrity**: Comprehensive model validation before database operations

### WordPress Hooks Integration
- **Actions**: `wecoza_agents_before_capture_form`, `wecoza_agents_after_capture_form`, `wecoza_agents_before_display_table`, `wecoza_agents_after_display_table`
- **Filters**: `wecoza_agents_form_fields`, `wecoza_agents_table_columns`, `wecoza_agents_validation_rules`
- **Custom Hooks**: Extensive hook system for third-party extensions

### Debugging and Logging
- **Debug Directory**: `/logs/` directory for plugin-specific logs
- **Database Logger**: `src/Database/DatabaseLogger.php` for query logging
- **Error Handling**: Comprehensive error logging with WordPress debug integration
- **Console Logging**: Client-side debugging via `console.txt` and JavaScript logging