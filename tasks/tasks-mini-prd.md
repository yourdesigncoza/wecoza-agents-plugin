# Tasks: WeCoza Agents Plugin - Table Styling Standardization

## Relevant Files

- `templates/display/agent-display-table.php` - Main table template that needs Phoenix styling updates
- `design-guide.md` - Reference document containing Phoenix design system patterns
- `mini-prd.md` - Product requirements document for this feature
- `/opt/lampp/htdocs/wecoza/wp-content/themes/wecoza_3_child_theme/includes/css/ydcoza-styles.css` - Custom CSS file for additional styling (if needed)
- `assets/css/agents-extracted.css` - Plugin-specific CSS file for agents styling

### Notes

- Phoenix theme CSS is already loaded via `ydcoza-theme.css`, so minimal custom CSS should be needed
- Bootstrap 5 is already available and should be leveraged for responsive design
- All existing table functionality (search, pagination, actions) must remain intact
- Focus on using existing Bootstrap 5 and Phoenix classes rather than custom CSS

## Tasks

- [x] 1.0 Update Table Structure and Classes
  - [x] 1.1 Locate main table element in `templates/display/agent-display-table.php` at line 110
  - [x] 1.2 Replace current table classes `table table-bordered ydcoza-compact-table table-hover borderless-table`
  - [x] 1.3 Apply Phoenix standard classes: `table table-hover table-sm fs-9 mb-0`
  - [x] 1.4 Verify table-responsive wrapper is maintained for mobile compatibility
  - [x] 1.5 Test table rendering after class changes to ensure no layout breaks

- [x] 2.0 Standardize Table Headers with Phoenix Design System
  - [x] 2.1 Add `border-bottom` class to `<thead>` element (line 111)
  - [x] 2.2 Add `border-end` class to table header `<th>` elements for column separation
  - [x] 2.3 Ensure header elements have `sort` class for sortable columns
  - [x] 2.4 Verify header styling matches Phoenix design guide patterns
  - [x] 2.5 Test header sorting functionality remains intact

- [x] 3.0 Implement Phoenix Action Button Styling
  - [x] 3.1 Locate action button section in template (lines 160-176)
  - [x] 3.2 Update View button: replace `btn bg-discovery-subtle` with `btn btn-sm btn-subtle-secondary`
  - [x] 3.3 Update Edit button: replace `btn bg-warning-subtle` with `btn btn-sm btn-subtle-secondary`
  - [x] 3.4 Update Delete button: replace `btn bg-danger-subtle` with `btn btn-sm btn-subtle-danger`
  - [x] 3.5 Ensure button group wrapper maintains `btn-group btn-group-sm` classes
  - [x] 3.6 Test all button functionality (view modal, edit navigation, delete action)

- [x] 4.0 Apply Consistent Typography and Text Colors
  - [x] 4.1 Add `text-body` class to table cell `<td>` elements for primary content
  - [x] 4.2 Update email links to use `text-primary` class for better visibility
  - [x] 4.3 Update phone number links to use `text-primary` class
  - [x] 4.4 Apply `text-muted` class to "No agents found" message
  - [x] 4.5 Verify text hierarchy follows Phoenix design system standards
  - [x] 4.6 Test text readability across light and dark themes

- [x] 5.0 Verify Responsive Design and Cross-Browser Compatibility
  - [x] 5.1 Test table display on mobile devices (320px - 768px)
  - [x] 5.2 Test table display on tablet devices (768px - 1024px)
  - [x] 5.3 Test table display on desktop devices (1024px+)
  - [x] 5.4 Verify button sizing and touch targets on mobile devices
  - [x] 5.5 Test table functionality across Chrome, Firefox, Safari, and Edge
  - [x] 5.6 Validate accessibility with screen readers and keyboard navigation
  - [x] 5.7 Confirm all existing features work: search, pagination, filters, actions