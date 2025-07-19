/**
 * Agents Table Search JavaScript for WeCoza Agents Plugin
 *
 * Implements real-time search functionality for the agents display table.
 * Searches across multiple agent fields (name, email, phone, city, etc.) with debouncing for performance.
 * Pagination is handled server-side through PHP.
 * 
 * @package WeCozaAgents
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Search configuration
     */
    const SEARCH_CONFIG = {
        debounceDelay: 300,        // Milliseconds to wait before executing search
        minSearchLength: 0,        // Minimum characters to trigger search (0 = search on empty)
        searchInputSelector: '.search-input.search.form-control-sm',
        tableSelector: '#agents-display-data',
        tableRowSelector: 'tbody tr',
        searchableColumns: [       // Column indices to search in (0-based)
            0, 1, 2, 3, 4, 5, 6, 7 // Adjust based on actual column count
        ]
    };

    /**
     * Search state
     */
    let searchTimeout = null;
    let $searchInput = null;
    let $table = null;
    let $tableRows = null;
    let totalRows = 0;
    let visibleRows = 0;

    /**
     * Search results state
     */
    let filteredRows = [];

    /**
     * Initialization state
     */
    let isInitialized = false;

    /**
     * Initialize the search functionality
     */
    function agents_init_table_search() {
        // Prevent duplicate initialization
        if (isInitialized) {
            console.log('WeCoza Agents: Already initialized, skipping duplicate initialization');
            return;
        }

        // Find search elements
        $searchInput = $(SEARCH_CONFIG.searchInputSelector);
        $table = $(SEARCH_CONFIG.tableSelector);
        $tableRows = $table.find(SEARCH_CONFIG.tableRowSelector);

        // Validate elements exist
        if ($searchInput.length === 0) {
            console.warn('WeCoza Agents: Search input not found');
            return;
        }

        if ($table.length === 0) {
            console.warn('WeCoza Agents: Table not found');
            return;
        }

        if ($tableRows.length === 0) {
            console.warn('WeCoza Agents: No table rows found');
            return;
        }

        // Initialize counters
        totalRows = $tableRows.length;
        visibleRows = totalRows;

        // Bind search event with debouncing
        $searchInput.on('input keyup paste', function() {
            const searchTerm = $(this).val();
            agents_debounced_search(searchTerm);
        });

        // Clear search on form reset
        $searchInput.closest('form').on('reset', function() {
            setTimeout(function() {
                agents_perform_search('');
            }, 10);
        });

        // Add search status indicator
        agents_add_search_status_indicator();

        // Initialize counters
        totalRows = $tableRows.length;
        visibleRows = totalRows;

        // Mark as initialized
        isInitialized = true;

        console.log('WeCoza Agents: Table search and pagination initialized successfully');
    }

    /**
     * Debounced search function to improve performance
     * 
     * @param {string} searchTerm - The search term to filter by
     */
    function agents_debounced_search(searchTerm) {
        // Clear existing timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Set new timeout
        searchTimeout = setTimeout(function() {
            agents_perform_search(searchTerm);
        }, SEARCH_CONFIG.debounceDelay);
    }

    /**
     * Perform the actual search filtering
     *
     * @param {string} searchTerm - The search term to filter by
     */
    function agents_perform_search(searchTerm) {
        // Normalize search term
        const normalizedSearchTerm = searchTerm.toLowerCase().trim();
        let matchedRows = 0;

        // Filter rows based on search term
        $tableRows.each(function() {
            const $row = $(this);
            const $cells = $row.find('td');

            if ($cells.length === 0) {
                return;
            }

            // Check if search term matches any searchable column
            let isMatch = true;
            if (normalizedSearchTerm.length >= SEARCH_CONFIG.minSearchLength) {
                isMatch = agents_search_matches($cells, normalizedSearchTerm);
            }

            if (isMatch) {
                $row.show();
                matchedRows++;
            } else {
                $row.hide();
            }
        });

        visibleRows = matchedRows;
        agents_update_search_status(searchTerm, visibleRows, totalRows);
    }

    /**
     * Check if search term matches any searchable column in the row
     * 
     * @param {jQuery} $cells - The table cells to search in
     * @param {string} searchTerm - The search term to look for
     * @returns {boolean} - True if match found
     */
    function agents_search_matches($cells, searchTerm) {
        // Search across all searchable columns
        for (let i = 0; i < SEARCH_CONFIG.searchableColumns.length; i++) {
            const columnIndex = SEARCH_CONFIG.searchableColumns[i];
            const $cell = $cells.eq(columnIndex);
            
            if ($cell.length === 0) {
                continue;
            }

            const cellText = $cell.text().toLowerCase().trim();
            
            // Direct substring match
            if (cellText.includes(searchTerm)) {
                return true;
            }

            // Split cell text by common separators to search individual parts
            const cellParts = cellText.split(/[\s:,.-]+/).filter(part => part.length > 0);
            
            // Check if any part starts with the search term
            if (cellParts.some(part => part.startsWith(searchTerm))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add search status indicator to the interface
     */
    function agents_add_search_status_indicator() {
        // Check if status indicator already exists
        if ($('#agents-search-status').length > 0) {
            return;
        }

        // Create status indicator
        const $statusIndicator = $('<span>', {
            id: 'agents-search-status',
            class: 'badge badge-phoenix badge-phoenix-primary mb-2',
            style: 'display: none;'
        });

        // Insert before the table
        $table.before($statusIndicator);
    }



    /**
     * Update search status indicator
     * 
     * @param {string} searchTerm - The current search term
     * @param {number} visible - Number of visible rows
     * @param {number} total - Total number of rows
     */
    function agents_update_search_status(searchTerm, visible, total) {
        const $statusIndicator = $('#agents-search-status');
        
        if ($statusIndicator.length === 0) {
            return;
        }

        // Show/hide status based on search activity
        if (searchTerm.trim().length === 0) {
            $statusIndicator.hide();
            return;
        }

        // Update status text
        let statusText = '';
        if (visible === 0) {
            statusText = `No agents found matching "${searchTerm}"`;
        } else if (visible === total) {
            statusText = `Showing all ${total} agents`;
        } else {
            statusText = `Showing ${visible} of ${total} agents matching "${searchTerm}"`;
        }

        $statusIndicator.text(statusText).show();
    }

    /**
     * Reset search functionality
     */
    function agents_reset_search() {
        if ($searchInput) {
            $searchInput.val('');
            agents_perform_search('');
        }
    }

    /**
     * Force re-initialization (useful for debugging)
     */
    function agents_force_reinit() {
        isInitialized = false;
        agents_init_table_search();
    }


    /**
     * Get current search statistics
     *
     * @returns {object} - Object containing search statistics
     */
    function agents_get_search_stats() {
        return {
            totalRows: totalRows,
            visibleRows: visibleRows,
            searchTerm: $searchInput ? $searchInput.val() : '',
            isSearchActive: $searchInput ? $searchInput.val().trim().length > 0 : false
        };
    }

    /**
     * Public API for external access
     */
    window.WeCozaAgentsSearch = {
        init: agents_init_table_search,
        reset: agents_reset_search,
        getStats: agents_get_search_stats,
        forceReinit: agents_force_reinit
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Small delay to ensure all elements are rendered
        setTimeout(function() {
            agents_init_table_search();
        }, 100);
    });

})(jQuery);