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
- **WordPress Hooks**: Actions and filters for WordPress integration
- **Template Override System**: Templates can be overridden in active theme

### Database Architecture
- **Dual Database Support**: PostgreSQL (primary) with MySQL fallback via `DatabaseService`
- **Main Tables**: `agents`, `agent_meta`, `agent_notes`, `agent_absences`
- **Model Pattern**: Active Record pattern with built-in validation and CRUD operations
- **Validation**: SA ID checksum validation, passport format validation, comprehensive field validation

### WordPress Integration Points
- **Shortcodes**: `[wecoza_capture_agents]` (form), `[wecoza_display_agents]` (table)
- **Admin Integration**: Settings page, plugin action links
- **Asset Management**: CSS/JS enqueuing with Bootstrap 5 and jQuery dependencies
- **Hooks**: Custom actions and filters for extensibility

### Development Workflow
- **No Build Process**: Direct file loading, no compilation required
- **Manual Asset Management**: Unminified files for development, minified versions for production
- **WordPress Standards**: Follows WordPress coding standards and security practices
- **Template Development**: Templates in `templates/` directory with theme override support

### Security Features
- **Nonce Verification**: All forms use WordPress nonces
- **Capability Checks**: Proper permission verification
- **Input Sanitization**: Comprehensive data sanitization
- **SQL Injection Prevention**: Prepared statements throughout

### Key Files to Understand
- `src/Database/DatabaseService.php` - Database abstraction layer
- `src/Models/Agent.php` - Core agent model with validation
- `src/Shortcodes/CaptureAgentShortcode.php` - Form handling
- `src/Shortcodes/DisplayAgentShortcode.php` - Table display
- `templates/forms/agent-capture-form.php` - Main form template
- `templates/display/agent-display-table.php` - Table template

### Common Development Tasks
- **Adding Fields**: Modify agent model, form template, and validation rules
- **Template Modifications**: Edit files in `templates/` directory
- **Database Changes**: Update `DatabaseService` queries and model validation
- **Styling**: Add CSS to main theme's `ydcoza-styles.css` file (per global instructions)

### Plugin Configuration
- **Database Settings**: WordPress options for PostgreSQL connection
- **Plugin Options**: `wecoza_agents_settings` for plugin configuration
- **Constants**: Defined in main plugin file and `includes/class-constants.php`

### File Upload Handling
- **Agent Agreements**: File upload support with validation
- **Storage**: WordPress uploads directory with security measures
- **Validation**: File type and size validation

### Frontend Features
- **Responsive Design**: Bootstrap 5 integration
- **Interactive Elements**: jQuery and Select2 for enhanced UX
- **Modal Display**: Agent details in responsive modals
- **Search/Filter**: Real-time table filtering and search

### Testing and Validation
- **SA ID Validation**: Built-in South African ID checksum validation
- **Form Validation**: Client-side and server-side validation
- **Data Integrity**: Comprehensive model validation before database operations