# WeCoza Agents Plugin - Task List & Mini PRD

## Overview
This document outlines the tasks required to fix critical issues identified in the code review and implement improvements to the agent form submission workflow.

**Last Updated**: 2025-07-18  
**Priority**: CRITICAL - Form submission is currently broken in production

## Task Categories

### 🚨 CRITICAL: Production Blockers (Fix Immediately)

#### Task 1: Fix Form Validation Field Name Mismatches
**Priority**: CRITICAL  
**Effort**: 15 minutes  
**Blocks**: All form submissions  

**Subtasks**:
- [x] 1.1 Fix validation in `CaptureAgentShortcode.php` lines 276-290 ✅
  - Change `$this->form_data['last_name']` to `$this->form_data['surname']`
  - Change `$this->form_data['phone']` to `$this->form_data['tel_number']`
  - Change `$this->form_data['email']` to `$this->form_data['email_address']`
- [ ] 1.2 Test form submission with all required fields
- [ ] 1.3 Test form submission with missing required fields to verify error messages

#### Task 2: Implement Input Sanitization
**Priority**: CRITICAL  
**Effort**: 30 minutes  
**Risk**: SQL injection, XSS attacks  

**Subtasks**:
- [x] 2.1 Add sanitization to `collect_form_data()` method in `CaptureAgentShortcode.php` ✅
  - Text fields: Use `sanitize_text_field()`
  - Email: Use `sanitize_email()`
  - Phone: Use regex to allow only numbers and formatting characters
  - Textarea fields: Use `sanitize_textarea_field()`
- [ ] 2.2 Verify `sanitize_agent_data()` in `AgentQueries.php` is being called
- [ ] 2.3 Test with malicious input (SQL injection attempts, XSS payloads)

#### Task 3: Fix Primary Key References
**Priority**: CRITICAL  
**Effort**: 30 minutes  
**Impact**: Database operations failing  

**Subtasks**:
- [x] 3.1 Update `save_agent()` method - already completed ✅
- [ ] 3.2 Update all queries in `AgentQueries.php` to use `agent_id` instead of `id`
- [x] 3.3 Update `display_form()` to check for `agent['agent_id']` instead of `agent['id']` ✅
- [x] 3.4 Search codebase for any remaining `$agent['id']` references ✅

### 🔒 HIGH PRIORITY: Security & Performance (Within 1 Week)

#### Task 4: Re-enable SA ID Validation
**Priority**: HIGH  
**Effort**: 2 hours  
**Impact**: Data integrity, security  

**Subtasks**:
- [ ] 4.1 Create async validation endpoint for SA ID
- [ ] 4.2 Implement client-side async validation in `agent-form-validation.js`
- [ ] 4.3 Re-enable server-side validation with proper error handling
- [ ] 4.4 Add rate limiting to prevent validation abuse
- [ ] 4.5 Log suspicious validation attempts

#### Task 5: Database Performance Optimization
**Priority**: HIGH  
**Effort**: 1 hour  
**Impact**: 50x query speed improvement  

**Subtasks**:
- [x] 5.1 Create SQL migration file for indexes ✅
  ```sql
  CREATE INDEX idx_agents_search ON agents(surname, first_name, email_address);
  CREATE INDEX idx_agents_status_created ON agents(status, created_at DESC);
  CREATE INDEX idx_agents_city_province ON agents(city, province);
  CREATE INDEX idx_agents_email_unique ON agents(email_address);
  CREATE INDEX idx_agents_sa_id_unique ON agents(sa_id_no) WHERE sa_id_no IS NOT NULL;
  ```
- [x] 5.2 Add `LIMIT 1` to existence check queries ✅ (Already implemented)
- [ ] 5.3 Implement query result caching using WordPress transients
- [ ] 5.4 Test query performance with 1000+ agent records

#### Task 6: Fix Database Connection Management
**Priority**: HIGH  
**Effort**: 2-4 hours  
**Impact**: Prevents connection exhaustion  

**Subtasks**:
- [ ] 6.1 Research PgBouncer setup for WordPress
- [ ] 6.2 Implement connection pooling in `DatabaseService.php`
- [ ] 6.3 Add connection timeout and retry logic
- [ ] 6.4 Monitor connection usage under load
- [ ] 6.5 Document connection pooling configuration

### 🛠️ MEDIUM PRIORITY: Code Quality & Maintainability (Within 2 Weeks)

#### Task 7: Consolidate Field Mapping Logic
**Priority**: MEDIUM  
**Effort**: 4 hours  
**ROI**: Saves 4+ hours/month in debugging  

**Subtasks**:
- [ ] 7.1 Create `FieldMapper` service class
- [ ] 7.2 Define single source of truth for field mappings
- [ ] 7.3 Update `CaptureAgentShortcode` to use FieldMapper
- [ ] 7.4 Update `FormHelpers` to use FieldMapper
- [ ] 7.5 Update `Agent` model to use FieldMapper
- [ ] 7.6 Remove duplicate field mapping arrays
- [ ] 7.7 Add unit tests for field mapping

