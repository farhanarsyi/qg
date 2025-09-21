/**
 * Persistence Manager - Manages filter persistence and activity name display
 * across index.php and monitoring.php pages
 */

class PersistenceManager {
    constructor() {
        this.storageKey = 'qg_persistence';
        this.currentPage = this.detectCurrentPage();
        this.init();
    }

    /**
     * Detect current page based on URL or page content
     */
    detectCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('index.php') || path.includes('dashboard')) {
            return 'dashboard';
        } else if (path.includes('monitoring.php')) {
            return 'monitoring';
        }
        return 'unknown';
    }

    /**
     * Initialize persistence manager
     */
    init() {
        console.log(`🔄 [Persistence] Initializing for page: ${this.currentPage}`);
        this.loadPersistedData();
        this.setupEventListeners();
    }

    /**
     * Load persisted data from localStorage
     */
    loadPersistedData() {
        try {
            const persistedData = localStorage.getItem(this.storageKey);
            if (persistedData) {
                const data = JSON.parse(persistedData);
                console.log('📂 [Persistence] Loaded data:', data);
                return data;
            }
        } catch (error) {
            console.error('❌ [Persistence] Error loading persisted data:', error);
        }
        return null;
    }

    /**
     * Save data to localStorage
     */
    saveData(data) {
        try {
            const currentData = this.loadPersistedData() || {};
            const updatedData = {
                ...currentData,
                [this.currentPage]: {
                    ...currentData[this.currentPage],
                    ...data,
                    timestamp: Date.now()
                }
            };
            
            localStorage.setItem(this.storageKey, JSON.stringify(updatedData));
            console.log('💾 [Persistence] Data saved:', updatedData);
        } catch (error) {
            console.error('❌ [Persistence] Error saving data:', error);
        }
    }

    /**
     * Get persisted data for current page
     */
    getPersistedData() {
        const data = this.loadPersistedData();
        return data ? data[this.currentPage] : null;
    }

    /**
     * Save filter values
     */
    saveFilters(filters) {
        this.saveData({ filters });
    }

    /**
     * Get saved filter values
     */
    getSavedFilters() {
        const data = this.getPersistedData();
        return data ? data.filters : null;
    }

    /**
     * Save activity name and data
     */
    saveActivityInfo(activityName, activityData = null) {
        this.saveData({ 
            activityName,
            activityData,
            hasActivity: !!activityName
        });
    }

    /**
     * Get saved activity info
     */
    getSavedActivityInfo() {
        const data = this.getPersistedData();
        return data ? {
            activityName: data.activityName,
            activityData: data.activityData,
            hasActivity: data.hasActivity
        } : null;
    }

    /**
     * Clear all persisted data
     */
    clearAllData() {
        try {
            localStorage.removeItem(this.storageKey);
            console.log('🗑️ [Persistence] All data cleared');
        } catch (error) {
            console.error('❌ [Persistence] Error clearing data:', error);
        }
    }

    /**
     * Clear data for current page only
     */
    clearCurrentPageData() {
        try {
            const data = this.loadPersistedData();
            if (data) {
                delete data[this.currentPage];
                localStorage.setItem(this.storageKey, JSON.stringify(data));
                console.log('🗑️ [Persistence] Current page data cleared');
            }
        } catch (error) {
            console.error('❌ [Persistence] Error clearing current page data:', error);
        }
    }

    /**
     * Setup event listeners for automatic saving
     */
    setupEventListeners() {
        // Listen for filter changes
        $(document).on('change', 'select, input[type="text"], input[type="hidden"]', (e) => {
            if ($(e.target).closest('#filtersCard, #secondaryFilterCard').length > 0) {
                this.autoSaveFilters();
            }
        });

        // Listen for page unload to save current state
        $(window).on('beforeunload', () => {
            this.autoSaveFilters();
        });
    }

    /**
     * Automatically save current filter state
     */
    autoSaveFilters() {
        const filters = this.extractCurrentFilters();
        if (filters && Object.keys(filters).length > 0) {
            this.saveFilters(filters);
        }
    }

    /**
     * Extract current filter values from the page
     */
    extractCurrentFilters() {
        const filters = {};

        if (this.currentPage === 'dashboard') {
            // Dashboard filters
            const projectFilter = $('#filterProject').val();
            const projectSearch = $('#filterProjectSearch').val();
            
            if (projectFilter) {
                filters.project = projectFilter;
                filters.projectName = projectSearch;
            }
        } else if (this.currentPage === 'monitoring') {
            // Monitoring filters
            const projectSelect = $('#projectSelect').val();
            const projectSearch = $('#projectSearch').val();
            const regionSelect = $('#regionSelect').val();
            const regionSearch = $('#regionSearch').val();
            
            if (projectSelect) {
                filters.project = projectSelect;
                filters.projectName = projectSearch;
            }
            
            if (regionSelect) {
                filters.region = regionSelect;
                filters.regionName = regionSearch;
            }

            // Secondary filters
            const secondaryFilters = {};
            $('#secondaryFilterCard select[multiple]').each(function() {
                const id = $(this).attr('id');
                const values = $(this).val();
                if (values && values.length > 0) {
                    secondaryFilters[id] = values;
                }
            });
            
            if (Object.keys(secondaryFilters).length > 0) {
                filters.secondary = secondaryFilters;
            }
        }

        return filters;
    }

    /**
     * Apply saved filters to the page
     */
    applySavedFilters() {
        const savedFilters = this.getSavedFilters();
        if (!savedFilters) return false;

        console.log('🔄 [Persistence] Applying saved filters:', savedFilters);

        if (this.currentPage === 'dashboard') {
            this.applyDashboardFilters(savedFilters);
        } else if (this.currentPage === 'monitoring') {
            this.applyMonitoringFilters(savedFilters);
        }

        return true;
    }

    /**
     * Apply filters to dashboard page
     */
    applyDashboardFilters(filters) {
        if (filters.project && filters.projectName) {
            $('#filterProject').val(filters.project);
            $('#filterProjectSearch').val(filters.projectName);
        }
    }

    /**
     * Apply filters to monitoring page
     */
    applyMonitoringFilters(filters) {
        if (filters.project && filters.projectName) {
            $('#projectSelect').val(filters.project);
            $('#projectSearch').val(filters.projectName);
        }

        if (filters.region && filters.regionName) {
            $('#regionSelect').val(filters.region);
            $('#regionSearch').val(filters.regionName);
        }

        if (filters.secondary) {
            Object.keys(filters.secondary).forEach(filterId => {
                const $select = $(`#${filterId}`);
                if ($select.length) {
                    $select.val(filters.secondary[filterId]);
                }
            });
        }
    }

    /**
     * Display activity name on the page
     */
    displayActivityName(activityName) {
        if (!activityName) return;

        // Remove existing activity name display
        $('.activity-name-display').remove();

        // Create activity name display
        const activityDisplay = $(`
            <div class="activity-name-display">
                <h4 class="activity-title">
                    <i class="fas fa-project-diagram me-2"></i>
                    ${activityName}
                </h4>
            </div>
        `);

        // Insert after filters card
        if (this.currentPage === 'dashboard') {
            $('#filtersCard').after(activityDisplay);
        } else if (this.currentPage === 'monitoring') {
            $('#projectSelectContainer').closest('.card').after(activityDisplay);
        }

        // Save activity name
        this.saveActivityInfo(activityName);
    }

    /**
     * Show saved activity name on page load
     */
    showSavedActivityName() {
        const activityInfo = this.getSavedActivityInfo();
        if (activityInfo && activityInfo.hasActivity && activityInfo.activityName) {
            this.displayActivityName(activityInfo.activityName);
            return true;
        }
        return false;
    }

    /**
     * Get activity name from current filters
     */
    getCurrentActivityName() {
        if (this.currentPage === 'dashboard') {
            return $('#filterProjectSearch').val() || 'Semua Kegiatan';
        } else if (this.currentPage === 'monitoring') {
            return $('#projectSearch').val() || 'Semua Kegiatan';
        }
        return 'Semua Kegiatan';
    }

    /**
     * Update activity name display
     */
    updateActivityName() {
        const activityName = this.getCurrentActivityName();
        if (activityName && activityName !== 'Semua Kegiatan') {
            this.displayActivityName(activityName);
        } else {
            $('.activity-name-display').remove();
            this.saveActivityInfo(null);
        }
    }
}

// CSS for activity name display
const activityNameCSS = `
<style>
.activity-name-display {
    margin: 1rem 0;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border: 1px solid #d1fae5;
    border-radius: 8px;
    border-left: 4px solid #059669;
}

.activity-title {
    color: #059669;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.activity-title i {
    color: #059669;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .activity-title {
        font-size: 1rem;
    }
}
</style>
`;

// Inject CSS
$('head').append(activityNameCSS);

// Global instance
window.persistenceManager = new PersistenceManager();
