# WeCoza Agents Plugin - Single Agent Display PRD

## Executive Summary
Create a dedicated single agent display functionality that replaces the current modal-based viewing with a full-page layout. This feature will provide a comprehensive view of individual agent details using a new shortcode `[wecoza_display_single_agent]`, following the pattern established by the legacy single class display.

## Project Overview

### Current State
- **Modal-based viewing**: Agent details currently open in a modal popup
- **Limited navigation**: No direct URL access to individual agent views
- **Constrained layout**: Modal restricts the amount of information displayed
- **Poor mobile experience**: Modals are not optimal for mobile devices

### Desired State
- **Dedicated page viewing**: Each agent has a dedicated page with a unique URL
- **Comprehensive layout**: Full-page display showing all agent information
- **Direct navigation**: Ability to share and bookmark individual agent pages
- **Responsive design**: Optimized for all device sizes

## Functional Requirements

### 1. New Shortcode: `[wecoza_display_single_agent]`

#### Parameters
- `agent_id` (required): The ID of the agent to display
- Can be passed via URL parameter (e.g., `?agent_id=123`)

#### Usage Example
```
[wecoza_display_single_agent]
```

### 2. SingleAgentShortcode Class

#### Location
`src/Shortcodes/SingleAgentShortcode.php`

#### Key Methods
- `init()`: Initialize shortcode with tag and default attributes
- `check_permissions()`: Verify user has permission to view agents
- `enqueue_assets()`: Load required CSS and JavaScript
- `render_shortcode()`: Process request and render template
- `get_agent_by_id()`: Retrieve single agent data
- `get_back_url()`: Generate URL back to agents list

### 3. Agent Single Display Template

#### Location
`templates/display/agent-single-display.php`

#### Layout Structure

##### A. Header Section
- Back to Agents button (left-aligned)
- Edit/Delete action buttons (right-aligned, permission-based)

##### B. Agent Summary Cards (Top Row)
Display key information in card format:
1. **Personal Info Card**
   - Full Name
   - Gender & Race
   - Contact (Phone/Email)

2. **Professional Status Card**
   - Agent ID
   - Status (Active/Inactive)
   - SACE Registration Status

3. **Qualifications Card**
   - Highest Qualification
   - Quantum Test Status
   - Training Completion

4. **Agreement Status Card**
   - Agreement Signed (Yes/No)
   - Agreement Date
   - Banking Details Status

##### C. Detailed Information Sections

###### Section 1: Personal Information
- **Basic Details**: Title, First Name, Last Name, Known As, Initials
- **Demographics**: Gender, Race, ID Type (SA ID/Passport), ID Number
- **Contact**: Phone, Email, Full Address (Street, City, Province, Postal Code)

###### Section 2: Professional Information
- **SACE Registration**: Number, Phase Registered, Subjects Registered
- **Qualifications**: Highest Qualification details
- **Quantum Tests**: Communications, Mathematics, Science, Training status
- **Preferred Working Areas**: Up to 3 preferred locations

###### Section 3: Compliance & Documentation
- **Criminal Record**: Check status, Date, File reference
- **Agreement**: Signed status, Date, File path
- **Banking Details**: Bank Name, Account Holder, Account Number, Branch Code, Account Type

###### Section 4: Administrative Information
- **System Data**: Date Loaded, Created Date, Updated Date
- **User Tracking**: Created By, Updated By
- **Notes**: Agent notes and additional information

## Technical Specifications

### File Structure
```
src/Shortcodes/SingleAgentShortcode.php    # New shortcode class
templates/display/agent-single-display.php  # New template file
```

### Data Flow
1. User clicks "View Details" in agents table
2. Redirect to page with `[wecoza_display_single_agent]` shortcode
3. Shortcode reads `agent_id` from URL parameter
4. Retrieves agent data from `get_hardcoded_agents()` (future: database)
5. Renders template with agent data

### URL Structure
```
/agents/view/?agent_id=123
```

### Integration Points
1. **Update agent-display-table.php**:
   - Change "View Details" from modal trigger to page redirect
   - Generate proper URL with agent_id parameter

2. **Maintain consistency with**:
   - Existing Phoenix design system
   - Bootstrap 5 components
   - Current permission system

## UI/UX Specifications

### Visual Design
- **Card-based layout**: Summary information in top cards
- **Table-based details**: Structured data in responsive tables
- **Color coding**: 
  - Success (green): Active status, completed items
  - Warning (yellow): Pending items
  - Danger (red): Missing or expired items
- **Icons**: Bootstrap Icons for visual enhancement
- **Typography**: Following Phoenix theme standards

### Responsive Behavior
- **Desktop**: Full 4-column card layout, 2-column detail sections
- **Tablet**: 2-column card layout, single column details
- **Mobile**: Single column throughout

