# WeCoza Agents Plugin - Table Styling Standardization PRD

## Executive Summary
Standardize the agents table styling in `#agents-container` to match the Phoenix design system and Bootstrap 5 patterns documented in `design-guide.md`, ensuring consistent visual appearance throughout the application.

## Current State Analysis

### Table Location
- **File**: `/templates/display/agent-display-table.php:110`
- **Container**: `#agents-container`
- **Current Classes**: `table table-bordered ydcoza-compact-table table-hover borderless-table`

### Current Issues Identified
1. **Mixed Class Naming**: Inconsistent combination of Bootstrap and custom classes
2. **Non-Standard Button Classes**: Using `bg-discovery-subtle`, `bg-warning-subtle`, `bg-danger-subtle`
3. **Inconsistent Typography**: Missing text hierarchy and color consistency
4. **Missing Phoenix Badge System**: No status indicators using Phoenix design patterns
5. **Custom CSS Overrides**: Conflicting styles in `ydcoza-styles.css`

### Current Action Button Implementation (Lines 160-176)
```php
// Current non-standard button classes
<button class="btn bg-discovery-subtle view-agent-details">View</button>
<a href="#" class="btn bg-warning-subtle">Edit</a>
<button class="btn btn-sm bg-danger-subtle delete-agent-btn">Delete</button>
```

## Target Phoenix Design System Requirements

### Table Structure (Per design-guide.md)
```html
<div class="table-responsive">
    <table class="table table-hover table-sm fs-9 mb-0">
        <thead class="border-bottom">
            <tr>
                <th class="sort border-end" data-sort="field_name">Header</th>
                <th class="sort" data-sort="field_name">Header</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-end">Content</td>
                <td>Content</td>
                <td>
                    <button class="btn btn-sm btn-subtle-secondary">Action</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Typography Requirements
- **Primary Text**: `text-body` for main content
- **Secondary Text**: `text-muted` for less important information
- **Contextual Colors**: `text-primary`, `text-success`, `text-danger`, `text-warning`

### Action Button Standards
- **Standard Actions**: `btn btn-sm btn-subtle-secondary`
- **Primary Actions**: `btn btn-sm btn-subtle-primary`
- **Danger Actions**: `btn btn-sm btn-subtle-danger`
- **Button Groups**: `btn-group btn-group-sm`

### Status Indicators (Phoenix Badge System)
- **Base Badge**: `badge badge-phoenix fs-10`
- **Primary**: `badge-phoenix-primary`
- **Success**: `badge-phoenix-success`
- **Warning**: `badge-phoenix-warning`
- **Danger**: `badge-phoenix-danger`
- **Secondary**: `badge-phoenix-secondary`

## Implementation Specifications

### Phase 1: Table Structure Update
**Target**: `agent-display-table.php:110`
- **Replace**: `table table-bordered ydcoza-compact-table table-hover borderless-table`
- **With**: `table table-hover table-sm fs-9 mb-0`

### Phase 2: Header Enhancement
**Target**: `agent-display-table.php:114-137`
- Add `border-bottom` class to `<thead>`
- Add `border-end` class to appropriate `<th>` elements
- Ensure consistent header styling with Phoenix patterns

### Phase 3: Action Button Standardization
**Target**: `agent-display-table.php:160-176`
- **View Button**: `btn bg-discovery-subtle` � `btn btn-sbtn-subtleix-secondary`
- **Edit Button**: `btn bg-warning-subtle` � `btn btn-sbtn-subtleix-secondary`
- **Delete Button**: `btn bg-danger-subtle` � `btn btn-sbtn-subtleix-danger`
- **Button Group**: Ensure `btn-group btn-group-sm` wrapper

### Phase 4: Typography Application
**Target**: Table cells throughout template
- Apply `text-body` for primary content (names, emails, phone numbers)
- Apply `text-muted` for secondary information
- Use contextual colors where appropriate

### Phase 5: Responsive Design
**Target**: Mobile and tablet breakpoints
- Ensure table responsiveness with existing `table-responsive` wrapper
- Optimize button sizes for touch devices
- Maintain readability across screen sizes

### Phase 6: Custom CSS Enhancements
**Target**: `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`
- Add supporting CSS for table spacing optimization
- Enhance mobile responsive design
- Add dark mode support improvements
- Custom spacing utilities for agents table

## Files to Modify

1. **`mini-prd.md`** - This PRD document
2. **`templates/display/agent-display-table.php`** - Main table template
3. **`/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css`** - Custom CSS additions

## Expected Outcomes

### Visual Consistency
- Consistent Phoenix theme appearance matching design-guide.md patterns
- Standardized action button styling throughout the application
- Enhanced typography hierarchy with proper text color usage

### User Experience
- Improved responsive design across all devices
- Better accessibility with proper contrast ratios
- Consistent interaction patterns with other tables in the system

### Technical Benefits
- Cleaner, more maintainable CSS code
- Better adherence to design system standards
- Reduced custom CSS overrides

## Success Metrics

1. **Visual Consistency**: Table appearance matches other Phoenix tables in the application
2. **Responsive Design**: Table functions properly on mobile, tablet, and desktop
3. **Accessibility**: Proper color contrast ratios and screen reader compatibility
4. **Functionality**: All existing table features (search, pagination, actions) continue to work
5. **Performance**: No degradation in table rendering performance

## Risk Mitigation

1. **Functionality Testing**: Thoroughly test all table interactions after styling changes
2. **Cross-Browser Compatibility**: Verify appearance across different browsers
3. **Mobile Testing**: Ensure responsive design works on various screen sizes
4. **Accessibility Validation**: Test with screen readers and keyboard navigation

## Timeline

- **Phase 1-2**: Table structure and headers (30 minutes)
- **Phase 3**: Action button standardization (20 minutes)
- **Phase 4**: Typography application (15 minutes)
- **Phase 5**: Responsive design verification (15 minutes)
- **Phase 6**: Custom CSS additions (20 minutes)
- **Total Estimated Time**: 1.5-2 hours

## Dependencies

- Bootstrap 5 framework (already available)
- Phoenix theme design system (documented in design-guide.md)
- Existing table functionality must remain intact
- Custom CSS must be added to theme's ydcoza-styles.css file per global instructions