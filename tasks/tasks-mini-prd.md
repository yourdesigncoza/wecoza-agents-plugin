## Relevant Files

- `src/Shortcodes/SingleAgentShortcode.php` - New shortcode class that handles single agent display functionality
- `templates/display/agent-single-display.php` - Template file for rendering the full-page agent view
- `src/Shortcodes/DisplayAgentShortcode.php` - Existing file that needs modification to add URL helper method
- `templates/display/agent-display-table.php` - Existing template that needs view details action update
- `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css` - Theme CSS file for custom styling
- `includes/class-plugin.php` - Main plugin class to ensure shortcode is loaded
- `CLAUDE.md` - Documentation file to update with new functionality

### Notes

- The SingleAgentShortcode class should extend AbstractShortcode following existing patterns
- URL parameter handling should use WordPress sanitization functions
- Permission checks should use WordPress capability system
- All translatable strings must use WordPress i18n functions

## Tasks

- [x] 1.0 Create SingleAgentShortcode Class
  - [x] 1.1 Create new file `src/Shortcodes/SingleAgentShortcode.php` with proper namespace and class declaration
  - [x] 1.2 Extend AbstractShortcode class and implement constructor
  - [x] 1.3 Implement `init()` method to set shortcode tag as 'wecoza_display_single_agent' and define default attributes
  - [x] 1.4 Implement `check_permissions()` method to verify if user can view agents (return true for now)
  - [x] 1.5 Implement `enqueue_assets()` method to load Bootstrap and any required scripts
  - [x] 1.6 Create `get_agent_by_id($agent_id)` method to retrieve agent from hardcoded data
  - [x] 1.7 Add error handling in `get_agent_by_id()` for invalid or missing agent IDs
  - [x] 1.8 Implement `render_shortcode($atts, $content)` method to handle the main rendering logic
  - [x] 1.9 Add URL parameter handling in render method to get agent_id from $_GET
  - [x] 1.10 Create `get_back_url()` method to generate URL back to agents list page
  - [x] 1.11 Pass all required variables to template using `load_template()` method

- [x] 2.0 Build Agent Single Display Template
  - [x] 2.1 Create new file `templates/display/agent-single-display.php` with WordPress security check
  - [x] 2.2 Add PHP documentation header explaining template purpose and available variables
  - [x] 2.3 Create loading state HTML with spinner and "Loading agent details..." message
  - [x] 2.4 Create error state HTML for "Agent not found" and "Invalid agent ID" scenarios
  - [x] 2.5 Build header section with "Back to Agents" button (left) and Edit/Delete buttons (right)
  - [x] 2.6 Create Personal Info Card showing full name, gender, race, phone, and email
  - [x] 2.7 Create Professional Status Card showing agent ID, status, and SACE registration
  - [x] 2.8 Create Qualifications Card showing highest qualification, quantum tests, and training
  - [x] 2.9 Create Agreement Status Card showing agreement signed status, date, and banking info
  - [x] 2.10 Build Personal Information detailed section with all personal fields in table format
  - [x] 2.11 Build Professional Information section with SACE, quantum, and working areas
  - [x] 2.12 Build Compliance & Documentation section with criminal record and agreement details
  - [x] 2.13 Build Administrative Information section with system dates and notes
  - [x] 2.14 Add responsive Bootstrap classes for mobile, tablet, and desktop layouts
  - [x] 2.15 Implement proper escaping for all output using esc_html(), esc_attr(), esc_url()

- [x] 3.0 Update Agent Table Navigation
  - [x] 3.1 Open `src/Shortcodes/DisplayAgentShortcode.php` and add `get_view_url($agent_id)` method
  - [x] 3.2 Implement `get_view_url()` to return URL to single agent page with agent_id parameter
  - [x] 3.3 Use `home_url()` or `get_permalink()` to generate proper WordPress URLs
  - [x] 3.4 Open `templates/display/agent-display-table.php` and locate View Details button
  - [x] 3.5 Remove `data-bs-toggle="modal"` and `data-bs-target="#agentModal"` attributes
  - [x] 3.6 Change button element to anchor tag or add onclick redirect
  - [x] 3.7 Update button to use `get_view_url()` method for href attribute
  - [x] 3.8 Test that clicking View Details redirects to single agent page with correct ID

- [ ] 4.0 Set Up WordPress Page and Routing
  - [ ] 4.1 Log into WordPress admin and navigate to Pages > Add New
  - [ ] 4.2 Create new page with title "View Agent" or "Agent Details"
  - [ ] 4.3 Add shortcode `[wecoza_display_single_agent]` to page content
  - [ ] 4.4 Set page slug to 'agent-view' or 'view-agent' in permalink settings
  - [ ] 4.5 Select appropriate page template (default or full-width)
  - [ ] 4.6 Set page visibility to Public
  - [ ] 4.7 Publish the page and note the full URL
  - [ ] 4.8 Update any hardcoded URLs in code to use this new page URL
  - [ ] 4.9 Test page loads correctly without agent_id parameter (should show error)
  - [ ] 4.10 Test page loads correctly with valid agent_id parameter

- [ ] 5.0 Apply Styling and Responsive Design
  - [ ] 5.1 Open `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`
  - [ ] 5.2 Add CSS class `.wecoza-single-agent-display` for main container styling
  - [ ] 5.3 Style summary cards with proper spacing, borders, and shadows
  - [ ] 5.4 Add hover effects for action buttons and cards
  - [ ] 5.5 Style detailed information tables with alternating row colors
  - [ ] 5.6 Add responsive breakpoints for tablet view (768px)
  - [ ] 5.7 Add responsive breakpoints for mobile view (576px)
  - [ ] 5.8 Ensure cards stack properly on mobile devices
  - [ ] 5.9 Add print media query to optimize layout for printing
  - [ ] 5.10 Test responsive design on various screen sizes
  - [ ] 5.11 Add CSS transitions for smooth hover effects
  - [ ] 5.12 Ensure consistent spacing using Bootstrap utility classes