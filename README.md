# WeCoza Agents Plugin

Comprehensive agent management system for WeCoza - manage agent profiles, qualifications, and assignments.

## Description

The WeCoza Agents Plugin provides a complete solution for managing educational agents within the WeCoza platform. It includes features for agent registration, profile management, qualification tracking, and assignment handling.

## Features

- **Agent Registration**: Comprehensive form for capturing agent information
- **Profile Management**: View and edit agent profiles with all details
- **SA ID Validation**: Built-in South African ID number validation with checksum
- **Passport Support**: Alternative identification for non-SA agents
- **Qualification Tracking**: SACE registration and subject management
- **Banking Details**: Secure storage of payment information
- **Area Preferences**: Multiple location preference selection
- **Document Management**: Upload and store agent agreements
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- PostgreSQL database (with MySQL fallback)
- Bootstrap 5 (provided by parent theme)
- jQuery (WordPress core)

## Installation

1. Upload the `wecoza-agents-plugin` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure PostgreSQL database settings if needed

## Usage

### Shortcodes

#### Agent Capture Form
```
[wecoza_capture_agents]
```
Displays the agent registration/edit form.

#### Agent Display Table
```
[wecoza_display_agents]
```
Shows a searchable table of all agents with page-based details view.

## Database Configuration

The plugin uses PostgreSQL as the primary database. Configure the connection in WordPress options:

- `wecoza_postgres_host`
- `wecoza_postgres_port`
- `wecoza_postgres_dbname`
- `wecoza_postgres_user`
- `wecoza_postgres_password`

## Development

### Directory Structure

```
wecoza-agents-plugin/
├── assets/          # CSS, JS, and image files
├── includes/        # Core plugin classes
├── languages/       # Translation files
├── logs/           # Debug and error logs
├── src/            # PSR-4 autoloaded classes
├── templates/      # Template files
└── tests/          # Unit tests
```

### Coding Standards

This plugin follows WordPress coding standards. Run PHPCS to check:

```bash
composer check-cs
```

### Building Assets

Currently, no build process is required. All assets are loaded unminified for development.

## Hooks and Filters

### Actions

- `wecoza_agents_before_capture_form` - Before agent form display
- `wecoza_agents_after_capture_form` - After agent form display
- `wecoza_agents_before_display_table` - Before agents table
- `wecoza_agents_after_display_table` - After agents table

### Filters

- `wecoza_agents_form_fields` - Modify form fields
- `wecoza_agents_table_columns` - Modify table columns
- `wecoza_agents_validation_rules` - Add/modify validation rules

## Support

For support, please contact the WeCoza development team at dev@wecoza.co.za

## License

GPL v2 or later - see LICENSE file for details.

## Credits

Developed by the WeCoza Development Team.