#### Task 8: Implement Transaction Handling
**Priority**: MEDIUM  
**Effort**: 2 hours  
**Impact**: Prevents partial data saves  

**Subtasks**:
- [ ] 8.1 Wrap agent creation + file uploads in database transaction
- [ ] 8.2 Add rollback on file upload failure
- [ ] 8.3 Add rollback on validation failure
- [ ] 8.4 Test transaction rollback scenarios
- [ ] 8.5 Add error logging for failed transactions

#### Task 9: Extract File Upload Service
**Priority**: MEDIUM  
**Effort**: 3 hours  
**Impact**: Better separation of concerns  

**Subtasks**:
- [ ] 9.1 Create `FileUploadService` class
- [ ] 9.2 Move file upload logic from `CaptureAgentShortcode`
- [ ] 9.3 Add file size validation (before upload)
- [ ] 9.4 Add file type validation whitelist
- [ ] 9.5 Implement virus scanning hook
- [ ] 9.6 Add cleanup for orphaned files

### 📈 LOW PRIORITY: Future Enhancements

#### Task 10: Implement Full-Text Search
**Priority**: LOW  
**Effort**: 4 hours  
**Impact**: Sub-100ms search on large datasets  

**Subtasks**:
- [ ] 10.1 Add PostgreSQL full-text search column
- [ ] 10.2 Create trigger to update search vector
- [ ] 10.3 Update search queries to use full-text
- [ ] 10.4 Add search relevance ranking

#### Task 11: Add Comprehensive Logging
**Priority**: LOW  
**Effort**: 2 hours  
**Impact**: Better debugging and monitoring  

**Subtasks**:
- [ ] 11.1 Add PSR-3 compatible logger
- [ ] 11.2 Log all database operations
- [ ] 11.3 Log validation failures with context
- [ ] 11.4 Add performance timing logs
- [ ] 11.5 Create log rotation policy

## Implementation Order

### Phase 1: Emergency Fixes (Today)
1. Task 1: Fix Form Validation Field Names ✅ (Partially Complete - needs testing)
2. Task 2: Implement Input Sanitization ✅ (Partially Complete - needs verification)
3. Task 3: Fix Primary Key References ✅ (Mostly Complete - AgentQueries.php still needs updating)

### Phase 2: Security & Performance (This Week)
4. Task 4: Re-enable SA ID Validation
5. Task 5: Database Performance Optimization
6. Task 6: Fix Database Connection Management

### Phase 3: Technical Debt (Next 2 Weeks)
7. Task 7: Consolidate Field Mapping Logic
8. Task 8: Implement Transaction Handling
9. Task 9: Extract File Upload Service

### Phase 4: Enhancements (Future)
10. Task 10: Implement Full-Text Search
11. Task 11: Add Comprehensive Logging

## Success Metrics

- **Immediate**: Form submissions working without errors
- **Week 1**: Zero security vulnerabilities, <500ms page loads
- **Week 2**: Clean code architecture, no field mapping bugs
- **Month 1**: Support for 10,000+ agents with sub-second performance

## Notes

- All database changes should be tested on staging first
- Create backups before running migrations
- Monitor error logs after each deployment
- Consider feature flags for gradual rollout

## Testing Checklist

### After Each Task:
- [ ] Test form submission with valid data
- [ ] Test form submission with invalid data
- [ ] Test form submission with malicious data
- [ ] Check error logs for warnings/errors
- [ ] Verify database integrity
- [ ] Test with 100+ concurrent users (for performance tasks)

## Resources

- [WordPress Sanitization Functions](https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/)
- [PostgreSQL Performance Tuning](https://www.postgresql.org/docs/current/performance-tips.html)
- [PgBouncer Documentation](https://www.pgbouncer.org/)
- [WordPress Transients API](https://developer.wordpress.org/apis/transients/)

## Status Summary

### ✅ Completed:
1. Fixed form validation field name mismatches (surname, tel_number, email_address)
2. Added comprehensive input sanitization to all form fields
3. Fixed primary key references throughout the codebase (agent_id vs id)
4. Added validation for all required fields
5. Updated AgentQueries.php to use agent_id consistently
6. Verified sanitize_agent_data() is being called in create/update operations
7. Re-enabled SA ID validation with proper checksum algorithm
8. Created database index migration file (needs to be run)
9. Added real-time client-side SA ID validation with feedback

### 🔄 Still Needed:
1. **Testing**: Form submission needs to be tested with valid/invalid data
2. **Database Migration**: Run the index creation script (`migrations/add-performance-indexes.sql`)
3. **Task 5.3**: Implement query result caching using WordPress transients
4. **Task 6**: Fix Database Connection Management (connection pooling)