### Loading States
- Display spinner while loading agent data
- Show appropriate error messages for:
  - Agent not found
  - Invalid agent ID
  - Permission denied

## Implementation Tasks

### Task 1: Create SingleAgentShortcode Class
**Priority**: High  
**Estimated Time**: 2 hours

**Subtasks**:
1. Create new file `src/Shortcodes/SingleAgentShortcode.php`
2. Extend AbstractShortcode class
3. Implement required methods:
   - `init()` - Set shortcode tag and defaults
   - `check_permissions()` - Verify viewing permissions
   - `enqueue_assets()` - Load required assets
   - `render_shortcode()` - Main rendering logic
4. Add agent retrieval logic:
   - `get_agent_by_id()` - Fetch single agent data
   - Error handling for invalid IDs
5. Implement URL parameter handling

### Task 2: Create Agent Single Display Template
**Priority**: High  
**Estimated Time**: 3 hours

**Subtasks**:
1. Create new file `templates/display/agent-single-display.php`
2. Implement header section with navigation
3. Create summary cards layout
4. Build detailed information sections:
   - Personal Information
   - Professional Information
   - Compliance & Documentation
   - Administrative Information
5. Add responsive design classes
6. Implement loading and error states

### Task 3: Update Agent Display Table
**Priority**: High  
**Estimated Time**: 1 hour

**Subtasks**:
1. Modify `templates/display/agent-display-table.php`
2. Update "View Details" action to use page redirect
3. Create `get_view_url()` method in DisplayAgentShortcode
4. Remove modal trigger attributes
5. Test navigation flow

### Task 4: Create Agent View Page
**Priority**: Medium  
**Estimated Time**: 30 minutes

**Subtasks**:
1. Create WordPress page for agent viewing
2. Add `[wecoza_display_single_agent]` shortcode
3. Set appropriate page template
4. Configure permalink structure
5. Test page accessibility

### Task 5: Styling and Polish
**Priority**: Medium  
**Estimated Time**: 1 hour

**Subtasks**:
1. Add custom CSS to `ydcoza-styles.css` if needed
2. Ensure consistent spacing and alignment
3. Add hover effects and transitions
4. Optimize for print view
5. Test across browsers

### Task 6: Testing and Documentation
**Priority**: Low  
**Estimated Time**: 1 hour

**Subtasks**:
1. Test all agent data fields display correctly
2. Verify permission checks work properly
3. Test responsive design on various devices
4. Check loading and error states
5. Update plugin documentation

## Success Criteria

1. **Functionality**
   - Single agent pages load correctly with agent_id parameter
   - All agent data fields display accurately
   - Navigation between list and detail views works smoothly
   - Permission checks prevent unauthorized access

2. **User Experience**
   - Page loads quickly with proper loading indicators
   - Information is well-organized and easy to scan
   - Responsive design works on all devices
   - Error messages are clear and helpful

3. **Technical Quality**
   - Code follows WordPress and plugin conventions
   - Proper escaping and sanitization
   - Efficient data retrieval
   - Clean URL structure

4. **Visual Consistency**
   - Matches Phoenix design system
   - Consistent with other plugin interfaces
   - Professional appearance
   - Accessible design

## Dependencies

- WordPress 6.0+
- PHP 7.4+
- Bootstrap 5 (already loaded)
- Phoenix theme design system
- Existing agent data structure
- Current permission system

## Migration Path

1. **Phase 1**: Implement new functionality alongside modal
2. **Phase 2**: Update table to use new page-based viewing
3. **Phase 3**: Remove modal code (future)
4. **Phase 4**: Integrate with database when available

## Risk Mitigation

1. **Data Consistency**: Ensure hardcoded data matches future database schema
2. **URL Conflicts**: Verify page slug doesn't conflict with existing pages
3. **Permission Gaps**: Thoroughly test permission checks
4. **Performance**: Optimize for large agent datasets (future consideration)

## Future Enhancements

1. **Print View**: Optimized layout for printing agent details
2. **Export Options**: PDF/CSV export functionality
3. **Activity Log**: Show agent's class history
4. **Document Viewer**: Inline viewing of uploaded documents
5. **Quick Actions**: Inline editing of certain fields
6. **Related Agents**: Show agents with similar qualifications
7. **QR Code**: Generate QR code for mobile access

## Estimated Timeline

- **Total Estimated Time**: 8-10 hours
- **Priority Order**: Tasks 1-3 (core functionality) � Tasks 4-5 (integration) � Task 6 (polish)
- **Deliverable**: Fully functional single agent display with page-based navigation

## Notes

- Current implementation uses hardcoded data but is designed to easily transition to database storage
- Template structure follows established patterns from legacy single class display
- All text should be translatable using WordPress i18n functions
- Security considerations include proper escaping, nonce verification, and capability checks

