# Changelog

All notable changes to the WeCoza Agents Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial plugin architecture and foundation
- PSR-4 autoloading support
- WordPress coding standards configuration
- Comprehensive documentation

### Changed
- Migrated agent functionality from theme to standalone plugin

### Security
- Added index.php files to all directories
- Implemented nonce verification for all forms

## [1.0.0] - 2025-01-16

### Added
- Initial release of WeCoza Agents Plugin
- Agent capture form shortcode `[wecoza_capture_agents]`
- Agent display table shortcode `[wecoza_display_agents]`
- SA ID number validation with checksum algorithm
- Passport number validation
- Bootstrap 5 integration
- Select2 multi-select for location preferences
- Form field validation (client and server-side)
- Page-based view for agent details with organized layout
- PostgreSQL database support with MySQL fallback
- Responsive design for mobile devices
- File upload support for agreements and documents
- Localization support
- Debug logging functionality

### Features from Theme Migration
- Complete agent registration form with 30+ fields
- Personal information management
- Contact details storage
- SACE registration tracking
- Banking information management
- Criminal record verification fields
- Quantum test results tracking
- Preferred working area selection (3 locations)
- Agreement document upload

### Known Issues
- Database operations not yet implemented (static data only)
- Edit/Delete functionality pending implementation
- Search and filter features not functional
- File uploads not processed
- AJAX operations not implemented

### Dependencies
- WordPress 6.0+
- PHP 7.4+
- Bootstrap 5 (from parent theme)
- jQuery (WordPress core)
- Select2 4.1.0-rc.0

[Unreleased]: https://github.com/wecoza/wecoza-agents-plugin/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/wecoza/wecoza-agents-plugin/releases/tag/v1.0.0