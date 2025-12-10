# Agent Update Functionality Test Guide

## Overview
This document provides testing instructions for the enhanced agent update functionality that supports URL parameter detection for editing existing agents.

## URL Formats Supported

### 1. New Format (Primary)
```
?update&agent_id=30
?update=1&agent_id=30
```

### 2. Backward Compatible Format
```
?agent_id=30
```

### 3. Shortcode Attribute Format
```
[wecoza_capture_agents agent_id="30"]
[wecoza_capture_agents mode="edit" agent_id="30"]
```

## Testing Steps

### Test 1: URL Parameter Detection
1. Navigate to a page with the `[wecoza_capture_agents]` shortcode
2. Add URL parameters:
   - `yoursite.com/agents-form/?update&agent_id=1`
   - Verify form loads in edit mode
   - Verify agent data is pre-populated
   - Verify edit indicator appears at top of form

### Test 2: Backward Compatibility
1. Use the old URL format:
   - `yoursite.com/agents-form/?agent_id=1`
   - Verify it still works as expected

### Test 3: Error Handling
1. Test invalid agent ID:
   - `yoursite.com/agents-form/?update&agent_id=999999`
   - Verify error message displays
2. Test missing agent ID:
   - `yoursite.com/agents-form/?update`
   - Verify appropriate warning in logs

### Test 4: Form Submission
1. Load existing agent for editing
2. Modify some fields
3. Submit form
4. Verify:
   - Success message shows "Agent [Name] (ID: X) has been updated successfully"
   - Data is actually updated in database
   - Form remains in edit mode with updated data

### Test 5: Permissions
1. Test with user who cannot manage agents
2. Verify access is denied appropriately

### Test 6: File Uploads (if applicable)
1. Load existing agent
2. Upload new files
3. Verify files are updated correctly

## Expected Results

### Edit Mode Indicators
- Alert box at top showing "Editing Agent: [Name] (ID: X)"
- Submit button shows "Update Agent" instead of "Add New Agent"
- Hidden field contains editing_agent_id
- Form data is pre-populated with existing agent data

### Success Messages
- **Create**: "New agent has been created successfully with ID: X"
- **Update**: "Agent [Name] (ID: X) has been updated successfully"

### Error Messages
- **Invalid ID**: "Invalid agent ID provided"
- **Not Found**: "Agent with ID X not found. Please check the agent ID and try again"
- **No Permission**: "You do not have permission to edit this agent"
- **Save Failed**: "Failed to update agent. Please check your input and try again"

## Logging
All operations are logged with prefixes:
- `[WeCoza Agents] Successfully loaded agent X for editing`
- `[WeCoza Agents] Successfully updated agent X`
- `[WeCoza Agents] Update mode requested but no valid agent_id provided`

## Troubleshooting

### Common Issues
1. **Form doesn't switch to edit mode**: Check agent ID is valid and exists
2. **Permission denied**: Verify user has agent management capabilities
3. **Data not pre-populated**: Check FormHelpers::get_field_value() calls in template
4. **Update doesn't save**: Check database connection and AgentQueries update method

### Debug Steps
1. Check browser developer tools for JavaScript errors
2. Check WordPress debug log for PHP errors and WeCoza Agents log entries
3. Verify database tables exist and are accessible
4. Test with WordPress admin user to rule out permission issues

## Code Files Modified
- `src/Shortcodes/CaptureAgentShortcode.php` - Enhanced URL detection and error handling
- `templates/forms/agent-capture-form.php` - Added edit mode indicators

## Security Considerations
- All agent IDs are validated and sanitized using `absint()`
- Permission checks are performed before loading agent data
- Nonce verification is maintained for form submissions
- Error messages don't expose sensitive system information