## Detailed Task Breakdown for Development

### 🔴 High Priority Tasks

#### Task 1.1: Create SingleAgentShortcode Base Structure
```
File: src/Shortcodes/SingleAgentShortcode.php
- Create class extending AbstractShortcode
- Set shortcode tag to 'wecoza_display_single_agent'
- Initialize default attributes
- Implement constructor
Time: 30 minutes
```

#### Task 1.2: Implement Core Shortcode Methods
```
File: src/Shortcodes/SingleAgentShortcode.php
- Implement check_permissions() method
- Implement enqueue_assets() method
- Add get_agent_by_id($agent_id) method
- Add error handling for invalid IDs
Time: 45 minutes
```

#### Task 1.3: Implement Render Logic
```
File: src/Shortcodes/SingleAgentShortcode.php
- Implement render_shortcode() method
- Handle agent_id from GET parameter
- Load template with agent data
- Pass required variables to template
Time: 45 minutes
```

#### Task 2.1: Create Template Header Structure
```
File: templates/display/agent-single-display.php
- Add PHP header and security check
- Create loading state HTML
- Create error state HTML
- Add back navigation button
- Add action buttons (Edit/Delete)
Time: 45 minutes
```

#### Task 2.2: Create Summary Cards Section
```
File: templates/display/agent-single-display.php
- Personal Info Card (Name, Gender, Race, Contact)
- Professional Status Card (ID, Status, SACE)
- Qualifications Card (Education, Quantum, Training)
- Agreement Status Card (Agreement, Banking)
Time: 1 hour
```

#### Task 2.3: Create Detailed Information Tables
```
File: templates/display/agent-single-display.php
- Personal Information section
- Professional Information section
- Compliance & Documentation section
- Administrative Information section
Time: 1.5 hours
```

#### Task 3.1: Update View Details Action
```
File: templates/display/agent-display-table.php
- Remove modal trigger attributes
- Change button to link
- Add proper URL generation
Time: 30 minutes
```

#### Task 3.2: Add URL Helper Method
```
File: src/Shortcodes/DisplayAgentShortcode.php
- Add get_view_url($agent_id) method
- Return URL to single agent page
- Use WordPress functions for URL generation
Time: 30 minutes
```

### 🟡 Medium Priority Tasks

#### Task 4.1: Create Agent View Page
```
WordPress Admin:
- Create new page titled "View Agent"
- Add [wecoza_display_single_agent] shortcode
- Set page slug to 'agent-view' or similar
- Publish page
Time: 15 minutes
```

#### Task 4.2: Configure Page Settings
```
WordPress Admin:
- Set appropriate page template
- Configure page visibility
- Test page access
- Update navigation if needed
Time: 15 minutes
```

#### Task 5.1: Add Custom Styles
```
File: /opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css
- Add single agent display styles
- Summary card styling
- Responsive breakpoints
- Print media queries
Time: 45 minutes
```

#### Task 5.2: Polish UI Elements
```
Various Files:
- Add hover effects
- Implement transitions
- Ensure consistent spacing
- Test responsive design
Time: 45 minutes
```

### 🟢 Low Priority Tasks

#### Task 6.1: Functionality Testing
```
Testing Checklist:
- Test with valid agent IDs
- Test with invalid agent IDs
- Test permission checks
- Test all data fields display
- Test navigation flow
Time: 30 minutes
```

#### Task 6.2: Cross-browser Testing
```
Testing Checklist:
- Chrome
- Firefox
- Safari
- Edge
- Mobile browsers
Time: 30 minutes
```

#### Task 6.3: Documentation
```
Files to Update:
- README.md (if exists)
- Inline code documentation
- CLAUDE.md file
- Usage examples
Time: 30 minutes
```

### Development Checklist

- [ ] SingleAgentShortcode class created and registered
- [ ] Template file created with all sections
- [ ] Agent ID parameter handling implemented
- [ ] Permission checks in place
- [ ] Loading and error states functional
- [ ] Summary cards displaying correctly
- [ ] Detailed sections showing all data
- [ ] Navigation from table to single view works
- [ ] Back button returns to agent list
- [ ] Edit/Delete buttons respect permissions
- [ ] Responsive design tested
- [ ] Custom styles applied
- [ ] Cross-browser compatibility verified
- [ ] Security measures implemented
- [ ] Code follows WordPress standards

### Code Quality Checklist

- [ ] Proper escaping with esc_html(), esc_attr(), esc_url()
- [ ] Nonce verification for actions
- [ ] Capability checks for permissions
- [ ] Translatable strings use __() or _e()
- [ ] No hardcoded URLs
- [ ] Consistent naming conventions
- [ ] Code is well-commented
- [ ] No console errors
- [ ] Performance optimized
- [ ] Follows DRY principles