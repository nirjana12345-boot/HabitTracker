/**
 * Tracking History Page JavaScript
 * Handles filters, pagination, and exports
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. FILTER FUNCTIONALITY
    // ============================================
    const applyBtn = document.getElementById('applyFilters');
    const clearBtn = document.getElementById('clearFilters');
    const habitFilter = document.getElementById('habitFilter');
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');

    function applyFilters() {
        const params = new URLSearchParams();
        
        if (habitFilter && habitFilter.value != 0) {
            params.append('habit', habitFilter.value);
        }
        if (statusFilter && statusFilter.value) {
            params.append('status', statusFilter.value);
        }
        if (dateFrom && dateFrom.value) {
            params.append('date_from', dateFrom.value);
        }
        if (dateTo && dateTo.value) {
            params.append('date_to', dateTo.value);
        }
        
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', applyFilters);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });
    }

    // ============================================
    // 2. ENTER KEY FOR FILTERS
    // ============================================
    const filterInputs = [habitFilter, statusFilter, dateFrom, dateTo];
    filterInputs.forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    if (applyBtn) {
                        applyBtn.click();
                    }
                }
            });
        }
    });

    // ============================================
    // 3. PAGINATION
    // ============================================
    function changePage(page) {
        if (page < 1) return;
        
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    // Expose pagination function globally
    window.changePage = changePage;

    // ============================================
    // 4. EXPORT FUNCTIONALITY (Optional)
    // ============================================
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'csv');
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    }

    // ============================================
    // 5. TABLE ROW HOVER EFFECTS
    // ============================================
    document.querySelectorAll('.tracking-table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'var(--secondary)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // ============================================
    // 6. STATUS FILTER QUICK LINKS
    // ============================================
    document.querySelectorAll('.stat-item').forEach((item, index) => {
        item.addEventListener('click', function() {
            const label = this.querySelector('.label')?.textContent;
            if (label === 'Completed') {
                const params = new URLSearchParams(window.location.search);
                params.set('status', 'Completed');
                window.location.href = window.location.pathname + '?' + params.toString();
            } else if (label === 'Missed') {
                const params = new URLSearchParams(window.location.search);
                params.set('status', 'Missed');
                window.location.href = window.location.pathname + '?' + params.toString();
            }
        });
        item.style.cursor = 'pointer';
    });
});