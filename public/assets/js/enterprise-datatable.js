/**
 * EnterpriseDataTable.js
 * High-performance, high-density, enterprise-grade data table engine.
 * Supports:
 * - Server-side pagination & client-side pagination fallback
 * - Live search & custom dropdown filters
 * - Dynamic column sorting
 * - Density modes (compact, comfortable, spacious)
 * - Page size selection (10, 20, 50, 100)
 * - Row actions & icon button rendering
 * - Sticky headers & responsive priority column hiding
 * - Persistent table states in SessionStorage
 */

(function($) {
    // Inject responsive breakpoint styles if not already present
    if (!$('#enterprise-datatable-responsive-styles').length) {
        $('head').append(`
            <style id="enterprise-datatable-responsive-styles">
                @media (max-width: 1024px) {
                    .edt-priority-low { display: none !important; }
                }
                @media (max-width: 768px) {
                    .edt-priority-medium { display: none !important; }
                }
            </style>
        `);
    }

    class EnterpriseDataTable {
        constructor(containerSelector, options) {
            this.container = $(containerSelector);
            if (!this.container.length) return;

            this.options = $.extend({
                url: null, // End point for fetching data (required for serverSide)
                method: 'GET',
                data: {}, // Additional request parameters
                columns: [], // Column configs: { key, title, align, render, sortable, priority: 'high'|'medium'|'low' }
                density: 'compact', // default
                pageSize: 20,
                pageSizeOptions: [10, 20, 50, 100],
                searchable: true,
                searchPlaceholder: 'Search...',
                filters: [], // Array of { name, label, options: [{value, label}], value: defaultVal, change: function }
                serverSide: true, // server-side pagination, search, sort
                clientSideData: null, // For serverSide: false, array of local records
                onLoad: null, // callback when data is loaded
                emptyMessage: 'No records found.',
                dataKey: 'items', // Key in the JSON response containing array of rows
                totalKey: 'total', // Key in JSON response for total count
                currentPageKey: 'current_page', // Key in JSON response for current page
                lastPageKey: 'last_page', // Key in JSON response for last page
                onAjaxError: null
            }, options);

            this.currentPage = 1;
            this.searchQuery = '';
            this.activeFilters = {};
            this.sortKey = null;
            this.sortOrder = 'desc'; // or 'asc'
            this.isLoading = false;
            this.rawData = []; // holds raw fetched list for client-side ops
            this.filteredData = []; // holds filtered/sorted list for client-side ops
            this.totalRecords = 0;

            // Load saved state from session storage for persistence
            this.storageKey = 'edt_' + containerSelector.replace(/[^a-zA-Z0-9]/g, '_');
            this.loadState();

            // Set default active filter values
            this.options.filters.forEach(f => {
                if (this.activeFilters[f.name] === undefined && f.value !== undefined) {
                    this.activeFilters[f.name] = f.value;
                }
            });

            this.initLayout();
            this.bindEvents();
            this.load();
        }

        loadState() {
            try {
                const state = JSON.parse(sessionStorage.getItem(this.storageKey));
                if (state) {
                    this.currentPage = state.currentPage || 1;
                    this.options.density = state.density || this.options.density;
                    this.options.pageSize = state.pageSize || this.options.pageSize;
                    this.searchQuery = state.searchQuery || '';
                    this.activeFilters = state.activeFilters || {};
                    this.sortKey = state.sortKey || null;
                    this.sortOrder = state.sortOrder || 'desc';
                }
            } catch(e) {}
        }

        saveState() {
            try {
                const state = {
                    currentPage: this.currentPage,
                    density: this.options.density,
                    pageSize: this.options.pageSize,
                    searchQuery: this.searchQuery,
                    activeFilters: this.activeFilters,
                    sortKey: this.sortKey,
                    sortOrder: this.sortOrder
                };
                sessionStorage.setItem(this.storageKey, JSON.stringify(state));
            } catch(e) {}
        }

        initLayout() {
            this.container.addClass('enterprise-table-wrapper');
            
            // 1. Build Toolbar
            let toolbarHtml = '';
            if (this.options.searchable || this.options.filters.length > 0 || this.options.pageSizeOptions) {
                toolbarHtml += `<div class="enterprise-toolbar">`;
                
                // Left Toolbar Actions (Search & Filters)
                toolbarHtml += `<div class="enterprise-toolbar-left">`;
                if (this.options.searchable) {
                    toolbarHtml += `
                        <div class="enterprise-search-wrapper">
                            <i class="fas fa-search enterprise-search-icon"></i>
                            <input type="text" class="enterprise-search-input" placeholder="${this.options.searchPlaceholder}" value="${this.escape(this.searchQuery)}">
                        </div>
                    `;
                }

                // Add filter selectors
                this.options.filters.forEach(f => {
                    toolbarHtml += `
                        <select class="enterprise-select edt-filter" data-name="${f.name}">
                            <option value="all">All ${f.label}</option>
                    `;
                    f.options.forEach(opt => {
                        const selected = this.activeFilters[f.name] == opt.value ? 'selected' : '';
                        toolbarHtml += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                    });
                    toolbarHtml += `</select>`;
                });
                toolbarHtml += `</div>`; // end left toolbar

                // Right Toolbar Actions (Density & Page Size)
                toolbarHtml += `<div class="enterprise-toolbar-right">`;
                
                // Density selector
                toolbarHtml += `
                    <select class="enterprise-select edt-density-select" title="Display Density">
                        <option value="compact" ${this.options.density === 'compact' ? 'selected' : ''}>Compact Mode</option>
                        <option value="comfortable" ${this.options.density === 'comfortable' ? 'selected' : ''}>Comfortable</option>
                        <option value="spacious" ${this.options.density === 'spacious' ? 'selected' : ''}>Spacious</option>
                    </select>
                `;

                // Page size selector
                if (this.options.pageSizeOptions && this.options.pageSizeOptions.length > 0) {
                    toolbarHtml += `
                        <select class="enterprise-select edt-pagesize-select" title="Rows Per Page">
                    `;
                    this.options.pageSizeOptions.forEach(opt => {
                        const selected = this.options.pageSize == opt ? 'selected' : '';
                        toolbarHtml += `<option value="${opt}" ${selected}>${opt} per page</option>`;
                    });
                    toolbarHtml += `</select>`;
                }

                toolbarHtml += `</div>`; // end right toolbar
                toolbarHtml += `</div>`; // end toolbar
            }

            // 2. Build Table structure
            const hasToolbarClass = toolbarHtml ? 'has-toolbar' : '';
            let tableHtml = `
                <div class="enterprise-table-container ${hasToolbarClass}">
                    <div class="enterprise-loading-overlay" style="display:none;">
                        <div class="loading-spinner"></div>
                    </div>
                    <table class="enterprise-table density-${this.options.density}">
                        <thead>
                            <tr>
            `;

            // Table Headers
            this.options.columns.forEach(col => {
                const alignClass = col.align ? 'text-' + col.align : 'text-left';
                const priorityClass = col.priority ? 'edt-priority-' + col.priority : '';
                const sortableClass = col.sortable !== false ? 'sortable' : '';
                const sortedClass = this.sortKey === col.key ? 'sorted-' + this.sortOrder : '';
                const sortIcon = col.sortable !== false ? '<i class="fas fa-sort enterprise-sort-icon"></i>' : '';
                
                tableHtml += `
                    <th class="${alignClass} ${priorityClass} ${sortableClass} ${sortedClass}" data-key="${col.key}">
                        ${col.title} ${sortIcon}
                    </th>
                `;
            });

            tableHtml += `
                            </tr>
                        </thead>
                        <tbody class="edt-tbody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            `;

            // 3. Build Pagination Bar
            let paginationHtml = `<div class="table-pagination-wrapper" style="display:none;"></div>`;

            // Output structured layout to container
            this.container.html(toolbarHtml + tableHtml + paginationHtml);
        }

        bindEvents() {
            const self = this;

            // Search input handlers with debounce
            let searchTimeout;
            this.container.on('keyup', '.enterprise-search-input', function() {
                clearTimeout(searchTimeout);
                self.searchQuery = $(this).val();
                searchTimeout = setTimeout(() => {
                    self.currentPage = 1;
                    self.saveState();
                    self.load();
                }, 300);
            });

            // Filter dropdown handler
            this.container.on('change', '.edt-filter', function() {
                const name = $(this).data('name');
                const val = $(this).val();
                
                if (val === 'all') {
                    delete self.activeFilters[name];
                } else {
                    self.activeFilters[name] = val;
                }

                // Trigger filter callback if defined
                const fConfig = self.options.filters.find(f => f.name === name);
                if (fConfig && typeof fConfig.change === 'function') {
                    fConfig.change(val);
                }

                self.currentPage = 1;
                self.saveState();
                self.load();
            });

            // Density mode handler
            this.container.on('change', '.edt-density-select', function() {
                const density = $(this).val();
                self.options.density = density;
                self.container.find('.enterprise-table')
                    .removeClass('density-compact density-comfortable density-spacious')
                    .addClass('density-' + density);
                self.saveState();
            });

            // Page size selection handler
            this.container.on('change', '.edt-pagesize-select', function() {
                self.options.pageSize = parseInt($(this).val());
                self.currentPage = 1;
                self.saveState();
                self.load();
            });

            // Sorting headers click handler
            this.container.on('click', '.enterprise-table th.sortable', function() {
                const th = $(this);
                const key = th.data('key');
                
                if (self.sortKey === key) {
                    self.sortOrder = self.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    self.sortKey = key;
                    self.sortOrder = 'desc'; // default sorting direction on first click
                }

                // Reset visual indicator on all headers
                self.container.find('.enterprise-table th').removeClass('sorted-asc sorted-desc');
                th.addClass('sorted-' + self.sortOrder);
                
                self.currentPage = 1;
                self.saveState();
                self.load();
            });

            // Pagination button delegator
            this.container.on('click', '.table-pagination-btn', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== self.currentPage) {
                    self.currentPage = page;
                    self.saveState();
                    self.load();
                }
            });
        }

        showLoading(show) {
            this.isLoading = show;
            const overlay = this.container.find('.enterprise-loading-overlay');
            if (show) {
                overlay.fadeIn(100);
            } else {
                overlay.fadeOut(150);
            }
        }

        load() {
            if (this.isLoading) return;
            this.showLoading(true);

            if (this.options.serverSide) {
                this.loadServerSide();
            } else {
                this.loadClientSide();
            }
        }

        loadServerSide() {
            const self = this;
            
            // Build data request parameters
            const params = $.extend({
                page: this.currentPage,
                per_page: this.options.pageSize,
                search: this.searchQuery,
                sort_key: this.sortKey,
                sort_order: this.sortOrder
            }, this.options.data, this.activeFilters);

            $.ajax({
                url: this.options.url,
                method: this.options.method,
                data: params,
                success: function(res) {
                    self.showLoading(false);
                    if (res.status === 'success') {
                        let rows = [];
                        let total = 0;
                        let lastPage = 1;

                        // Support nested data attributes
                        if (self.options.dataKey === 'items' && res.data && res.data.items !== undefined) {
                            rows = res.data.items;
                            total = res.data.pagination?.total || res.data.total || 0;
                            lastPage = res.data.pagination?.last_page || res.data.last_page || 1;
                        } else if (res.data && Array.isArray(res.data)) {
                            rows = res.data;
                            total = res.data.length;
                        } else if (res.data && res.data[self.options.dataKey] !== undefined) {
                            rows = res.data[self.options.dataKey];
                            total = res.data[self.options.totalKey] || rows.length;
                            lastPage = res.data[self.options.lastPageKey] || 1;
                        } else if (res[self.options.dataKey] !== undefined) {
                            rows = res[self.options.dataKey];
                            total = res[self.options.totalKey] || rows.length;
                            lastPage = res[self.options.lastPageKey] || 1;
                        } else if (res.data && res.data.data !== undefined) {
                            // Support standard Laravel paginator
                            rows = res.data.data;
                            total = res.data.total;
                            lastPage = res.data.last_page;
                        }

                        self.totalRecords = total;
                        self.renderRows(rows);
                        self.renderPagination(self.currentPage, lastPage, total);
                        
                        if (typeof self.options.onLoad === 'function') {
                            self.options.onLoad(res, rows);
                        }
                    } else {
                        self.renderError('Unable to load server data.');
                    }
                },
                error: function(xhr) {
                    self.showLoading(false);
                    self.renderError(xhr.responseJSON?.message || 'Server error occurred.');
                    if (typeof self.options.onAjaxError === 'function') {
                        self.options.onAjaxError(xhr);
                    }
                }
            });
        }

        loadClientSide() {
            const self = this;

            const executeLocalRefers = (rawDataList) => {
                self.rawData = rawDataList || [];
                self.processClientSideData();
                self.showLoading(false);
                
                if (typeof self.options.onLoad === 'function') {
                    self.options.onLoad({ status: 'success', data: rawDataList }, self.filteredData);
                }
            };

            // If local data array is passed in options directly
            if (this.options.clientSideData) {
                executeLocalRefers(this.options.clientSideData);
                return;
            }

            // Fetch once from URL and then paginate locally
            $.ajax({
                url: this.options.url,
                method: this.options.method,
                data: this.options.data,
                success: function(res) {
                    if (res.status === 'success') {
                        let list = [];
                        if (Array.isArray(res.data)) {
                            list = res.data;
                        } else if (res.data && Array.isArray(res.data[self.options.dataKey])) {
                            list = res.data[self.options.dataKey];
                        } else if (Array.isArray(res[self.options.dataKey])) {
                            list = res[self.options.dataKey];
                        }
                        executeLocalRefers(list);
                    } else {
                        self.showLoading(false);
                        self.renderError('Failed to fetch data.');
                    }
                },
                error: function(xhr) {
                    self.showLoading(false);
                    self.renderError('Failed to query local data.');
                }
            });
        }

        processClientSideData() {
            let data = [...this.rawData];

            // 1. Apply Search
            if (this.searchQuery.trim()) {
                const search = this.searchQuery.toLowerCase().trim();
                data = data.filter(row => {
                    return this.options.columns.some(col => {
                        const val = row[col.key];
                        if (val === null || val === undefined) return false;
                        return String(val).toLowerCase().indexOf(search) !== -1;
                    });
                });
            }

            // 2. Apply active filters
            Object.keys(this.activeFilters).forEach(fKey => {
                const filterVal = this.activeFilters[fKey];
                if (filterVal !== undefined && filterVal !== 'all') {
                    data = data.filter(row => {
                        return row[fKey] == filterVal;
                    });
                }
            });

            // 3. Apply Sorting
            if (this.sortKey) {
                const key = this.sortKey;
                const order = this.sortOrder === 'asc' ? 1 : -1;
                data.sort((a, b) => {
                    let valA = a[key];
                    let valB = b[key];

                    // Null checks
                    if (valA === null || valA === undefined) valA = '';
                    if (valB === null || valB === undefined) valB = '';

                    // Numeric comparison
                    if (!isNaN(valA) && !isNaN(valB)) {
                        return (parseFloat(valA) - parseFloat(valB)) * order;
                    }

                    // String comparison
                    return String(valA).localeCompare(String(valB)) * order;
                });
            }

            this.filteredData = data;
            this.totalRecords = data.length;

            // 4. Slicing for pagination
            const total = this.totalRecords;
            const pageSize = this.options.pageSize;
            const lastPage = Math.ceil(total / pageSize) || 1;
            
            if (this.currentPage > lastPage) {
                this.currentPage = lastPage;
            }

            const startIdx = (this.currentPage - 1) * pageSize;
            const endIdx = startIdx + pageSize;
            const pageRows = data.slice(startIdx, endIdx);

            this.renderRows(pageRows);
            this.renderPagination(this.currentPage, lastPage, total);
        }

        renderRows(rows) {
            const tbody = this.container.find('.edt-tbody');
            tbody.empty();

            if (rows.length === 0) {
                const colSpan = this.options.columns.length;
                tbody.html(`
                    <tr>
                        <td colspan="${colSpan}" class="enterprise-empty-state">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 0.5rem auto; display:block;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            ${this.options.emptyMessage}
                        </td>
                    </tr>
                `);
                return;
            }

            let html = '';
            rows.forEach((row, rIdx) => {
                html += `<tr>`;
                
                this.options.columns.forEach(col => {
                    const alignClass = col.align ? 'text-' + col.align : 'text-left';
                    const priorityClass = col.priority ? 'edt-priority-' + col.priority : '';
                    let val = row[col.key];

                    let renderedVal = '';
                    if (typeof col.render === 'function') {
                        renderedVal = col.render(row, val);
                    } else {
                        renderedVal = val !== null && val !== undefined ? this.escape(val) : '';
                    }

                    html += `
                        <td class="${alignClass} ${priorityClass} ${col.key === 'actions' ? 'action-column' : ''}">
                            ${renderedVal}
                        </td>
                    `;
                });

                html += `</tr>`;
            });

            tbody.html(html);
        }

        renderPagination(currentPage, lastPage, total) {
            const wrapper = this.container.find('.table-pagination-wrapper');
            if (total === 0 || lastPage <= 1) {
                wrapper.hide().empty();
                return;
            }

            const pageSize = this.options.pageSize;
            const from = (currentPage - 1) * pageSize + 1;
            const to = Math.min(currentPage * pageSize, total);

            let html = `
                <div class="table-pagination-info">
                    Showing <strong>${from}–${to}</strong> of <strong>${total}</strong> records
                </div>
                <div class="table-pagination-controls">
            `;

            // First & Prev buttons
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            html += `
                <button class="table-pagination-btn table-pagination-prev" data-page="1" ${prevDisabled} title="First Page">&laquo;</button>
                <button class="table-pagination-btn table-pagination-prev" data-page="${currentPage - 1}" ${prevDisabled} title="Previous Page">Prev</button>
            `;

            // Max visible buttons
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(lastPage, startPage + maxVisible - 1);

            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                html += `<button class="table-pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
            }

            // Next & Last buttons
            const nextDisabled = currentPage === lastPage ? 'disabled' : '';
            html += `
                <button class="table-pagination-btn table-pagination-next" data-page="${currentPage + 1}" ${nextDisabled} title="Next Page">Next</button>
                <button class="table-pagination-btn table-pagination-next" data-page="${lastPage}" ${nextDisabled} title="Last Page">&raquo;</button>
            </div>`;

            wrapper.html(html).fadeIn(100);
        }

        renderError(msg) {
            const tbody = this.container.find('.edt-tbody');
            const colSpan = this.options.columns.length;
            tbody.html(`
                <tr>
                    <td colspan="${colSpan}" style="text-align:center; padding:2rem; color:#ef4444;">
                        <i class="fas fa-exclamation-triangle" style="margin-right:0.5rem;"></i> ${this.escape(msg)}
                    </td>
                </tr>
            `);
            this.container.find('.table-pagination-wrapper').hide();
        }

        escape(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        refresh() {
            this.load();
        }

        updateParams(newParams) {
            this.options.data = $.extend(this.options.data, newParams);
            this.currentPage = 1;
            this.saveState();
            this.load();
        }
    }

    // Export globally
    window.EnterpriseDataTable = EnterpriseDataTable;
})(jQuery);
