@inject('seoService', 'App\Domains\Jobs\Services\SeoService')
@php
    $seo = [
        'meta_title' => seo_setting('meta_title', 'GovJobs - Premium Automated Government Jobs Portal'),
        'meta_description' => seo_setting('meta_description', 'Discover real-time, highly validated recruitment alerts verified by AI across UPSC, SSC, Banking, and Railways. Fast, mobile responsive, and fully automated.'),
        'meta_keywords' => seo_setting('meta_keywords', 'government jobs, upsc, ssc, banking, railways, rrb, admit cards, results'),
        'og_title' => seo_setting('og_title', seo_setting('meta_title', 'GovJobs')),
        'og_description' => seo_setting('og_description', seo_setting('meta_description', 'Discover real-time...')),
        'og_image' => seo_setting('og_image', asset('assets/images/icons/pwa-icon-192.png')),
        'twitter_title' => seo_setting('twitter_title', seo_setting('meta_title', 'GovJobs')),
        'twitter_description' => seo_setting('twitter_description', seo_setting('meta_description', 'Discover real-time...')),
        'twitter_image' => seo_setting('twitter_image', seo_setting('og_image')),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $seo['meta_title'])</title>
    
    <!-- Meta SEO Binds -->
    <meta name="description" content="{{ $seo['meta_description'] }}">
    <meta name="keywords" content="{{ $seo['meta_keywords'] }}">
    <meta name="robots" content="{{ seo_setting('robots_txt', 'index, follow') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="@yield('title', $seo['og_title'])">
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    <meta property="og:image" content="{{ $seo['og_image'] }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ request()->url() }}">
    <meta property="twitter:title" content="@yield('title', $seo['twitter_title'])">
    <meta property="twitter:description" content="{{ $seo['twitter_description'] }}">
    <meta property="twitter:image" content="{{ $seo['twitter_image'] }}">

    <!-- Canonical URL (pagination-aware, query-stripped) -->
    @php
        $canonicalUrl = request()->url();
        // Preserve page param for paginated pages (page > 1)
        if (request()->has('page') && (int) request()->input('page') > 1) {
            $canonicalUrl .= '?page=' . (int) request()->input('page');
        }
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @yield('pagination_meta')
    
    <!-- Speed Optimization: Resource Hint Preconnects -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Speed Optimization: Async Web Fonts Loading -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&family=Hind:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Design Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Translate Custom Translator Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/css/translator.css') }}">

    @if(!auth()->check() || !in_array(auth()->user()->membership_plan, ['premium', 'pro']))
        @if(config('app.env') !== 'local' && config('app.env') !== 'testing')
            <!-- Asynchronous Google AdSense Integrations -->
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-mock-9876543210" crossorigin="anonymous" defer></script>
        @endif
        
        <!-- Asynchronous Ezoic Standalone Head Integration -->
        <script type="text/javascript" async defer>
            window.ezstandalone = window.ezstandalone || {};
            window.ezstandalone.cmd = window.ezstandalone.cmd || [];
        </script>
    @endif

    <!-- Global Technical SEO Structured Data (Organization, WebSite, SearchAction) -->
    <script type="application/ld+json">
    {!! json_encode($seoService->getSchemaService()->getOrganizationSchema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($seoService->getSchemaService()->getWebSiteSchema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @yield('schema')
    <!-- Progressive Web Application (PWA) Manifest & Mobile Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GovJobs">
    <link rel="apple-touch-icon" href="/assets/images/icons/pwa-icon-192.png">
    <meta name="theme-color" content="#2563eb">
    
    <!-- Anti-FOUC (Flash of Untranslated Content) style guard -->
    <script>
        (function() {
            const preferredLang = localStorage.getItem('preferred_language') || 'en';
            const userAgent = navigator.userAgent.toLowerCase();
            const isBot = /bot|googlebot|bingbot|yandex|baidu|crawler|spider|robot|crawling|lighthouse|chrome-lighthouse/i.test(userAgent);
            if (preferredLang === 'hi' && !isBot) {
                document.documentElement.classList.add('lang-hi');
                document.write('<style id="fouc-guard">body { visibility: hidden !important; }</style>');
            }
        })();
    </script>
    <!-- Dynamic theme custom properties injection -->
    <style>
        :root {
            @if(theme_setting('accent_color')) --accent-color: {{ theme_setting('accent_color') }}; @endif
            @if(theme_setting('accent_color')) --accent-hover: {{ theme_setting('accent_color') }}dd; @endif
            @if(theme_setting('background_color')) --bg-primary: {{ theme_setting('background_color') }}; @endif
            @if(theme_setting('text_color')) --text-primary: {{ theme_setting('text_color') }}; @endif
        }
        .dark-theme {
            @if(theme_setting('dark_primary_color')) --accent-color: {{ theme_setting('dark_primary_color') }}; @endif
            @if(theme_setting('dark_primary_color')) --accent-hover: {{ theme_setting('dark_primary_color') }}dd; @endif
            @if(theme_setting('dark_background_color')) --bg-primary: {{ theme_setting('dark_background_color') }}; @endif
        }
        
    </style>

    <!-- Custom Injected Header Scripts -->
    {!! setting('header_scripts') !!}
</head>
<body>

    <!-- 1. Mega Menu Header & Glassmorphic Navigation -->
    <header class="glass-panel">
        <nav class="navbar">
            <a href="/" class="logo">
                @if(setting('header_logo'))
                    <img src="{{ asset(setting('header_logo')) }}" alt="{{ setting('website_name', 'GovJobs') }}" style="max-height: 40px; border-radius: 4px;">
                @else
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-color);"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                @endif
                <span>{!! setting('website_name', 'Gov<span>Jobs</span>') !!}</span>
            </a>
            
            <ul class="nav-links">
                @forelse($headerMenu as $mItem)
                    @if($mItem->children->count() > 0)
                        <li class="nav-item-dropdown relative group">
                            <a href="{{ $mItem->url }}" target="{{ $mItem->target }}" class="dropdown-trigger flex items-center gap-1 font-bold">
                                @if($mItem->icon)
                                    <span class="menu-icon">
                                        @if(str_starts_with(trim($mItem->icon), '<'))
                                            {!! $mItem->icon !!}
                                        @else
                                            <i class="{{ $mItem->icon }}"></i>
                                        @endif
                                    </span>
                                @endif
                                <span data-translate-lookup="{{ $mItem->title }}">{{ $mItem->title }}</span>
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </a>
                            <ul class="dropdown-menu-list glass-panel hidden group-hover:block absolute top-full left-0 min-w-[200px] p-2 rounded-lg shadow-lg z-50">
                                @foreach($mItem->children as $childItem)
                                    <li>
                                        <a href="{{ $childItem->url }}" target="{{ $childItem->target }}" class="dropdown-item block px-4 py-2 rounded-md font-medium text-sm hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                            @if($childItem->icon)
                                                <span class="menu-icon">
                                                    @if(str_starts_with(trim($childItem->icon), '<'))
                                                        {!! $childItem->icon !!}
                                                    @else
                                                        <i class="{{ $childItem->icon }}"></i>
                                                    @endif
                                                </span>
                                            @endif
                                            <span data-translate-lookup="{{ $childItem->title }}">{{ $childItem->title }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li>
                            <a href="{{ $mItem->url }}" target="{{ $mItem->target }}" class="font-bold flex items-center gap-1">
                                @if($mItem->icon)
                                    <span class="menu-icon">
                                        @if(str_starts_with(trim($mItem->icon), '<'))
                                            {!! $mItem->icon !!}
                                        @else
                                            <i class="{{ $mItem->icon }}"></i>
                                        @endif
                                    </span>
                                @endif
                                <span data-translate-lookup="{{ $mItem->title }}">{{ $mItem->title }}</span>
                            </a>
                        </li>
                    @endif
                @empty
                    <li><a href="/#jobs-search-section" class="nav-tab-trigger font-bold" data-target="jobs" data-i18n="nav_home">Home</a></li>
                    <li><a href="/ssc-jobs" class="font-bold" data-i18n="nav_ssc">SSC Board</a></li>
                    <li><a href="/railway-jobs" class="font-bold" data-i18n="nav_railway">Railways</a></li>
                    <li><a href="/upsc-jobs" class="font-bold" data-i18n="nav_upsc">UPSC</a></li>
                    <li><a href="/state-jobs" class="font-bold" data-i18n="nav_state">State Jobs</a></li>
                    <li><a href="/#info-hub-section" class="nav-tab-trigger font-bold" data-target="info-hub" data-i18n="nav_info">Info Hub</a></li>
                @endforelse
            </ul>

            <div class="header-actions flex items-center gap-3">
                <!-- Language Switcher Badge -->
                <div class="lang-switcher" aria-label="Language Selector" role="navigation">
                    <button type="button" class="lang-btn active" data-lang="en" aria-label="Switch to English" aria-current="true">EN</button>
                    <span class="lang-divider" aria-hidden="true">|</span>
                    <button type="button" class="lang-btn" data-lang="hi" aria-label="Switch to Hindi" aria-current="false">हिन्दी</button>
                </div>

                <button class="theme-toggle-btn" id="themeToggle" aria-label="Toggle Theme Mode">
                    <svg id="themeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></svg>
                    <span id="themeText" data-i18n="theme_night">Night Mode</span>
                </button>

                @auth
                    <div class="user-menu-dropdown">
                        <button class="theme-toggle-btn flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span class="max-w-[100px] overflow-hidden text-ellipsis whitespace-nowrap">{{ auth()->user()->name }}</span>
                        </button>
                        <div class="dropdown-menu">
                            <a href="/#dashboard-section" class="dropdown-item nav-tab-trigger" data-target="dashboard" data-i18n="dropdown_dashboard">Dashboard</a>
                            @can('admin-access')
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item" data-i18n="dropdown_admin">Admin Panel</a>
                            @endcan
                            <div class="dropdown-divider border-t border-gray-200 my-1"></div>
                            <a href="javascript:void(0)" class="dropdown-item" id="logoutBtn" data-i18n="dropdown_logout">Logout</a>
                        </div>
                    </div>
                @else
                    <button class="theme-toggle-btn" id="openAuthModalBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        <span data-i18n="btn_login_register">Login / Register</span>
                    </button>
                @endauth

                <!-- Hamburger menu button for smaller screens -->
                <button class="hamburger-btn" id="hamburgerMenuBtn" aria-label="Toggle Navigation Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </nav>
    </header>

    <!-- Mobile Glassmorphic Navigation Drawer -->
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
    <div class="mobile-drawer glass-panel" id="mobileDrawer">
        <div class="mobile-drawer-header">
            <a href="/" class="logo">
                @if(setting('header_logo'))
                    <img src="{{ asset(setting('header_logo')) }}" alt="{{ setting('website_name', 'GovJobs') }}" style="max-height: 40px; border-radius: 4px;">
                @else
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-color);"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                @endif
                <span>{!! setting('website_name', 'Gov<span>Jobs</span>') !!}</span>
            </a>
            <button class="drawer-close-btn" id="closeMobileDrawerBtn" aria-label="Close navigation menu">&times;</button>
        </div>
        <ul class="mobile-drawer-links">
            @forelse($headerMenu as $mItem)
                <li>
                    <a href="{{ $mItem->url }}" target="{{ $mItem->target }}" class="mobile-drawer-link">
                        @if($mItem->icon)
                            <span class="menu-icon">
                                @if(str_starts_with(trim($mItem->icon), '<'))
                                    {!! $mItem->icon !!}
                                @else
                                    <i class="{{ $mItem->icon }}"></i>
                                @endif
                            </span>
                        @endif
                        <span data-translate-lookup="{{ $mItem->title }}">{{ $mItem->title }}</span>
                    </a>
                </li>
            @empty
                <li><a href="/#jobs-search-section" class="nav-tab-trigger mobile-drawer-link" data-target="jobs" data-i18n="nav_home">Home</a></li>
                <li><a href="/#jobs-search-section" class="nav-tab-trigger mobile-drawer-link" data-target="jobs" data-i18n="nav_jobs_list">Jobs List</a></li>
                <li><a href="/#info-hub-section" class="nav-tab-trigger mobile-drawer-link" data-target="info-hub" data-i18n="nav_info">Information Hub</a></li>
                <li><a href="/ssc-jobs" class="mobile-drawer-link" data-i18n="nav_ssc">SSC Board</a></li>
                <li><a href="/railway-jobs" class="mobile-drawer-link" data-i18n="nav_railway">Railways</a></li>
                <li><a href="/upsc-jobs" class="mobile-drawer-link" data-i18n="nav_upsc">UPSC</a></li>
                <li><a href="/state-jobs" class="mobile-drawer-link" data-i18n="nav_state">State Jobs</a></li>
                <li><a href="/admit-cards" class="mobile-drawer-link" data-i18n="nav_utilities">Exam Utilities</a></li>
            @endforelse
            @auth
                <li style="border-top: 1px solid var(--border-color); margin-top: 0.5rem; padding-top: 0.5rem;">
                    <a href="/#dashboard-section" class="nav-tab-trigger mobile-drawer-link" data-target="dashboard" data-i18n="dropdown_dashboard">Dashboard</a>
                </li>
                @can('admin-access')
                    <li><a href="{{ route('admin.dashboard') }}" class="mobile-drawer-link" data-i18n="dropdown_admin">Admin Panel</a></li>
                @endcan
                <li>
                    <a href="javascript:void(0)" class="mobile-drawer-link" id="mobileLogoutBtn" data-i18n="dropdown_logout">Logout</a>
                </li>
            @endauth
            
            <li style="border-top: 1px solid var(--border-color); margin-top: 0.5rem; padding-top: 0.75rem; display: flex; justify-content: center;">
                <div class="lang-switcher" aria-label="Language Selector" role="navigation">
                    <button type="button" class="lang-btn active" data-lang="en" aria-label="Switch to English" aria-current="true">EN</button>
                    <span class="lang-divider" aria-hidden="true">|</span>
                    <button type="button" class="lang-btn" data-lang="hi" aria-label="Switch to Hindi" aria-current="false">हिन्दी</button>
                </div>
            </li>
        </ul>
    </div>

    <!-- 2. Master Dynamic Content -->
    <main>
        @yield('content')
    </main>

    <!-- 3. Dynamic Footer -->
    <footer style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-color); padding: 4rem 5% 2rem 5%; font-size: 0.9rem; color: var(--text-secondary); margin-top: 4rem;">
        <div style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 3rem; margin-bottom: 3rem;">
            
            <!-- Column 1: Brand & Trust -->
            <div class="footer-brand">
                <h3 style="color: var(--text-primary); margin-bottom: 1rem; font-family: 'Outfit'; font-size: 1.5rem; font-weight: 700;">{{ setting('website_name', 'GovJobs') }}</h3>
                <p style="line-height: 1.6; margin-bottom: 1.5rem;">{{ setting('footer_about_text', 'GovJobs is India\'s premier platform for aggregating public sector employment notifications. We help job seekers find official government recruitment details efficiently.') }}</p>
                
                @if($socialLinks && $socialLinks->count() > 0)
                    <div class="social-links-row" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        @foreach($socialLinks as $sLink)
                            <a href="{{ $sLink->url }}" target="_blank" aria-label="Visit our {{ $sLink->platform }} page" title="{{ $sLink->platform }}" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-primary); text-decoration: none; font-size: 1.1rem; transition: all 0.3s ease;">
                                @if($sLink->icon)
                                    @if(str_starts_with(trim($sLink->icon), '<'))
                                        {!! $sLink->icon !!}
                                    @else
                                        <i class="{{ $sLink->icon }}"></i>
                                    @endif
                                @else
                                    <span style="font-size: 0.8rem; font-weight: bold;">{{ strtoupper(substr($sLink->platform, 0, 2)) }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Column 2: Top Job Categories -->
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1.25rem; font-family: 'Outfit'; font-size: 1.1rem; font-weight: 600;" data-translate-lookup="Top Job Categories">Top Job Categories</h4>
                <ul style="list-style: none; display: grid; gap: 0.75rem; padding: 0; margin: 0;">
                    @forelse($footerMenu3 ?? [] as $mItem)
                        <li><a href="{{ $mItem->url }}" target="{{ $mItem->target }}" style="color: var(--text-secondary); text-decoration: none; transition: color 0.3s ease;"><span data-translate-lookup="{{ $mItem->title }}">{{ $mItem->title }}</span></a></li>
                    @empty
                        <li><a href="/jobs/ssc" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="SSC Jobs">SSC Jobs</span></a></li>
                        <li><a href="/jobs/upsc" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="UPSC Jobs">UPSC Jobs</span></a></li>
                        <li><a href="/jobs/banking" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Banking Jobs">Banking Jobs</span></a></li>
                        <li><a href="/jobs/railway" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Railway Jobs">Railway Jobs</span></a></li>
                        <li><a href="/jobs/defence" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Defence Jobs">Defence Jobs</span></a></li>
                        <li><a href="/jobs/psu" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="PSU Jobs">PSU Jobs</span></a></li>
                    @endforelse
                </ul>
            </div>

            <!-- Column 3: Exam Resources -->
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1.25rem; font-family: 'Outfit'; font-size: 1.1rem; font-weight: 600;" data-translate-lookup="Exam Resources">Exam Resources</h4>
                <ul style="list-style: none; display: grid; gap: 0.75rem; padding: 0; margin: 0;">
                    @forelse($footerMenu4 ?? [] as $mItem)
                        <li><a href="{{ $mItem->url }}" target="{{ $mItem->target }}" style="color: var(--text-secondary); text-decoration: none; transition: color 0.3s ease;"><span data-translate-lookup="{{ $mItem->title }}">{{ $mItem->title }}</span></a></li>
                    @empty
                        <li><a href="/admit-cards" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Admit Cards">Admit Cards</span></a></li>
                        <li><a href="/results" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Exam Results">Exam Results</span></a></li>
                        <li><a href="/answer-keys" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Answer Keys">Answer Keys</span></a></li>
                        <li><a href="/syllabus" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Syllabus & Patterns">Syllabus & Patterns</span></a></li>
                        <li><a href="/exam-calendars" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Exam Calendars">Exam Calendars</span></a></li>
                    @endforelse
                </ul>
            </div>

            <!-- Column 4: Legal & Company -->
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1.25rem; font-family: 'Outfit'; font-size: 1.1rem; font-weight: 600;" data-translate-lookup="Legal & Company">Legal & Company</h4>
                <ul style="list-style: none; display: grid; gap: 0.75rem; padding: 0; margin: 0;">
                    @php
                        $combinedFooter = collect();
                        if(isset($footerMenu1) && $footerMenu1->count() > 0) {
                            $combinedFooter = $combinedFooter->merge($footerMenu1);
                        }
                        if(isset($footerMenu2) && $footerMenu2->count() > 0) {
                            $combinedFooter = $combinedFooter->merge($footerMenu2);
                        }
                    @endphp
                    @forelse($combinedFooter as $mItem)
                        <li><a href="{{ $mItem->url }}" target="{{ $mItem->target }}" style="color: var(--text-secondary); text-decoration: none; transition: color 0.3s ease;"><span data-translate-lookup="{{ $mItem->title }}">{{ $mItem->title }}</span></a></li>
                    @empty
                        <li><a href="/p/about-us" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="About Us">About Us</span></a></li>
                        <li><a href="/p/contact-us" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Contact Us">Contact Us</span></a></li>
                        <li><a href="/p/privacy-policy" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Privacy Policy">Privacy Policy</span></a></li>
                        <li><a href="/p/terms-of-service" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Terms of Service">Terms of Service</span></a></li>
                        <li><a href="/p/disclaimer" style="color: var(--text-secondary); text-decoration: none;"><span data-translate-lookup="Disclaimer">Disclaimer</span></a></li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Footer Bottom: Copyright & Disclaimer -->
        <div style="max-width: 1400px; margin: 0 auto; border-top: 1px solid var(--border-color); padding-top: 2rem;">
            <div style="display: flex; flex-direction: column; gap: 1rem; text-align: center; justify-content: center; align-items: center;">
                <p style="margin: 0; font-size: 0.85rem; max-width: 900px; line-height: 1.5; color: rgba(255,255,255,0.4);" data-translate-lookup="GovJobs is a private informational platform. We are not affiliated with the Government of India or any State Government. Always verify recruitment details on the official board websites.">
                    <strong>Disclaimer:</strong> GovJobs is a private informational platform. We are not affiliated with the Government of India or any State Government. Always verify recruitment details on the official board websites.
                </p>
                <p style="margin: 0; font-size: 0.95rem; color: var(--text-primary);">{!! setting('copyright_text', '&copy; ' . date('Y') . ' GovJobs. All rights reserved.') !!}</p>
            </div>
        </div>
    </footer>

    <!-- ================= DYNAMIC MODALS OVERLAYS ================= -->

    <!-- A. Authenticatable Login/Register Modal -->
    <div class="modal-overlay" id="authModal" style="z-index: 9999;">
        <div class="modal-box glass-panel">
            <button class="modal-close-btn" id="closeAuthModal" aria-label="Close Authentication Dialog">&times;</button>
            <div class="auth-tabs">
                <button class="auth-tab-btn active" data-tab="loginFormContainer" data-i18n="auth_tab_signin">Sign In</button>
                <button class="auth-tab-btn" data-tab="registerFormContainer" data-i18n="auth_tab_register">Register</button>
                <button class="auth-tab-btn" data-tab="forgotFormContainer" data-i18n="auth_tab_reset">Reset PW</button>
            </div>

            <!-- Login Sub-Form -->
            <div id="loginFormContainer">
                <form id="ajaxLoginForm" translate="no" class="notranslate">
                    @csrf
                    <div class="form-group">
                        <label for="loginEmail" data-i18n="lbl_email_addr">Email Address</label>
                        <input type="email" name="email" id="loginEmail" class="form-control" placeholder="candidate@example.com" required>
                        <div class="invalid-feedback" id="loginEmailError"></div>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword" data-i18n="lbl_password">Password</label>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                        <div class="invalid-feedback" id="loginPasswordError"></div>
                    </div>
                    <button type="submit" class="form-btn" id="loginSubmitBtn" data-i18n="auth_tab_signin">Sign In</button>
                    <p class="form-text"><span data-i18n="auth_forgot_pw">Forgot password?</span> <a href="#" class="auth-toggle-link" data-target="forgotFormContainer" data-i18n="auth_recover_acct">Recover account</a></p>
                </form>
            </div>

            <!-- Register Sub-Form -->
            <div id="registerFormContainer" style="display: none;">
                <form id="ajaxRegisterForm" translate="no" class="notranslate">
                    @csrf
                    <div class="form-group">
                        <label for="regName" data-i18n="lbl_full_name">Full Name</label>
                        <input type="text" name="name" id="regName" class="form-control" placeholder="John Doe" required>
                        <div class="invalid-feedback" id="regNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regEmail" data-i18n="lbl_email_addr">Email Address</label>
                        <input type="email" name="email" id="regEmail" class="form-control" placeholder="johndoe@example.com" required>
                        <div class="invalid-feedback" id="regEmailError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regPhone" data-i18n="lbl_phone_num">Phone Number</label>
                        <input type="text" name="phone" id="regPhone" class="form-control" placeholder="9876543210" required>
                        <div class="invalid-feedback" id="regPhoneError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regPassword" data-i18n="lbl_new_pass">Password (Min 6 chars)</label>
                        <input type="password" name="password" id="regPassword" class="form-control" placeholder="••••••••" required>
                        <div class="invalid-feedback" id="regPasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regPasswordConfirm" data-i18n="lbl_confirm_pass">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="form-btn" id="registerSubmitBtn" data-i18n="auth_btn_register">Register Now</button>
                    <p class="form-text"><span data-i18n="auth_already_reg">Already registered?</span> <a href="#" class="auth-toggle-link" data-target="loginFormContainer" data-i18n="auth_signin_instead">Sign In instead</a></p>
                </form>
            </div>

            <!-- Forgot Password OTP Reset Flow -->
            <div id="forgotFormContainer" style="display: none;">
                <!-- Step 1: Send OTP code -->
                <form id="ajaxForgotForm" translate="no" class="notranslate">
                    @csrf
                    <div class="form-group">
                        <label for="forgotEmail" data-i18n="lbl_reg_email_addr">Registered Email Address</label>
                        <input type="email" name="email" id="forgotEmail" class="form-control" placeholder="candidate@example.com" required>
                        <div class="invalid-feedback" id="forgotEmailError"></div>
                    </div>
                    <button type="submit" class="form-btn" id="forgotSubmitBtn" data-i18n="auth_btn_send_otp">Send Verification Code</button>
                </form>

                <!-- Step 2: Validate OTP and Set password -->
                <form id="ajaxResetForm" translate="no" class="notranslate" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                    @csrf
                    <input type="hidden" name="email" id="resetEmailHidden">
                    <div class="form-group">
                        <label for="resetOtp" data-i18n="lbl_enter_otp">Enter OTP Code (Sent: 123456)</label>
                        <input type="text" name="otp_code" id="resetOtp" class="form-control" placeholder="123456" required>
                        <div class="invalid-feedback" id="resetOtpError"></div>
                    </div>
                    <div class="form-group">
                        <label for="resetPassword" data-i18n="lbl_new_pass">New Password (Min 6 chars)</label>
                        <input type="password" name="password" id="resetPassword" class="form-control" placeholder="••••••••" required>
                        <div class="invalid-feedback" id="resetPasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="resetPasswordConfirm" data-i18n="lbl_confirm_pass">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="resetPasswordConfirm" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="form-btn" id="resetSubmitBtn" data-i18n="auth_btn_sync_pass">Synchronize Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- B. Asynchronous Job Details Modal -->
    <div class="modal-overlay" id="jobDetailsModal">
        <div class="modal-box glass-panel" style="max-width: 1000px;">
            <button class="modal-close-btn" id="closeJobDetailsModal" aria-label="Close Details Dialog">&times;</button>
            
            <!-- Skeleton Loader placeholder inside modal -->
            <div id="modalSkeletonLoader" class="skeleton-modal">
                <div class="skeleton-modal-line" style="height: 40px; width: 60%;"></div>
                <div class="skeleton-modal-line" style="height: 20px; width: 40%; margin-bottom: 2rem;"></div>
                <div class="details-grid">
                    <div class="skeleton-modal-line" style="height: 80px;"></div>
                    <div class="skeleton-modal-line" style="height: 80px;"></div>
                </div>
                <div class="skeleton-modal-line" style="height: 150px; width: 100%;"></div>
            </div>

            <!-- Authentic details block loaded dynamically -->
            <div id="modalRealContent" style="display: none;">
                <div class="details-header">
                    <h2 id="detailTitle" style="font-size: 1.8rem; margin-bottom: 0.25rem;"></h2>
                    <p style="color: var(--text-secondary); display: flex; gap: 0.75rem; flex-wrap: wrap; font-size: 0.95rem; margin-top: 0.5rem;">
                        <span class="badge" id="detailCategory"></span>
                        <span class="badge badge-dept" id="detailDepartment"></span>
                        <span class="badge" id="detailState" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"></span>
                    </p>
                </div>

                <div class="details-grid">
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Salary Range (Monthly)</div>
                        <div class="details-box-val">₹ <span id="detailSalary"></span></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Age Requirements</div>
                        <div class="details-box-val" id="detailAge"></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Total Vacancies</div>
                        <div class="details-box-val" id="detailVacancies"></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Application Fees</div>
                        <div class="details-box-val">₹ <span id="detailFee"></span></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Application Deadline</div>
                        <div class="details-box-val" id="detailDeadline" style="color: #ef4444;"></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Expected Exam Date</div>
                        <div class="details-box-val" id="detailExamDate"></div>
                    </div>
                </div>

                <div class="details-full-section">
                    <h4>Recruitment Overview & Eligibility</h4>
                    <p id="detailDescription" style="color: var(--text-secondary); margin-bottom: 1.25rem; font-size: 0.95rem;"></p>
                </div>

                <div class="details-full-section">
                    <h4>Official Syllabus & Exam Pattern</h4>
                    <div class="details-syllabus-container" id="detailSyllabus"></div>
                </div>

                <div class="details-full-section">
                    <h4>Selection Process</h4>
                    <p id="detailSelection" style="color: var(--text-secondary); font-size: 0.95rem;"></p>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                    <a id="detailOfficialLink" href="" target="_blank" class="btn-view" style="flex:1; text-align:center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; vertical-align: middle;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        Official Advertisement
                    </a>
                    
                    <!-- Detail Apply button removed -->
                </div>
            </div>

            <!-- Application Form section loaded inside details modal -->
            <div id="modalApplicationFormBlock" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                <h3 style="font-family: 'Outfit'; font-size: 1.3rem; margin-bottom: 0.5rem; color: var(--accent-color);">Recruitment Submission Form</h3>
                <form id="recruitmentApplicationForm" translate="no" class="notranslate" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="job_id" id="applicationFormJobId">
                    <div class="form-group">
                        <label for="appResume">Upload Default CV / Resume (PDF, DOC, DOCX up to 2MB)</label>
                        <input type="file" name="resume" id="appResume" class="form-control" required>
                        <div class="invalid-feedback" id="appResumeError"></div>
                    </div>
                    <div class="form-actions-flex" style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                        <button type="button" class="btn-view" id="cancelApplicationBtn" style="flex:1; text-align:center;">Cancel</button>
                        <button type="submit" class="form-btn" id="submitApplicationBtn" style="flex:2; margin-top:0;">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- C. Sidebar/Utilities Details Modal (Admit Cards / Results / Syllabus Detail) -->
    <div class="modal-overlay" id="sidebarDetailsModal">
        <div class="modal-box glass-panel" style="max-width: 600px;">
            <button class="modal-close-btn" id="closeSidebarDetailsModal" aria-label="Close Sidebar Details Dialog">&times;</button>
            <h3 style="font-family: 'Outfit'; font-size: 1.4rem; margin-bottom: 1.25rem; color: var(--accent-color);" id="sidebarDetailTitle">Exam Utility Info</h3>
            <div id="sidebarDetailBody" style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.75;">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- D. Sliding toast feedback alerts -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- 4. Local Offline jQuery and Theme JS Controller -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script>
        // Global Sliding Toast Dispenser
        function showToast(message, type = 'success') {
            const container = $('#toastContainer');
            const toastId = 'toast_' + Math.floor(Math.random() * 1000);
            
            let icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            if (type === 'error') {
                icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            } else if (type === 'warning') {
                icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            }

            const toastHtml = `
                <div class="toast toast-${type}" id="${toastId}">
                    ${icon}
                    <span>${message}</span>
                </div>
            `;
            container.append(toastHtml);
            
            setTimeout(() => {
                $(`#${toastId}`).addClass('active');
            }, 50);

            setTimeout(() => {
                const toast = $(`#${toastId}`);
                toast.removeClass('active');
                setTimeout(() => {
                    toast.remove();
                }, 400);
            }, 3500);
        }

        $(document).ready(function() {
            // Theme Toggle Controller Binds
            const body = $('body');
            const themeBtn = $('#themeToggle');
            const themeText = $('#themeText');
            
            if (localStorage.getItem('theme') === 'dark') {
                body.addClass('dark-theme');
                themeText.text('Day Mode');
            }

            themeBtn.on('click', function() {
                body.toggleClass('dark-theme');
                if (body.hasClass('dark-theme')) {
                    localStorage.setItem('theme', 'dark');
                    themeText.text('Day Mode');
                } else {
                    localStorage.setItem('theme', 'light');
                    themeText.text('Night Mode');
                }
            });

            // ================== MOBILE DRAWER TOGGLE SYSTEM ==================
            const mDrawer = $('#mobileDrawer');
            const mOverlay = $('#mobileDrawerOverlay');
            
            function openDrawer() {
                mDrawer.addClass('active');
                mOverlay.addClass('active');
                body.css('overflow', 'hidden');
            }
            
            function closeDrawer() {
                mDrawer.removeClass('active');
                mOverlay.removeClass('active');
                body.css('overflow', '');
            }

            $('#hamburgerMenuBtn').on('click', openDrawer);
            $('#closeMobileDrawerBtn, #mobileDrawerOverlay').on('click', closeDrawer);
            $(document).on('click', '.mobile-drawer-link', closeDrawer);

            // ================== USER DROPDOWN TOGGLE (CLICK-BASED) ==================
            // Works on touch devices where CSS :hover is unreliable
            const userDropdown = $('.user-menu-dropdown');
            userDropdown.find('> button').on('click', function(e) {
                e.stopPropagation();
                userDropdown.toggleClass('show');
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.user-menu-dropdown').length) {
                    $('.user-menu-dropdown').removeClass('show');
                    $('.user-menu-dropdown .dropdown-menu').css('display', '');
                }
                if (!$(e.target).closest('.nav-item-dropdown').length) {
                    $('.nav-item-dropdown').removeClass('show');
                }
            });

            // Close dropdown when clicking any dropdown item inside it
            $(document).on('click', '.user-menu-dropdown .dropdown-item', function() {
                $('.user-menu-dropdown').removeClass('show');
                $('.user-menu-dropdown .dropdown-menu').css('display', '');
            });

            // ================== HEADER MENU DROPDOWN TOGGLE (TOUCH-READY) ==================
            // Prevents immediate link navigation on first tap for touch screen devices
            const headerDropdowns = $('.nav-item-dropdown');
            headerDropdowns.find('> .dropdown-trigger').on('click', function(e) {
                if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
                    const parent = $(this).parent();
                    if (!parent.hasClass('show')) {
                        e.preventDefault();
                        e.stopPropagation();
                        headerDropdowns.not(parent).removeClass('show'); // Close other open menus
                        parent.addClass('show');
                    }
                }
            });

            headerDropdowns.find('.dropdown-item').on('click', function() {
                headerDropdowns.removeClass('show');
            });

            // ================== GLOBAL NAV-TAB-TRIGGER HANDLER ==================
            // Handles navigation from subpages (e.g., /ssc-jobs) to homepage sections
            // On the homepage, the handler in home.blade.php takes over via e.preventDefault()
            // On subpages, this ensures clicking navigates to the homepage with the correct hash
            $(document).on('click', '.nav-tab-trigger', function(e) {
                const target = $(this).data('target');
                const isHomepage = window.location.pathname === '/' || window.location.pathname === '';

                if (!isHomepage) {
                    // On subpages: let the browser navigate via the href (e.g., /#dashboard-section)
                    return; // Don't prevent default — let the <a href="/#..."> navigate naturally
                }

                // On homepage: prevent default browser jump and toggle the tab programmatically
                e.preventDefault();

                // Close mobile drawer if open
                if (typeof closeDrawer === 'function') {
                    closeDrawer();
                } else {
                    $('#mobileDrawer, #mobileDrawerOverlay').removeClass('active');
                    $('body').css('overflow', '');
                }

                // Close user dropdown if open
                $('.user-menu-dropdown').removeClass('show');
                $('.user-menu-dropdown .dropdown-menu').css('display', '');

                // Hide all homepage main tabs
                $('.portal-main-tab').hide();

                // Show and load selected tab
                if (target === 'dashboard') {
                    $('#dashboard-section').fadeIn();
                    if (typeof loadDashboardData === 'function') {
                        loadDashboardData();
                    }
                } else if (target === 'admin') {
                    $('#admin-section').fadeIn();
                    if (typeof loadAdminData === 'function') {
                        loadAdminData();
                    }
                } else if (target === 'info-hub') {
                    $('#info-hub-section').fadeIn();
                } else {
                    $('#jobs-search-section').fadeIn();
                }

                // Sync URL hash
                window.location.hash = target + '-section';
            });

            // ================== MOBILE LOGOUT HANDLER ==================
            $('#mobileLogoutBtn').on('click', function(e) {
                e.preventDefault();
                closeDrawer();
                $.ajax({
                    url: '/api/logout',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 800);
                    },
                    error: function() {
                        window.location.reload();
                    }
                });
            });


            // ================== LOGIN MODAL TRIGGERS ==================
            const authModal = $('#authModal');
            
            $(document).on('click', '#openAuthModalBtn, .trigger-auth-redirect-btn', function(e) {
                e.preventDefault();
                $('#ajaxLoginForm')[0].reset();
                $('#ajaxRegisterForm')[0].reset();
                $('#ajaxForgotForm')[0].reset();
                $('#ajaxResetForm')[0].reset();
                $('#ajaxResetForm').hide();
                $('#ajaxForgotForm').show();
                $('.invalid-feedback').hide();
                authModal.addClass('active');
            });

            $('#closeAuthModal, #authModal').on('click', function(e) {
                if (e.target === this || e.target.id === 'closeAuthModal') {
                    authModal.removeClass('active');
                }
            });

            // Tab Switching inside Auth modal
            $('.auth-tab-btn').on('click', function() {
                $('.auth-tab-btn').removeClass('active');
                $(this).addClass('active');
                
                const target = $(this).data('tab');
                $('#loginFormContainer, #registerFormContainer, #forgotFormContainer').hide();
                $(`#${target}`).show();
            });

            $('.auth-toggle-link').on('click', function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                $(`.auth-tab-btn[data-tab="${target}"]`).trigger('click');
            });

            // ================== AJAX LOGIN SUBMISSION ==================
            $('#ajaxLoginForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#loginSubmitBtn');
                btn.prop('disabled', true).text('Signing In...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/login',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Sign In');
                        if (err.status === 422 || err.status === 401) {
                            const res = err.responseJSON;
                            showToast(res.message || 'Login failed', 'error');
                            if (res.errors) {
                                Object.keys(res.errors).forEach(key => {
                                    $(`#login${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                                });
                            }
                        } else {
                            showToast('Server connection failed.', 'error');
                        }
                    }
                });
            });

            // ================== AJAX REGISTRATION SUBMISSION ==================
            $('#ajaxRegisterForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#registerSubmitBtn');
                btn.prop('disabled', true).text('Registering...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/register',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Register Now');
                        if (err.status === 422) {
                            const res = err.responseJSON;
                            showToast('Please correct validation flaws', 'error');
                            if (res.errors) {
                                Object.keys(res.errors).forEach(key => {
                                    $(`#reg${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                                });
                            }
                        } else {
                            showToast('Server connection failed.', 'error');
                        }
                    }
                });
            });

            // ================== AJAX FORGOT PASSWORD OTP TRIGGER ==================
            $('#ajaxForgotForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#forgotSubmitBtn');
                btn.prop('disabled', true).text('Requesting code...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/forgot-password',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).text('Send Verification Code');
                        showToast(res.message, 'success');
                        
                        // Proceed to Reset Step Form
                        $('#resetEmailHidden').val($('#forgotEmail').val());
                        $('#ajaxForgotForm').hide();
                        $('#ajaxResetForm').fadeIn();
                        
                        // Seed mock OTP in placeholder to make manual testing super friendly!
                        $('#resetOtp').val(res.otp_code || '123456');
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Send Verification Code');
                        if (err.status === 422 || err.status === 404) {
                            const res = err.responseJSON;
                            showToast(res.message || 'OTP dispatch failed.', 'error');
                            if (res.errors && res.errors.email) {
                                $('#forgotEmailError').text(res.errors.email[0]).show();
                            }
                        } else {
                            showToast('Server connection failed.', 'error');
                        }
                    }
                });
            });

            // ================== AJAX RESET PASSWORD CONFIRM SUBMIT ==================
            $('#ajaxResetForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#resetSubmitBtn');
                btn.prop('disabled', true).text('Overriding password...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/reset-password',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Synchronize Password');
                        const res = err.responseJSON;
                        showToast(res.message || 'Password override failed.', 'error');
                        if (res.errors) {
                            Object.keys(res.errors).forEach(key => {
                                $(`#reset${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                            });
                        }
                    }
                });
            });

            // ================== AJAX LOGOUT SUBMISSION ==================
            $('#logoutBtn').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/api/logout',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 800);
                    },
                    error: function() {
                        window.location.reload();
                    }
                });
            });

            // ================== DYNAMIC SIDEBAR DETAILS MODAL ==================
            const sModal = $('#sidebarDetailsModal');
            
            // Map sidebar clicks to show immersive instructions popup
            $(document).on('click', '.tab-item a', function(e) {
                e.preventDefault();
                const span = $(this).find('span[data-i18n]');
                const i18nKey = span.attr('data-i18n') || '';
                
                // Get clean title in Hindi or English
                const rawTitle = span.text();
                $('#sidebarDetailTitle').text(rawTitle);
                
                // Synthesize comprehensive mock description depending on clicks
                let detailsText = '';
                if (i18nKey.includes('admit')) {
                    detailsText = `
                        <strong>${window.t('modal_sidebar_rec_body', 'Recruitment Body')}:</strong> ${window.t('modal_sidebar_gov_board', 'Government Selection Board')}<br>
                        <strong>${window.t('modal_sidebar_rel_status', 'Release Status')}:</strong> <span style="color: #10b981; font-weight: 700;">${window.t('modal_sidebar_status_live', 'LIVE & Ready to Download')}</span><br>
                        <strong>${window.t('modal_sidebar_instructions_lbl', 'Instructions')}:</strong> ${window.t('modal_sidebar_admit_instr_val', 'Candidates can access their call letters by logging into their application reference dashboard. Please carry a printed copy of the Admit Card along with an active government-issued Photo ID (Aadhaar, Passport, PAN Card) to the allocated testing venue.')}<br><br>
                        <strong>${window.t('modal_exam_lbl', 'Expected Exam Date')}:</strong> ${window.t('modal_sidebar_scheduled_next_month', 'Scheduled for next month.')}<br>
                        <strong>${window.t('modal_sidebar_reporting_time', 'Reporting Time')}:</strong> ${window.t('modal_sidebar_reporting_val', '08:30 AM (Strict closing gate hours apply).')}
                    `;
                } else if (i18nKey.includes('result')) {
                    detailsText = `
                        <strong>${window.t('modal_sidebar_exam_segment', 'Examination Segment')}:</strong> ${window.t('modal_sidebar_merit_cutoff_val', 'Final Merit & Cutoff Index Lists')}<br>
                        <strong>${window.t('modal_sidebar_review_status', 'Review Status')}:</strong> ${window.t('modal_sidebar_verification_complete', 'Official Verification Complete')}<br>
                        <strong>${window.t('modal_sidebar_cutoff_params', 'Cutoff Parameters')}:</strong> General 78.5%, OBC 72.4%, SC/ST 65.0%<br><br>
                        ${window.t('modal_sidebar_result_congrats', 'Congratulations to all qualifying candidates! The selection board will dispatch individual call letters for physical verification and biometric checks via registered email profiles shortly.')}
                    `;
                } else {
                    detailsText = `
                        <strong>${window.t('modal_sidebar_subject_stream', 'Subject Stream')}:</strong> ${window.t('modal_sidebar_combined_syllabus', 'Combined Competitive Exam Syllabus Patterns')}<br>
                        <strong>${window.t('modal_sidebar_topic_outlines', 'Topic Outlines')}:</strong><br>
                        &bull; <strong>${window.t('modal_sidebar_paper_1', 'Paper I (Aptitude & Math)')}:</strong> ${window.t('modal_sidebar_paper_1_val', 'Quantitative Reasoning, Algebra, Numerical Analysis, Data Interpretation.')}<br>
                        &bull; <strong>${window.t('modal_sidebar_paper_2', 'Paper II (General Studies)')}:</strong> ${window.t('modal_sidebar_paper_2_val', 'Current Affairs, Constitutional Law, Public Policies, Indian History & Geography.')}<br><br>
                        <strong>${window.t('modal_sidebar_marking_scheme', 'Marking Scheme')}:</strong> ${window.t('modal_sidebar_marking_val', 'Objective type MCQ format (negative marking 0.25 index points for every wrong answer choice).')}
                    `;
                }
                
                $('#sidebarDetailBody').html(detailsText);
                sModal.addClass('active');
            });

            $('#closeSidebarDetailsModal, #sidebarDetailsModal').on('click', function(e) {
                if (e.target === this || e.target.id === 'closeSidebarDetailsModal') {
                    sModal.removeClass('active');
                }
            });

            // ================== HIGH PERFORMANCE TELEMETRY TRACKER ==================
            // 1. Auto Page View Track
            if (!window.location.pathname.startsWith('/admin') && !window.location.pathname.startsWith('/api')) {
                $.ajax({
                    url: '/api/analytics/page-view',
                    method: 'POST',
                    data: {
                        path: window.location.pathname,
                        referer: document.referrer,
                        _token: '{{ csrf_token() }}'
                    }
                });
            }

            // 2. Track Ad Slot Impressions
            $('.ad-banner-placeholder').each(function() {
                const slotName = $(this).attr('id') || 'home_sponsored_banner';
                $.ajax({
                    url: '/api/analytics/ad-event',
                    method: 'POST',
                    data: {
                        event_type: 'ad_impression',
                        slot_name: slotName,
                        _token: '{{ csrf_token() }}'
                    }
                });
            });

            // 3. Track Ad Slot Clicks
            $(document).on('click', '.ad-banner-placeholder', function() {
                const slotName = $(this).attr('id') || 'home_sponsored_banner';
                $.ajax({
                    url: '/api/analytics/ad-event',
                    method: 'POST',
                    data: {
                        event_type: 'ad_click',
                        slot_name: slotName,
                        _token: '{{ csrf_token() }}'
                    }
                });
            });

            // 4. Track External Apply link CTR Click
            $(document).on('click', '#detailOfficialLink', function() {
                const jobId = $('#applicationFormJobId').val();
                if (jobId) {
                    $.ajax({
                        url: '/api/analytics/job-event',
                        method: 'POST',
                        data: {
                            job_post_id: jobId,
                            event_type: 'apply_click',
                            _token: '{{ csrf_token() }}'
                        }
                    });
                }
            });

            // ================== PWA SERVICE WORKER & INSTALL MANAGER ==================
            let deferredPrompt;
            const installBanner = document.getElementById('pwaInstallBanner');

            // 1. Service Worker Bootloader Registration
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((reg) => {
                            console.log('[PWA Bootloader] Service Worker registered successfully: ', reg.scope);
                            
                            // Initialize Background Sync if supported
                            if ('sync' in reg) {
                                console.log('[PWA Bootloader] Background Sync engine is ready');
                            }
                        })
                        .catch((err) => console.log('[PWA Bootloader] Service Worker registration failed: ', err));
                });
            }

            // 2. Capture standalone install prompts
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Show installation banner to the candidate
                if (installBanner && localStorage.getItem('pwa_banner_dismissed') !== 'true') {
                    setTimeout(() => {
                        installBanner.style.display = 'flex';
                    }, 3000);
                }
            });

            // 3. Banner action click triggers browser installer
            $('#pwaInstallBannerAction').on('click', function() {
                if (!deferredPrompt) return;
                installBanner.style.display = 'none';
                
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA Installer] Candidate accepted installer prompt');
                        showToast('GovJobs successfully installed on your desktop/mobile!', 'success');
                    } else {
                        console.log('[PWA Installer] Candidate dismissed installer prompt');
                    }
                    deferredPrompt = null;
                });
            });

            // 4. Banner close action
            $('#pwaInstallBannerClose').on('click', function() {
                installBanner.style.display = 'none';
                localStorage.setItem('pwa_banner_dismissed', 'true');
            });

            // 5. Offline Job Alerts IndexedDB support:
            // Intercept standard Email Alert activation forms to queue them offline if navigator is offline
            $('#growthSubscribeForm').off('submit').on('submit', function(e) {
                const form = $(this);
                const emailInput = $('#subscriberEmail').val();
                const categoryInput = $('input[name="category_name"]').val();
                
                if (!navigator.onLine) {
                    e.preventDefault();
                    
                    // Queue subscription request into IndexedDB
                    try {
                        const dbReq = indexedDB.open('govjobs_offline_db', 1);
                        dbReq.onupgradeneeded = function(event) {
                            const db = event.target.result;
                            if (!db.objectStoreNames.contains('subscriptions')) {
                                db.createObjectStore('subscriptions', { keyPath: 'id', autoIncrement: true });
                            }
                        };
                        
                        dbReq.onsuccess = function(event) {
                            const db = event.target.result;
                            const tx = db.transaction(['subscriptions'], 'readwrite');
                            const store = tx.objectStore('subscriptions');
                            
                            store.add({
                                email: emailInput,
                                category_name: categoryInput,
                                token: $('input[name="_token"]').val(),
                                created_at: new Date().toISOString()
                            });
                            
                            tx.oncomplete = function() {
                                // Request background synchronization via service worker if possible
                                if ('serviceWorker' in navigator && 'SyncManager' in window) {
                                    navigator.serviceWorker.ready.then((reg) => {
                                        return reg.sync.register('sync-subscriptions');
                                    }).then(() => {
                                        console.log('[PWA Sync] Background sync token registered successfully');
                                    });
                                }
                                
                                showToast('Offline standby: Alert request queued in background sync!', 'warning');
                                form.html(`
                                    <div style="text-align: center; padding: 1rem 0; color: var(--accent-color);">
                                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-bottom: 0.5rem;"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div style="font-weight: 700; font-size: 0.9rem;">Queued for Connection Sync!</div>
                                    </div>
                                `);
                            };
                        };
                    } catch (err) {
                        showToast('Standby error: IndexedDB support failed.', 'error');
                    }
                }
            });
        });
    </script>

    <!-- Smart Translation Loading Spinner Overlay -->
    <div class="translation-loader" id="translationLoader" aria-hidden="true">
        <div class="spinner"></div>
        <span class="loader-text">Translating page / अनुवाद किया जा रहा है...</span>
    </div>

    <!-- Smart Language Auto-Detection Suggestion Card -->
    <div class="lang-suggestion-popup" id="langSuggestionPopup" role="alert" aria-live="assertive">
        <div class="popup-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <div class="popup-content">
            <h4 class="popup-title">Language / भाषा</h4>
            <p class="popup-text">Would you like to view this website in हिन्दी?</p>
            <div class="popup-actions">
                <button type="button" class="popup-btn btn-accept" id="btnSwitchToHindi">Switch to हिन्दी</button>
                <button type="button" class="popup-btn btn-dismiss" id="btnContinueEnglish">English</button>
            </div>
        </div>
    </div>

    <!-- Floating Reset Language Pill -->
    <button id="langResetPill" class="lang-reset-pill" aria-label="Back to English">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><polyline points="16 3 21 8 16 13"></polyline><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><polyline points="8 21 3 16 8 11"></polyline></svg>
        <span id="langResetPillText">Back to English</span>
    </button>

    <!-- Custom Google Translate Script Integration -->
    <script src="{{ asset('assets/js/translator.js') }}"></script>

    <!-- Global Table Pagination Script -->
    <script>
        $(document).ready(function() {
            function initTablePagination(table, perPage = 10) {
                const $table = $(table);
                const $tbody = $table.find('tbody');
                if (!$tbody.length) return;

                function applyPagination() {
                    const $rows = $tbody.children('tr').not('.no-paginate, .loading-row');
                    
                    // Filter out loading rows and message rows (usually 1 cell with colspan)
                    const filteredRows = $rows.filter(function() {
                        const $tds = $(this).children('td');
                        if ($tds.length === 1 && ($tds.attr('colspan') || '').length > 0) {
                            return false;
                        }
                        return true;
                    });

                    const totalRows = filteredRows.length;

                    // Remove existing pagination controls for this table
                    $table.closest('.responsive-table-container').next('.table-pagination-wrapper').remove();
                    $table.next('.table-pagination-wrapper').remove();

                    if (totalRows <= perPage) {
                        filteredRows.show();
                        return;
                    }

                    const totalPages = Math.ceil(totalRows / perPage);
                    let currentPage = 1;

                    function showPage(page) {
                        currentPage = page;
                        const start = (page - 1) * perPage;
                        const end = start + perPage;

                        filteredRows.hide();
                        filteredRows.slice(start, end).show();

                        const showingStart = start + 1;
                        const showingEnd = Math.min(end, totalRows);
                        $wrapper.find('.table-pagination-info').text(
                            `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`
                        );

                        // Update buttons active status
                        $wrapper.find('.table-pagination-btn.page-num').removeClass('active');
                        $wrapper.find(`.table-pagination-btn.page-num[data-page="${page}"]`).addClass('active');

                        $wrapper.find('.table-pagination-prev').prop('disabled', page === 1);
                        $wrapper.find('.table-pagination-next').prop('disabled', page === totalPages);
                    }

                    const $wrapper = $('<div class="table-pagination-wrapper"></div>');
                    const $info = $('<div class="table-pagination-info"></div>');
                    const $controls = $('<div class="table-pagination-controls"></div>');

                    const $prevBtn = $('<button class="table-pagination-btn table-pagination-prev" type="button">&laquo; Prev</button>');
                    $prevBtn.on('click', function() {
                        if (currentPage > 1) showPage(currentPage - 1);
                    });
                    $controls.append($prevBtn);

                    // Add page numbers
                    for (let i = 1; i <= totalPages; i++) {
                        // Limit number of page buttons if there are too many (e.g. > 5)
                        if (totalPages > 5) {
                            // Show first, last, current, and adjacent pages
                            if (i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                                // Add ellipsis once
                                if (i === 2 && currentPage > 3) {
                                    $controls.append('<span style="color:var(--text-secondary); padding:0 0.25rem;">...</span>');
                                } else if (i === totalPages - 1 && currentPage < totalPages - 2) {
                                    $controls.append('<span style="color:var(--text-secondary); padding:0 0.25rem;">...</span>');
                                }
                                continue;
                            }
                        }

                        const $pageBtn = $(`<button class="table-pagination-btn page-num" type="button" data-page="${i}">${i}</button>`);
                        $pageBtn.on('click', function() {
                            showPage(i);
                        });
                        $controls.append($pageBtn);
                    }

                    const $nextBtn = $('<button class="table-pagination-btn table-pagination-next" type="button">Next &raquo;</button>');
                    $nextBtn.on('click', function() {
                        if (currentPage < totalPages) showPage(currentPage + 1);
                    });
                    $controls.append($nextBtn);

                    $wrapper.append($info).append($controls);

                    const $container = $table.closest('.responsive-table-container');
                    if ($container.length) {
                        $container.after($wrapper);
                    } else {
                        $table.after($wrapper);
                    }

                    showPage(1);
                }

                applyPagination();

                if (window.MutationObserver) {
                    if (table._paginationObserver) {
                        table._paginationObserver.disconnect();
                    }
                    const observer = new MutationObserver(function(mutations) {
                        let relevantChange = false;
                        mutations.forEach(function(m) {
                            if (m.type === 'childList') {
                                relevantChange = true;
                            }
                        });
                        if (relevantChange) {
                            observer.disconnect();
                            applyPagination();
                            observer.observe($tbody[0], { childList: true });
                        }
                    });
                    observer.observe($tbody[0], { childList: true });
                    table._paginationObserver = observer;
                }
            }

            // Initialize on all portal-table and salary-table elements
            function initAllTables() {
                $('.portal-table, .salary-table').each(function() {
                    if (!this._paginationObserver) {
                        initTablePagination(this, 10);
                    }
                });
            }

            initAllTables();

            // Also check for dynamically added tables or tab switches
            $(document).on('shown.bs.tab click', '[data-toggle="tab"], .salary-tab-btn, .admin-nav-links button', function() {
                setTimeout(initAllTables, 100);
            });
        });
    </script>

    <!-- PWA Smart Install App Banner -->
    <div id="pwaInstallBanner" style="position: fixed; bottom: 2rem; left: 2rem; right: 2rem; max-width: 500px; background: rgba(17, 24, 39, 0.95); border: 1px solid var(--border-color); box-shadow: var(--card-shadow); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 16px; padding: 1.25rem; display: none; align-items: center; justify-content: space-between; gap: 1rem; z-index: 1050; margin: 0 auto; animation: slide-up-pwa 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <style>
            @keyframes slide-up-pwa {
                from { transform: translateY(100px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        </style>
        <div style="display: flex; align-items: center; gap: 0.75rem; text-align: left;">
            <img src="/assets/images/icons/pwa-icon-96.png" width="48" height="48" alt="GovJobs Logo" style="border-radius: 10px;" loading="lazy">
            <div>
                <h4 style="font-family: 'Outfit'; font-size: 0.95rem; font-weight: 800; color: #ffffff; margin: 0 0 0.15rem 0;">Install GovJobs App</h4>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">Add to your home screen for instant updates & offline search!</p>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button id="pwaInstallBannerClose" style="background: rgba(255,255,255,0.05); color: var(--text-secondary); border: none; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">Not Now</button>
            <button id="pwaInstallBannerAction" style="background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer; white-space: nowrap;">Install App</button>
        </div>
    </div>
    
    @yield('scripts')
    <!-- Custom Injected Footer Scripts -->
    {!! setting('footer_scripts') !!}
</body>
</html>
