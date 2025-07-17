# Tasks: Replace Select2 with Bootstrap 5 Native Dropdowns

Based on the PRD document `mini-prd.md`, this task list guides the implementation of replacing Select2 library with native Bootstrap 5 dropdown functionality.

## Relevant Files

- `includes/class-plugin.php` - Main plugin class that enqueues Select2 CSS/JS (lines 604-610, 624-631, 637)
- `src/Shortcodes/CaptureAgentShortcode.php` - Controller that enqueues Select2 for capture form (lines 82-96, 102, 108)
- `includes/class-backwards-compatibility.php` - Backwards compatibility handler with Select2 dependency (line 204)
- `assets/js/agent-form-validation.js` - Form validation script with Select2 initialization (lines 24-31)
- `assets/js/agents-app.js` - Main application script with Select2 initialization (lines 219-237)
- `templates/forms/agent-capture-form.php` - Form template (already Bootstrap 5 compatible, no changes needed)
- `CLAUDE.md` - Documentation file that references Select2 usage

### Notes

- Templates already use Bootstrap 5 classes (`form-select form-select-sm`) - no template changes required
- Focus on removing code rather than adding - avoid code bloat
- Test functionality thoroughly after each removal to ensure no regressions
- MVC principle: Controllers handle script enqueuing, Views (templates) remain unchanged

## Tasks

- [ ] 1.0 Remove Select2 Dependencies from PHP Files
  - [x] 1.1 Remove Select2 CSS enqueue from `includes/class-plugin.php` (lines 604-610)
  - [x] 1.2 Remove Select2 JS enqueue from `includes/class-plugin.php` (lines 624-631)
  - [x] 1.3 Remove 'select2' dependency from main script in `includes/class-plugin.php` (line 637)
  - [x] 1.4 Remove Select2 CSS enqueue from `src/Shortcodes/CaptureAgentShortcode.php` (lines 82-88)
  - [x] 1.5 Remove Select2 JS enqueue from `src/Shortcodes/CaptureAgentShortcode.php` (lines 90-96)
  - [x] 1.6 Remove 'select2' dependency from form validation script in `src/Shortcodes/CaptureAgentShortcode.php` (lines 102, 108)
  - [x] 1.7 Remove 'select2' dependency from `includes/class-backwards-compatibility.php` (line 204)

- [ ] 2.0 Update JavaScript Files to Remove Select2 Initialization
  - [ ] 2.1 Remove Select2 initialization code from `assets/js/agent-form-validation.js` (lines 24-31)
  - [ ] 2.2 Remove conditional Select2 check `if ($.fn.select2)` from `assets/js/agent-form-validation.js`
  - [ ] 2.3 Remove Select2 initialization for individual dropdowns from `assets/js/agents-app.js` (lines 219-237)
  - [ ] 2.4 Remove Select2 configuration objects from `assets/js/agents-app.js`
  - [ ] 2.5 Verify no other JavaScript files reference Select2 functionality

- [ ] 3.0 Verify Template Compatibility with Bootstrap 5 Native Selects
  - [ ] 3.1 Confirm `templates/forms/agent-capture-form.php` uses `form-select form-select-sm` classes
  - [ ] 3.2 Verify working area dropdowns (lines 241-268) have proper Bootstrap 5 markup
  - [ ] 3.3 Ensure placeholder options are properly formatted (`<option value="">Select</option>`)
  - [ ] 3.4 Confirm no template changes are required (avoid unnecessary modifications)

- [ ] 4.0 Test and Validate Functionality
  - [ ] 4.1 Test dropdown selection functionality in preferred working areas
  - [ ] 4.2 Verify form submission processes correctly with native selects
  - [ ] 4.3 Test form validation behavior with Bootstrap 5 native selects
  - [ ] 4.4 Ensure no JavaScript console errors occur
  - [ ] 4.5 Test responsive design functionality on mobile devices
  - [ ] 4.6 Verify accessibility features work with native selects
  - [ ] 4.7 Test across major browsers (Chrome, Firefox, Safari, Edge)

- [ ] 5.0 Performance Testing and Documentation Updates
  - [ ] 5.1 Measure page load time improvement after Select2 removal
  - [ ] 5.2 Verify reduction in HTTP requests (should be 2 fewer)
  - [ ] 5.3 Confirm no JavaScript errors in browser console
  - [ ] 5.4 Update `CLAUDE.md` to remove Select2 references (line 75)
  - [ ] 5.5 Update any other documentation mentioning Select2 usage
  - [ ] 5.6 Verify plugin activation/deactivation works correctly