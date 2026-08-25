@inject('seoService', 'App\Domains\Jobs\Services\SeoService')
@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<style>
    /* Advanced Search Dashboard Style Tokens */
    .search-dashboard-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        align-items: start;
        margin-top: 1.5rem;
    }

    @media (max-width: 992px) {
        .search-dashboard-container {
            grid-template-columns: 1fr;
        }
    }

    /* Typo Correction Banner */
    .typo-banner {
        background: rgba(245, 158, 11, 0.08);
        border: 1.5px solid rgba(245, 158, 11, 0.2);
        color: #f59e0b;
        padding: 0.85rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        animation: slideDown 0.3s ease;
        font-weight: 500;
    }

    .typo-banner a {
        color: var(--text-primary);
        text-decoration: underline;
        font-weight: 700;
        cursor: pointer;
        transition: color 0.2s;
    }

    .typo-banner a:hover {
        color: var(--accent-color);
    }

    /* Autocomplete Suggestions Menu */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-top: 0.5rem;
        max-height: 480px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 15px 35px -5px rgba(0,0,0,0.25);
        display: none;
        backdrop-filter: blur(14px);
    }

    .autocomplete-section {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
    }

    .autocomplete-section:last-child {
        border-bottom: none;
    }

    .autocomplete-header {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--accent-color);
        letter-spacing: 0.08em;
        padding: 0.75rem 1rem 0.4rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255,255,255,0.01);
    }

    .autocomplete-item {
        padding: 0.6rem 1.25rem;
        font-size: 0.88rem;
        color: var(--text-secondary);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.15s ease;
    }

    .autocomplete-item:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--text-primary);
        padding-left: 1.5rem;
    }

    .autocomplete-item .badge-type {
        font-size: 0.68rem;
        font-weight: 700;
        background: rgba(255,255,255,0.04);
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        color: var(--text-secondary);
    }

    /* Skeleton Feed Placeholders */
    .skeleton-search-item {
        background: linear-gradient(90deg, var(--bg-secondary) 25%, rgba(255,255,255,0.03) 50%, var(--bg-secondary) 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
        height: 110px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        margin-bottom: 1rem;
    }

    @keyframes loading-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Clean Breadcrumbs */
    .breadcrumb-trail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin: 1.5rem 0;
        font-family: 'Outfit', sans-serif;
    }
    .breadcrumb-trail a {
        color: var(--accent-color);
        text-decoration: none;
    }
    .breadcrumb-trail a:hover {
        text-decoration: underline;
    }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* ==================== REDESIGNED SINGLE ROW SEARCH & FILTER SYSTEM ==================== */
    .search-toolbar-wrapper {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.02) 0%, rgba(255, 255, 255, 0.01) 100%);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 100;
    }

    .search-toolbar-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
    }

    .search-input-col {
        flex: 2.5;
        position: relative;
        min-width: 200px;
    }

    .search-input-container {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 0.25rem 0.5rem;
        transition: all 0.25s ease;
    }

    .search-input-container:focus-within {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .search-input-container input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.65rem 0.5rem;
        color: var(--text-primary);
        font-size: 1rem;
        font-weight: 500;
        outline: none;
    }

    .search-input-container svg.search-icon {
        color: var(--text-secondary);
        margin-left: 0.5rem;
        flex-shrink: 0;
    }

    .clear-search-btn {
        background: var(--bg-secondary);
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .clear-search-btn:hover {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.08);
    }

    .search-dropdown-col {
        flex: 1.2;
        min-width: 0; /* Reset for flex/grid on mobile */
        position: relative;
    }
    
    @media (min-width: 993px) {
        .search-dropdown-col {
            min-width: 180px;
        }
    }

    .search-btn-col {
        flex-shrink: 0;
    }

    /* Primary CTA Search Button */
    .btn-search-primary {
        background: linear-gradient(135deg, var(--accent-color) 0%, #1e40af 100%);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s ease;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
    }

    .btn-search-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
        filter: brightness(1.1);
    }

    .btn-search-primary:active {
        transform: translateY(0);
    }

    /* Custom Searchable Dropdowns */
    .searchable-dropdown {
        position: relative;
        width: 100%;
    }

    .dropdown-selected {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 0.65rem 1rem;
        color: var(--text-primary);
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .dropdown-selected:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }

    .searchable-dropdown.open .dropdown-selected {
        border-color: var(--accent-color);
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .caret-icon {
        color: var(--text-secondary);
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .searchable-dropdown.open .caret-icon {
        transform: rotate(180deg);
    }

    .dropdown-panel {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        border: 2px solid var(--accent-color);
        border-top: none;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        z-index: 1010;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .searchable-dropdown.open .dropdown-panel {
        display: block;
    }

    .dropdown-search {
        padding: 0.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .dropdown-search-input {
        width: 100%;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0.5rem;
        color: var(--text-primary);
        font-size: 0.9rem;
        outline: none;
    }

    .dropdown-search-input:focus {
        border-color: var(--accent-color);
    }

    .dropdown-list {
        max-height: 200px;
        overflow-y: auto;
        padding: 0.25rem 0;
    }

    .dropdown-list::-webkit-scrollbar {
        width: 6px;
    }

    .dropdown-list::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 3px;
    }

    .dropdown-option {
        padding: 0.6rem 1rem;
        color: var(--text-secondary);
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .dropdown-option:hover, .dropdown-option.highlighted {
        background: rgba(37, 99, 235, 0.08);
        color: var(--text-primary);
        padding-left: 1.25rem;
    }

    .dropdown-option.selected {
        background: var(--accent-color);
        color: #ffffff;
        font-weight: 600;
    }

    /* Quick suggestions row */
    .quick-suggestions-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
        font-size: 0.85rem;
    }

    .suggestion-label {
        font-weight: 600;
        color: var(--text-secondary);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .suggestions-chips-container {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 2px;
    }

    .suggestions-chips-container::-webkit-scrollbar {
        display: none;
    }

    .suggestion-chip-item {
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        color: var(--text-secondary);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .suggestion-chip-item:hover {
        border-color: var(--accent-color);
        color: var(--text-primary);
        transform: translateY(-1px);
        background: rgba(37, 99, 235, 0.05);
    }

    /* Sub-toolbar */
    .search-sub-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed var(--border-color);
        flex-wrap: wrap;
    }

    .sub-toolbar-left-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-advanced-trigger {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s;
    }

    .btn-advanced-trigger:hover {
        border-color: var(--accent-color);
        background: rgba(37, 99, 235, 0.04);
    }

    .btn-advanced-trigger.active {
        background: rgba(37, 99, 235, 0.08);
        border-color: var(--accent-color);
        color: var(--accent-color);
    }

    .btn-reset-trigger {
        background: transparent;
        border: 1px solid transparent;
        color: #ef4444;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s;
    }

    .btn-reset-trigger:hover {
        background: rgba(239, 68, 68, 0.08);
        border-color: rgba(239, 68, 68, 0.15);
    }

    /* Active Filter Summary tags */
    .active-filter-chips-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .active-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(37, 99, 235, 0.06);
        border: 1px solid rgba(37, 99, 235, 0.15);
        border-radius: 16px;
        padding: 0.3rem 0.7rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--accent-color);
    }

    .active-filter-chip .remove-filter-btn {
        cursor: pointer;
        font-size: 1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        transition: all 0.15s;
    }

    .active-filter-chip .remove-filter-btn:hover {
        background: #ef4444;
        color: #ffffff;
    }

    /* Collapsible Advanced Filters Drawer */
    .advanced-drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 1999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .advanced-drawer-overlay.open {
        opacity: 1;
        visibility: visible;
    }

    .advanced-drawer {
        position: fixed;
        top: 0;
        right: -420px;
        width: 400px;
        height: 100%;
        background: var(--bg-secondary);
        border-left: 1px solid var(--border-color);
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.25);
        z-index: 2000;
        display: flex;
        flex-direction: column;
        transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .advanced-drawer.open {
        right: 0;
    }

    .drawer-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .drawer-header h3 {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .close-drawer-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
        padding: 0.25rem;
        transition: color 0.2s;
    }

    .close-drawer-btn:hover {
        color: #ef4444;
    }

    .drawer-body {
        padding: 1.5rem 1.5rem 8rem 1.5rem;
        flex: 1;
        overflow-y: auto;
    }

    .filter-group {
        margin-bottom: 1.25rem;
    }

    .filter-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    /* User-friendly clearly visible custom toggle switch */
    .custom-switch {
        position: relative;
        display: inline-block;
        width: 46px !important;
        height: 26px !important;
    }

    .slider-switch {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.18) !important;
        border: 1.5px solid var(--border-color);
        transition: .3s;
        border-radius: 26px !important;
    }

    .slider-switch:before {
        position: absolute;
        content: "";
        height: 18px !important;
        width: 18px !important;
        left: 3px !important;
        bottom: 2.5px !important;
        background-color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.4);
        transition: .3s;
        border-radius: 50% !important;
    }

    .custom-switch input:checked + .slider-switch {
        background-color: #10b981 !important;
        border-color: #10b981;
    }

    .custom-switch input:checked + .slider-switch:before {
        transform: translateX(18px) !important;
    }

    .drawer-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 0.75rem;
        background: rgba(255, 255, 255, 0.01);
    }

    .btn-reset-drawer {
        flex: 1;
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border-radius: 8px;
        padding: 0.75rem;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }

    .btn-reset-drawer:hover {
        background: #ef4444;
        color: white;
    }

    .btn-apply-drawer {
        flex: 2;
        background: var(--accent-color);
        border: none;
        color: white;
        border-radius: 8px;
        padding: 0.75rem;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }

    .btn-apply-drawer:hover {
        filter: brightness(1.1);
    }

    /* Media Queries */
    @media (max-width: 992px) {
        .search-toolbar-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .search-input-col {
            grid-column: span 2;
        }
        .search-dropdown-col {
            grid-column: span 1;
        }
        .search-btn-col {
            grid-column: span 2;
        }
        .btn-search-primary {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .search-toolbar-main {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .search-input-col, .search-dropdown-col, .search-btn-col {
            width: 100%;
        }
        .advanced-drawer {
            width: 100%;
            height: auto;
            max-height: 85vh;
            bottom: -100%;
            top: auto;
            right: 0;
            border-left: none;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.25);
            transition: bottom 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .advanced-drawer.open {
            bottom: 0;
        }
    }
    
    /* Premium Empty State */
    .empty-state-panel {
        padding: 4rem; 
        text-align: center; 
        color: var(--text-secondary); 
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(255,255,255,0.01) 0%, rgba(255,255,255,0.03) 100%);
    }
    @media (max-width: 576px) {
        .empty-state-panel {
            padding: 2rem 1rem;
        }
    }
</style>

<div style="max-width: 1400px; margin: 0 auto; padding: 0 5%;">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-trail">
        <a href="/">Home</a>
        <span class="breadcrumb-separator">&raquo;</span>
        @if(count($breadcrumbs) > 1)
            @php $keys = array_keys($breadcrumbs); $lastLabel = end($keys); @endphp
            @foreach($breadcrumbs as $label => $url)
                @if($url)
                    <a href="{{ $url }}">{{ $label }}</a>
                    <span class="breadcrumb-separator">&raquo;</span>
                @else
                    <span>{{ $label }}</span>
                @endif
            @endforeach
        @else
            <span>Search</span>
        @endif
    </div>

    <!-- Redesigned Single Row Search Bar Layout -->
    <div class="search-toolbar-wrapper">
        <div class="search-toolbar-main">
            <!-- 1. Search Input Column -->
            <div class="search-input-col">
                <div class="search-input-container">
                    <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="searchKeywords" placeholder="Search Government Jobs, UPSC, SSC, Railway..." data-i18n="search_placeholder" value="{{ $activeFilters['search'] ?? '' }}" autocomplete="off">
                    <button type="button" id="clearSearchBtn" class="clear-search-btn" style="{{ empty($activeFilters['search']) ? 'display: none;' : '' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <!-- Autocomplete Suggestions Menu -->
                <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
            </div>

            <!-- 2. State searchable dropdown -->
            <div class="search-dropdown-col">
                <div class="searchable-dropdown" id="stateDropdownWrapper">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Region">
                        <span class="selected-text">All Regions</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search region..." class="dropdown-search-input" aria-label="Search region">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
            </div>

            <!-- 3. Qualification searchable dropdown -->
            <div class="search-dropdown-col">
                <div class="searchable-dropdown" id="qualificationDropdownWrapper">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Qualification">
                        <span class="selected-text">All Qualifications</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search qualification..." class="dropdown-search-input" aria-label="Search qualification">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
            </div>

            <!-- 4. Search Submit Button -->
            <div class="search-btn-col">
                <button type="button" class="btn-search-primary" id="searchSubmitBtn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Search</span>
                </button>
            </div>
        </div>

        <!-- Hidden/Sync dropdown elements -->
        <select id="stateSelectFilter" style="display: none;">
            <option value=""></option>
            @foreach($states as $state)
                <option value="{{ $state->id }}" {{ (isset($activeFilters['state_id']) && $activeFilters['state_id'] == $state->id) || (isset($activeFilters['state_slug']) && $activeFilters['state_slug'] == $state->slug) ? 'selected' : '' }}>{{ $state->name }}</option>
            @endforeach
        </select>
        <select id="qualSelectFilter" style="display: none;">
            <option value=""></option>
            @foreach($qualifications as $qual)
                <option value="{{ $qual->id }}" {{ (isset($activeFilters['qualification_id']) && $activeFilters['qualification_id'] == $qual->id) || (isset($activeFilters['qualification_slug']) && $activeFilters['qualification_slug'] == $qual->slug) ? 'selected' : '' }}>{{ $qual->name }}</option>
            @endforeach
        </select>

        <!-- Quick Suggestions Row -->
        <div class="quick-suggestions-row">
            <span class="suggestion-label"><span class="fire-icon">🔥</span> Quick Search:</span>
            <div class="suggestions-chips-container">
                <span class="suggestion-chip-item" data-query="UPSC">🔥 UPSC</span>
                <span class="suggestion-chip-item" data-query="SSC">🔥 SSC</span>
                <span class="suggestion-chip-item" data-query="Railway">🔥 Railway</span>
                <span class="suggestion-chip-item" data-query="Banking">🔥 Banking</span>
                <span class="suggestion-chip-item" data-query="Teaching">🔥 Teaching</span>
                <span class="suggestion-chip-item" data-query="Bihar Police">🔥 Bihar Police</span>
            </div>
        </div>

        <!-- Sub-toolbar containing Advanced Filters toggle and active tags summary -->
        <div class="search-sub-toolbar">
            <div class="sub-toolbar-left-group">
                <button type="button" class="btn-advanced-trigger" id="toggleAdvancedFiltersBtn">
                    <span>⚙ Advanced Filters</span>
                </button>
                <button type="button" class="btn-reset-trigger" id="resetFiltersTrigger">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span data-i18n="btn_reset">Reset All</span>
                </button>
            </div>
            <div class="active-filter-chips-list" id="activeFilterChipsContainer"></div>
        </div>
    </div>

    <!-- Advanced Filters Drawer overlay & container -->
    <div class="advanced-drawer-overlay" id="advancedDrawerOverlay"></div>
    <div class="advanced-drawer" id="advancedDrawer">
        <div class="drawer-header">
            <h3>⚙ Advanced Filters</h3>
            <button type="button" class="close-drawer-btn" id="closeDrawerBtn">&times;</button>
        </div>
        <div class="drawer-body" style="overflow-y: auto;">
            <!-- Stream Filter -->
            <div class="filter-group">
                <label class="filter-label">💼 Stream / Sector</label>
                <div class="searchable-dropdown" id="categoryDropdownWrapperFilter">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Category">
                        <span class="selected-text" data-i18n="all_streams">All Streams</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search category..." class="dropdown-search-input" aria-label="Search category">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
                <!-- Hidden select input to preserve all existing logic/APIs -->
                <select class="filter-select" id="categorySelectFilter" style="display: none;">
                    <option value="" data-i18n="all_streams">All Streams</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-translate-lookup="{{ $cat->name }}" {{ (isset($activeFilters['category_id']) && $activeFilters['category_id'] == $cat->id) || (isset($activeFilters['category_slug']) && $activeFilters['category_slug'] == $cat->slug) ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Board Filter -->
            <div class="filter-group">
                <label class="filter-label">🏢 Recruitment Board</label>
                <div class="searchable-dropdown" id="deptDropdownWrapperFilter">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Board">
                        <span class="selected-text" data-i18n="all_boards">All Boards</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search board..." class="dropdown-search-input" aria-label="Search board">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
                <!-- Hidden select input to preserve all existing logic/APIs -->
                <select class="filter-select" id="deptSelectFilter" style="display: none;">
                    <option value="" data-i18n="all_boards">All Boards</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" data-translate-lookup="{{ $dept->name }}" data-translate-suffix=" ({{ $dept->code }})" {{ (isset($activeFilters['department_id']) && $activeFilters['department_id'] == $dept->id) || (isset($activeFilters['department_slug']) && $activeFilters['department_slug'] == $dept->slug) ? 'selected' : '' }}>{{ $dept->name }} ({{ $dept->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Free Application -->
            <div class="filter-group">
                <div class="switch-wrapper" style="width: 100%; min-height: 48px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-primary); border: 1px solid var(--border-color); padding: 0.75rem 1rem; border-radius: 10px;">
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-secondary);" data-i18n="filter_free_app">Free Applications Only (₹0)</span>
                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                        <input type="checkbox" id="noFeeCheckFilter" {{ isset($activeFilters['has_no_fee']) && filter_var($activeFilters['has_no_fee'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn-reset-drawer" id="resetFiltersDrawerBtn">Reset All</button>
            <button type="button" class="btn-apply-drawer" id="applyFiltersDrawerBtn">Apply Filters</button>
        </div>
    </div>


    <!-- Spellcheck Did You Mean Banner -->
    <div id="typoCorrectionBanner" style="{{ empty($typoSuggestion) ? 'display: none;' : '' }}">
        @if(!empty($typoSuggestion))
            <div class="typo-banner">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span><span data-i18n="did_you_mean">Did you mean:</span> <a id="suggestedQueryLink" data-query="{{ $typoSuggestion }}">{{ $typoSuggestion }}</a> ?</span>
            </div>
        @endif
    </div>

    <!-- Active Search Filter Dashboard Grid -->
    <div class="search-dashboard-container" style="grid-template-columns: 1fr;">

        <!-- Right Side: Live Paginated Feed -->
        <div>
            <!-- Live Results Stats Panel -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                <h2 style="font-family:'Outfit'; font-size:1.4rem; display:flex; align-items:center; gap:0.5rem;">
                    <span style="display:inline-block; width:8px; height:20px; background:var(--accent-color); border-radius:4px;"></span>
                    <span id="jobsCountFeedback" data-translate-key="found_jobs" data-translate-count="{{ $jobs->total() }}">Found {{ $jobs->total() }} recruitments</span>
                </h2>
                <div style="font-size:0.85rem; color:var(--text-secondary); background:var(--bg-secondary); border:1px solid var(--border-color); padding:0.4rem 0.8rem; border-radius:6px;" data-i18n="sort_featured">
                    Sort: Featured First &bull; Fresh
                </div>
            </div>

            <!-- Skeletal Loader -->
            <div id="skeletonLoader" style="display: none;">
                <div class="skeleton-search-item"></div>
                <div class="skeleton-search-item"></div>
                <div class="skeleton-search-item"></div>
                <div class="skeleton-search-item"></div>
            </div>

            <!-- Job Cards Container -->
            <div id="jobsListContainer">
                @forelse($jobs as $job)
                    @php
                        $detailRoute = match($job->post_type) {
                            'result' => route('seo.result_detail', ['slug' => $job->slug]),
                            'admit_card' => route('seo.admit_card_detail', ['slug' => $job->slug]),
                            'answer_key' => route('seo.answer_key_detail', ['slug' => $job->slug]),
                            'syllabus' => route('seo.syllabus_detail', ['slug' => $job->slug]),
                            default => route('seo.job_detail', ['slug' => $job->slug]),
                        };
                    @endphp
                    <div class="glass-panel job-card" style="{{ $job->is_featured ? 'border-left: 4px solid var(--accent-color);' : '' }}">
                        <div class="job-info">
                            <h3 style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;" title="{{ $job->title }}">
                                <span class="notranslate" translate="no" data-translate-title="{{ $job->title }}" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis; white-space:normal; word-break:break-word; line-height:1.3;">{{ $job->title }}</span>
                                @if($job->is_featured)
                                    <span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.7rem; padding:0.15rem 0.4rem; flex-shrink:0;" data-i18n="badge_featured">FEATURED</span>
                                @endif
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.6rem;">
                                <span data-translate-lookup="{{ $job->department->name ?? 'Government Board' }}">{{ $job->department->name ?? 'Government Board' }}</span> &bull; <span data-translate-lookup="{{ $job->state->name ?? 'Pan India' }}">{{ $job->state->name ?? 'Pan India' }}</span>
                                @if($job->district) &bull; <span>{{ $job->district->name }}</span> @endif
                            </p>
                            <div class="job-tags">
                                <span class="badge badge-dept" data-translate-lookup="{{ str_replace("\n", " | ", $job->qualification->name ?? 'Eligibility Required') }}" data-translate-prefix="🎓 ">🎓 {{ str_replace("\n", " | ", $job->qualification->name ?? 'Eligibility Required') }}</span>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981; font-weight:700;" data-translate-key="vacancies_count" data-translate-prefix="👥 " data-translate-suffix=": {{ number_format($job->vacancy_count) }}">👥 Vacancies: {{ number_format($job->vacancy_count) }}</span>
                                <span class="badge" style="background: rgba(139, 92, 246, 0.08); color: #8b5cf6; font-weight:700;">
                                    @if($job->salary_min > 0)
                                        💸 ₹{{ number_format($job->salary_min, 0) }} - ₹{{ number_format($job->salary_max, 0) }}
                                    @else
                                        💸 <span data-i18n="govt_scale">Govt Scale</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="job-card-actions">
                            <a href="{{ $detailRoute }}" class="btn-view" style="text-decoration:none;" data-i18n="btn_view_details" aria-label="View details for {{ $job->title }}">View Details</a>
                            <span class="badge badge-deadline" style="text-align:center; width:100%; box-sizing:border-box;" data-translate-key="apply_by" data-translate-prefix="📅 " data-translate-suffix=": {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A' }}">📅 Apply by: {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="glass-panel empty-state-panel">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-secondary); margin-bottom:1rem; opacity:0.6;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <h3 style="font-family:'Outfit'; color:var(--text-primary); margin-bottom:0.5rem;">No matching recruitments found</h3>
                        <p style="font-size:0.9rem;">We couldn't locate any active postings matching your search filters. Try widening your criteria or resetting filters.</p>
                    </div>
                @endforelse
            </div>

            <!-- Dynamic AJAX Pagination Container -->
            <div class="pagination-container" id="paginationContainer">
                @if($jobs->lastPage() > 1)
                    @if($jobs->currentPage() > 1)
                        <a href="#" class="page-link" data-page="{{ $jobs->currentPage() - 1 }}">&laquo; Prev</a>
                    @endif
                    @for($i = 1; $i <= $jobs->lastPage(); $i++)
                        <a href="#" class="page-link {{ $i === $jobs->currentPage() ? 'active' : '' }}" data-page="{{ $i }}">{{ $i }}</a>
                    @endfor
                    @if($jobs->currentPage() < $jobs->lastPage())
                        <a href="#" class="page-link" data-page="{{ $jobs->currentPage() + 1 }}">Next &raquo;</a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('schema')
<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getBreadcrumbListSchema($breadcrumbs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- ItemList Schema -->
@php
  $itemListSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'numberOfItems' => $jobs->count(),
      'itemListElement' => collect($jobs->items())->map(fn($job, $index) => [
          '@type' => 'ListItem',
          'position' => $index + 1,
          'url' => route('seo.job_detail', ['slug' => $job->slug]),
          'name' => $job->title
      ])->toArray()
  ];
@endphp
<script type="application/ld+json">
{!! json_encode($itemListSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- JobPostings Schema -->
@foreach($jobs as $job)
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getJobPostingSchema($job), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endforeach
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let currentPage = 1;
        let autocompleteTimeout = null;

        // Perform AJAX search fetching
        function fetchSearchResults(page = 1) {
            currentPage = page;
            const filters = {
                search: $('#searchKeywords').val(),
                state_id: $('#stateSelectFilter').val(),
                category_id: $('#categorySelectFilter').val(),
                qualification_id: $('#qualSelectFilter').val(),
                department_id: $('#deptSelectFilter').val(),
                has_no_fee: $('#noFeeCheckFilter').is(':checked'),
                page: page
            };

            // Toggle loading indicators
            $('#jobsListContainer').hide();
            $('#paginationContainer').empty();
            $('#skeletonLoader').show();

            $.ajax({
                url: '/search',
                type: 'GET',
                data: filters,
                dataType: 'json',
                success: function(response) {
                    $('#skeletonLoader').hide();
                    
                    if (response.status === 'success') {
                        const data = response.data;
                        const jobs = data.jobs;

                        // 1. Update total count text
                        $('#jobsCountFeedback').text(window.t('found_jobs', `Found ${data.total} recruitments`).replace('{count}', data.total))
                                               .attr('data-translate-count', data.total);

                        // 2. Render spell suggestion did-you-mean banner
                        const typoBanner = $('#typoCorrectionBanner');
                        if (data.typo_suggestion) {
                            typoBanner.html(`
                                <div class="typo-banner">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span><span data-i18n="did_you_mean">${window.t('did_you_mean', 'Did you mean:')}</span> <a class="suggested-query" data-query="${data.typo_suggestion}">${data.typo_suggestion}</a> ?</span>
                                </div>
                            `).fadeIn();
                        } else {
                            typoBanner.hide();
                        }

                        // 3. Render Job cards list
                        if (jobs.length === 0) {
                            $('#jobsListContainer').html(`
                                <div class="glass-panel empty-state-panel">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-secondary); margin-bottom:1rem; opacity:0.6;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <h3 style="font-family:'Outfit'; color:var(--text-primary); margin-bottom:0.5rem;" data-i18n="no_matching_jobs">No matching recruitments found</h3>
                                    <p style="font-size:0.9rem;" data-i18n="no_matching_jobs_desc">We couldn't locate any active postings matching your search filters. Try widening your criteria or resetting filters.</p>
                                </div>
                            `).fadeIn();
                            return;
                        }

                        let html = '';
                        jobs.forEach(function(job) {
                            const isFeaturedBadge = job.is_featured ? `<span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.7rem; padding:0.15rem 0.4rem; flex-shrink:0;" data-i18n="badge_featured">${window.t('badge_featured', 'FEATURED')}</span>` : '';
                            const detailUrl = `/job/${job.slug}`; // Fallback router
                            
                            html += `
                                <div class="glass-panel job-card" style="${job.is_featured ? 'border-left: 4px solid var(--accent-color);' : ''}">
                                    <div class="job-info">
                                        <h3 style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;" title="${job.title}">
                                            <span class="notranslate" translate="no" data-translate-title="${job.title}" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis; white-space:normal; word-break:break-word; line-height:1.3;">${window.translateJobTitle(job.title)}</span> 
                                            ${isFeaturedBadge}
                                        </h3>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.6rem;">
                                            <span data-translate-lookup="${job.department}">${window.t(job.department, job.department)}</span> &bull; <span data-translate-lookup="${job.state}">${window.t(job.state, job.state)}</span>
                                        </p>
                                        <div class="job-tags">
                                            <span class="badge badge-dept" data-translate-lookup="${job.qualification || 'Eligibility Required'}">🎓 ${window.t(job.qualification || 'Eligibility Required', job.qualification || 'Eligibility Required')}</span>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981; font-weight:700;" data-translate-key="vacancies_count" data-translate-prefix="👥 " data-translate-suffix=": ${job.vacancy_count > 0 ? Number(job.vacancy_count).toLocaleString('en-IN') : 'Announced'}">👥 ${window.t('vacancies_count', 'Vacancies')}: ${job.vacancy_count > 0 ? Number(job.vacancy_count).toLocaleString('en-IN') : 'Announced'}</span>
                                            <span class="badge" style="background: rgba(139, 92, 246, 0.08); color: #8b5cf6; font-weight:700;">
                                                💸 ${parseFloat(String(job.salary_min).replace(/,/g, '')) > 0 ? `₹ ${job.salary_min} - ₹ ${job.salary_max}` : `<span data-i18n="govt_scale">${window.t('govt_scale', 'Govt Scale')}</span>`}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="job-card-actions">
                                        <a href="${detailUrl}" class="btn-view" style="text-decoration:none;" data-i18n="btn_view_details" aria-label="View details for ${job.title}">${window.t('btn_view_details', 'View Details')}</a>
                                        <span class="badge badge-deadline" style="text-align:center; width:100%; box-sizing:border-box;" data-translate-key="apply_by" data-translate-prefix="📅 " data-translate-suffix=": ${job.last_date}">📅 ${window.t('apply_by', 'Apply by')}: ${job.last_date}</span>
                                    </div>
                                </div>
                            `;
                        });

                        $('#jobsListContainer').html(html).fadeIn();

                        // 4. Rebuild pagination buttons
                        buildPaginationButtons(data.current_page, data.last_page);

                        // 5. Update browser URL history state to ensure SEO friendliness
                        const cleanFilters = {};
                        Object.keys(filters).forEach(key => {
                            if (filters[key] !== '' && filters[key] !== false && filters[key] !== null) {
                                cleanFilters[key] = filters[key];
                            }
                        });
                        window.history.pushState(null, '', '/search?' + $.param(cleanFilters));
                    }
                },
                error: function() {
                    $('#skeletonLoader').hide();
                    $('#jobsListContainer').html(`
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                            ${window.t('search_failed_timeout', 'Search failed! Connection to database indexing timed out. Please retry.')}
                        </div>
                    `).fadeIn();
                }
            });
        }

        // Quick Search Tags handler
        $('.suggestion-chip-item').on('click', function() {
            $('#searchKeywords').val($(this).data('query'));
            fetchSearchResults(1);
        });

        // Helper to construct pagination buttons
        function buildPaginationButtons(current, last) {
            if (last <= 1) return;

            let html = '';
            if (current > 1) {
                html += `<a href="#" class="page-link" data-page="${current - 1}">&laquo; ${window.t('btn_prev', 'Prev')}</a>`;
            }
            for (let i = 1; i <= last; i++) {
                const activeClass = i === current ? 'active' : '';
                html += `<a href="#" class="page-link ${activeClass}" data-page="${i}">${i}</a>`;
            }
            if (current < last) {
                html += `<a href="#" class="page-link" data-page="${current + 1}">${window.t('btn_next', 'Next')} &raquo;</a>`;
            }
            $('#paginationContainer').html(html);
        }

        // Initialize custom searchable dropdowns
        function initSearchableDropdown(wrapperId, hiddenSelectId, placeholderText) {
            const $wrapper = $(wrapperId);
            const $hiddenSelect = $(hiddenSelectId);
            const $selectedText = $wrapper.find('.selected-text');
            const $selectedDiv = $wrapper.find('.dropdown-selected');
            const $searchInput = $wrapper.find('.dropdown-search-input');
            const $panel = $wrapper.find('.dropdown-panel');
            const $list = $wrapper.find('.dropdown-list');
            const defaultI18nKey = $selectedText.attr('data-i18n') || '';

            function populateOptions() {
                $list.empty();
                $hiddenSelect.find('option').each(function() {
                    const val = $(this).val();
                    let text = $(this).text().trim();
                    if (!val) {
                        text = placeholderText;
                    }
                    const isSelected = $(this).is(':selected');
                    const lookup = $(this).attr('data-translate-lookup');
                    const prefix = $(this).attr('data-translate-prefix');
                    const suffix = $(this).attr('data-translate-suffix');
                    const i18nKey = $(this).attr('data-i18n');
                    
                    const optionAttr = {
                        class: 'dropdown-option' + (isSelected ? ' selected' : ''),
                        'data-value': val,
                        text: text,
                        role: 'option',
                        tabindex: '-1'
                    };
                    
                    if (lookup) { optionAttr['data-translate-lookup'] = lookup; }
                    if (prefix) { optionAttr['data-translate-prefix'] = prefix; }
                    if (suffix) { optionAttr['data-translate-suffix'] = suffix; }
                    if (i18nKey) { optionAttr['data-i18n'] = i18nKey; }

                    const $option = $('<div>', optionAttr);
                    $list.append($option);
                    if (isSelected) {
                        $selectedText.text(text);
                        if (val) {
                            $selectedText.removeAttr('data-i18n');
                        } else {
                            if (defaultI18nKey) {
                                $selectedText.attr('data-i18n', defaultI18nKey);
                            }
                        }
                        if (lookup) {
                            $selectedText.attr('data-translate-lookup', lookup);
                        } else {
                            $selectedText.removeAttr('data-translate-lookup');
                        }
                        if (prefix) {
                            $selectedText.attr('data-translate-prefix', prefix);
                        } else {
                            $selectedText.removeAttr('data-translate-prefix');
                        }
                        if (suffix) {
                            $selectedText.attr('data-translate-suffix', suffix);
                        } else {
                            $selectedText.removeAttr('data-translate-suffix');
                        }
                    }
                });
            }

            populateOptions();

            // Toggle panel
            $selectedDiv.on('click', function(e) {
                e.stopPropagation();
                const isOpen = $wrapper.hasClass('open');
                $('.searchable-dropdown').not($wrapper).removeClass('open');
                $wrapper.toggleClass('open');
                if (!isOpen) {
                    $searchInput.val('').trigger('input');
                    $searchInput.focus();
                }
            });

            // Handle option click
            $list.on('click', '.dropdown-option', function(e) {
                e.stopPropagation();
                const val = $(this).attr('data-value') || '';
                const text = $(this).text();
                $hiddenSelect.val(val).trigger('change');
                $selectedText.text(text);
                $wrapper.removeClass('open');
                $selectedDiv.focus();
            });

            // Handle search input filtering
            $searchInput.on('input', function() {
                const query = $(this).val().toLowerCase();
                $list.find('.dropdown-option').each(function() {
                    const text = $(this).text().toLowerCase();
                    if (text.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Keyboard accessibility
            $selectedDiv.on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });

            $searchInput.on('keydown', function(e) {
                const $visibleOptions = $list.find('.dropdown-option:visible');
                let activeIdx = $visibleOptions.index($list.find('.dropdown-option.highlighted'));
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = (activeIdx + 1) % $visibleOptions.length;
                    $visibleOptions.removeClass('highlighted');
                    $visibleOptions.eq(activeIdx).addClass('highlighted');
                    $visibleOptions.eq(activeIdx)[0].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = (activeIdx - 1 + $visibleOptions.length) % $visibleOptions.length;
                    $visibleOptions.removeClass('highlighted');
                    $visibleOptions.eq(activeIdx).addClass('highlighted');
                    $visibleOptions.eq(activeIdx)[0].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const $highlighted = $list.find('.dropdown-option.highlighted');
                    if ($highlighted.length > 0) {
                        $highlighted.trigger('click');
                    } else if ($visibleOptions.length > 0) {
                        $visibleOptions.first().trigger('click');
                    }
                } else if (e.key === 'Escape') {
                    $wrapper.removeClass('open');
                    $selectedDiv.focus();
                }
            });

            // Sync from hidden select changes
            $hiddenSelect.on('change', function() {
                const val = $(this).val();
                let selectedTextVal = placeholderText;
                let activeLookup = null;
                let activePrefix = null;
                let activeSuffix = null;
                
                $list.find('.dropdown-option').removeClass('selected').each(function() {
                    if (($(this).attr('data-value') || '') == val) {
                        $(this).addClass('selected');
                        selectedTextVal = $(this).text();
                        activeLookup = $(this).attr('data-translate-lookup');
                        activePrefix = $(this).attr('data-translate-prefix');
                        activeSuffix = $(this).attr('data-translate-suffix');
                    }
                });
                
                $selectedText.text(selectedTextVal);
                if (val) {
                    $selectedText.removeAttr('data-i18n');
                } else {
                    if (defaultI18nKey) {
                        $selectedText.attr('data-i18n', defaultI18nKey);
                    }
                }
                
                if (activeLookup) {
                    $selectedText.attr('data-translate-lookup', activeLookup);
                } else {
                    $selectedText.removeAttr('data-translate-lookup');
                }
                if (activePrefix) {
                    $selectedText.attr('data-translate-prefix', activePrefix);
                } else {
                    $selectedText.removeAttr('data-translate-prefix');
                }
                if (activeSuffix) {
                    $selectedText.attr('data-translate-suffix', activeSuffix);
                } else {
                    $selectedText.removeAttr('data-translate-suffix');
                }
            });
        }

        // Initialize searchable dropdowns
        initSearchableDropdown('#stateDropdownWrapper', '#stateSelectFilter', 'All Regions');
        initSearchableDropdown('#qualificationDropdownWrapper', '#qualSelectFilter', 'All Qualifications');
        initSearchableDropdown('#categoryDropdownWrapperFilter', '#categorySelectFilter', 'All Streams');
        initSearchableDropdown('#deptDropdownWrapperFilter', '#deptSelectFilter', 'All Boards');

        // Render initial chips on load
        updateFilterChips();

        // Close dropdowns clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.searchable-dropdown').length) {
                $('.searchable-dropdown').removeClass('open');
            }
        });

        // Advanced filter drawer toggle
        $('#toggleAdvancedFiltersBtn').on('click', function() {
            $('#advancedDrawer, #advancedDrawerOverlay').addClass('open');
            $(this).addClass('active');
            $('#advancedDrawer').find('select, input').first().focus();
        });

        $('#closeDrawerBtn, #advancedDrawerOverlay, #applyFiltersDrawerBtn').on('click', function() {
            $('#advancedDrawer, #advancedDrawerOverlay').removeClass('open');
            $('#toggleAdvancedFiltersBtn').removeClass('active');
        });

        // Selected Active Filter tag chips manager
        function updateFilterChips() {
            const container = $('#activeFilterChipsContainer');
            container.empty();
            let hasFilters = false;

            const stateVal = $('#stateSelectFilter').val();
            if (stateVal) {
                const text = $(`#stateSelectFilter option[value="${stateVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">📍 ${text} <span class="remove-filter-btn" data-type="state" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const qualVal = $('#qualSelectFilter').val();
            if (qualVal) {
                const text = $(`#qualSelectFilter option[value="${qualVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">🎓 ${text} <span class="remove-filter-btn" data-type="qualification" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const catVal = $('#categorySelectFilter').val();
            if (catVal) {
                const text = $(`#categorySelectFilter option[value="${catVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">💼 ${text} <span class="remove-filter-btn" data-type="category" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const deptVal = $('#deptSelectFilter').val();
            if (deptVal) {
                const text = $(`#deptSelectFilter option[value="${deptVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">🏢 ${text} <span class="remove-filter-btn" data-type="department" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const noFeeVal = $('#noFeeCheckFilter').is(':checked');
            if (noFeeVal) {
                container.append(`<div class="active-filter-chip">💸 Free Application <span class="remove-filter-btn" data-type="nofee" role="button" aria-label="Remove free applications filter">&times;</span></div>`);
                hasFilters = true;
            }

            if (hasFilters) {
                container.append(`<a href="#" id="clearAllFiltersBtn" style="font-size: 0.85rem; color: #ef4444; font-weight: 600; text-decoration: none; margin-left: 0.5rem;">Clear All</a>`);
            }
        }

        // Dismiss active filter chips handler
        $('#activeFilterChipsContainer').on('click', '.remove-filter-btn', function() {
            const type = $(this).data('type');
            if (type === 'state') {
                $('#stateSelectFilter').val('').trigger('change');
            } else if (type === 'qualification') {
                $('#qualSelectFilter').val('').trigger('change');
            } else if (type === 'category') {
                $('#categorySelectFilter').val('').trigger('change');
            } else if (type === 'department') {
                $('#deptSelectFilter').val('').trigger('change');
            } else if (type === 'nofee') {
                $('#noFeeCheckFilter').prop('checked', false).trigger('change');
            }
        });

        $(document).on('click', '#clearAllFiltersBtn', function(e) {
            e.preventDefault();
            $('#resetFiltersTrigger').trigger('click');
        });

        // ─── Filter Events Bindings ──────────────────────────────────────────

        $('#stateSelectFilter, #categorySelectFilter, #qualSelectFilter, #deptSelectFilter, #noFeeCheckFilter').on('change', function() {
            updateFilterChips();
            fetchSearchResults(1);
        });

        // Try searching suggestions click
        $(document).on('click', '.suggestion-chip-item', function() {
            const query = $(this).data('query');
            $('#searchKeywords').val(query);
            $('#clearSearchBtn').show();
            fetchSearchResults(1);
        });


        // Submit search on button click
        $('#searchSubmitBtn').on('click', function() {
            clearTimeout(searchTimeout);
            fetchSearchResults(1);
        });

        // Submit search on Enter keypress
        $('#searchKeywords').on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                fetchSearchResults(1);
                $('#autocompleteDropdown').hide();
            }
        });

        // Search Input debouncer keyup trigger
        let searchTimeout = null;
        $('#searchKeywords').on('input keyup', function() {
            const query = $(this).val();

            // Toggle clear search button
            if (query.length > 0) {
                $('#clearSearchBtn').show();
            } else {
                $('#clearSearchBtn').hide();
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                fetchSearchResults(1);
            }, 350);

            // ─── Autocomplete suggestions loading ───
            clearTimeout(autocompleteTimeout);
            if (query.length < 2) {
                $('#autocompleteDropdown').hide().empty();
                return;
            }

            autocompleteTimeout = setTimeout(function() {
                $.ajax({
                    url: '/api/search/autocomplete',
                    type: 'GET',
                    data: { q: query },
                    success: function(res) {
                        if (res.status === 'success') {
                            const data = res.data;
                            let html = '';
                            let totalSuggestions = 0;

                            // A. Matched Jobs
                            if (data.jobs && data.jobs.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">💼 Jobs Found</div>`;
                                data.jobs.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-job" data-slug="${item.slug}">
                                        <span>${item.title}</span>
                                        <span class="badge-type">${item.post_type}</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.jobs.length;
                            }

                            // B. Matched Categories
                            if (data.categories && data.categories.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">📁 Streams / Sectors</div>`;
                                data.categories.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="category" data-slug="${item.slug}">
                                        <span>${item.name} board listings</span>
                                        <span class="badge-type">stream</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.categories.length;
                            }

                            // C. Matched States
                            if (data.states && data.states.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">📍 Regions</div>`;
                                data.states.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="state" data-slug="${item.slug}">
                                        <span>Jobs located in ${item.name}</span>
                                        <span class="badge-type">region</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.states.length;
                            }

                            // D. Matched Qualifications
                            if (data.qualifications && data.qualifications.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">🎓 Degrees</div>`;
                                data.qualifications.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="qualification" data-slug="${item.slug}">
                                        <span>Postings requiring ${item.name}</span>
                                        <span class="badge-type">eligibility</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.qualifications.length;
                            }

                            // E. Matched Departments
                            if (data.departments && data.departments.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">🏢 Agencies & Boards</div>`;
                                data.departments.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="organization" data-slug="${item.slug}">
                                        <span>${item.name} (${item.code})</span>
                                        <span class="badge-type">board</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.departments.length;
                            }

                            if (totalSuggestions > 0) {
                                $('#autocompleteDropdown').html(html).fadeIn();
                            } else {
                                $('#autocompleteDropdown').hide().empty();
                            }
                        }
                    }
                });
            }, 150);
        });

        // Clear Search text
        $(document).on('click', '#clearSearchBtn', function() {
            $('#searchKeywords').val('');
            $(this).hide();
            $('#autocompleteDropdown').hide().empty();
            $('#typoCorrectionBanner').hide().empty();
            fetchSearchResults(1);
        });

        // Autocomplete click on direct job suggestion
        $(document).on('click', '.select-suggest-job', function() {
            const slug = $(this).data('slug');
            window.location.href = `/job/${slug}`;
        });

        // Autocomplete click on stream, state, degree, board
        $(document).on('click', '.select-suggest-slug', function() {
            const type = $(this).data('type');
            const slug = $(this).data('slug');
            window.location.href = `/search/${type}/${slug}`;
        });

        // Hide autocomplete when clicking outside input
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#search-filter-section').length) {
                $('#autocompleteDropdown').hide();
            }
        });

        // Spell suggestion clicked did-you-mean handler
        $(document).on('click', '#suggestedQueryLink, .suggested-query', function(e) {
            e.preventDefault();
            const query = $(this).data('query');
            $('#searchKeywords').val(query);
            $('#typoCorrectionBanner').hide();
            $('#clearSearchBtn').show();
            fetchSearchResults(1);
        });

        // Reset all filters trigger
        $('#resetFiltersTrigger').on('click', function() {
            $('#searchKeywords').val('');
            $('#stateSelectFilter').val('').trigger('change');
            $('#categorySelectFilter').val('').trigger('change');
            $('#qualSelectFilter').val('').trigger('change');
            $('#deptSelectFilter').val('').trigger('change');
            $('#noFeeCheckFilter').prop('checked', false).trigger('change');
            $('#clearSearchBtn').hide();
            $('#autocompleteDropdown').hide().empty();
            $('#typoCorrectionBanner').hide();
            $('#advancedDrawer, #advancedDrawerOverlay').removeClass('open');
            $('#toggleAdvancedFiltersBtn').removeClass('active');
            
            fetchSearchResults(1);
        });

        // Pagination links click handler
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const targetPage = $(this).data('page');
            fetchSearchResults(targetPage);
            $('html, body').animate({ scrollTop: $('#searchKeywords').offset().top - 120 }, 300);
        });
    });
</script>
@endsection
