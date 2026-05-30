{{--
|--------------------------------------------------------------------------
| Internal Linking: Enhanced Landing Page Explorer Component
|--------------------------------------------------------------------------
|
| Replaces the static seo-explorer-card section with a richer, data-driven
| explorer featuring live job counts, trending categories, and freshness
| badges. Receives $explorer from InternalLinkingService::getLinksForLandingPage().
|
--}}

<style>
    /* ─── Enhanced Landing Explorer Styles ──────────────────────────────── */
    .il-explorer {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 2rem;
        margin-top: 3rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .il-explorer-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .il-explorer-subtitle {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }
    .il-explorer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 2rem;
    }
    .il-explorer-col h4 {
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--accent-color);
        margin-bottom: 0.85rem;
        border-bottom: 1.5px solid var(--border-color);
        padding-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .il-explorer-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 0;
        margin: 0;
    }
    .il-explorer-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.85rem;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.2s;
        padding: 0.3rem 0;
        border-radius: 4px;
    }
    .il-explorer-link:hover {
        color: var(--text-primary);
        padding-left: 0.35rem;
    }
    .il-explorer-link-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .il-explorer-badge {
        font-size: 0.65rem;
        font-weight: 700;
        background: rgba(37, 99, 235, 0.1);
        color: var(--accent-color);
        padding: 1px 6px;
        border-radius: 4px;
        flex-shrink: 0;
        margin-left: 0.5rem;
    }
    .il-explorer-badge-fresh {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }

    /* Trending Categories Chips */
    .il-trending-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.25rem;
    }
    .il-trending-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 99px;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.25s;
        background: var(--bg-secondary);
    }
    .il-trending-chip:hover {
        background: var(--accent-color);
        color: #fff;
        border-color: var(--accent-color);
    }
    .il-trending-chip .il-chip-count {
        font-size: 0.65rem;
        font-weight: 700;
        opacity: 0.7;
    }
</style>

<section class="il-explorer" id="il-landing-explorer">
    <h3 class="il-explorer-title">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: var(--accent-color);"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
        Explore More Government Job Hubs
    </h3>
    <p class="il-explorer-subtitle">Navigate our search networks for alternative recruitments, exam schedules, and circular announcements.</p>

    <div class="il-explorer-grid">
        {{-- Column 1: States with Job Counts --}}
        <div class="il-explorer-col">
            <h4>📍 State-Wise Jobs</h4>
            <ul class="il-explorer-list">
                @foreach($explorer['states'] as $st)
                    <li>
                        <a href="{{ route('seo.dynamic_state', ['state_slug' => $st->slug]) }}" class="il-explorer-link">
                            <span class="il-explorer-link-label">{{ $st->name }} Jobs</span>
                            @if($st->job_posts_count > 0)
                                <span class="il-explorer-badge">{{ $st->job_posts_count }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
                <li>
                    <a href="{{ route('seo.state') }}" class="il-explorer-link" style="color: var(--accent-color); font-weight: 700;">
                        View All States »
                    </a>
                </li>
            </ul>
        </div>

        {{-- Column 2: Districts (contextual) --}}
        @if($explorer['districts']->isNotEmpty())
            <div class="il-explorer-col">
                <h4>🏢 District Jobs</h4>
                <ul class="il-explorer-list">
                    @foreach($explorer['districts'] as $dist)
                        <li>
                            <a href="{{ route('seo.dynamic_district', ['state_slug' => $dist->state->slug, 'district_slug' => $dist->slug]) }}" class="il-explorer-link">
                                <span class="il-explorer-link-label">Jobs in {{ $dist->name }}</span>
                                @if(($dist->job_posts_count ?? 0) > 0)
                                    <span class="il-explorer-badge">{{ $dist->job_posts_count }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Column 3: Sector Categories --}}
        <div class="il-explorer-col">
            <h4>💼 Trending Sectors</h4>
            <ul class="il-explorer-list">
                @foreach($explorer['sectors'] as $sector)
                    <li>
                        <a href="{{ $sector['url'] }}" class="il-explorer-link">
                            <span class="il-explorer-link-label">{{ $sector['icon'] }} {{ $sector['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Column 4: Exam Utilities with Counts --}}
        <div class="il-explorer-col">
            <h4>✓ Utilities Hub</h4>
            <ul class="il-explorer-list">
                @foreach($explorer['utilities'] as $util)
                    <li>
                        <a href="{{ $util['url'] }}" class="il-explorer-link">
                            <span class="il-explorer-link-label">{{ $util['icon'] }} {{ $util['label'] }}</span>
                            @if(($util['count'] ?? 0) > 0)
                                <span class="il-explorer-badge il-explorer-badge-fresh">{{ $util['count'] }} active</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Trending DB Categories Row --}}
    @if($explorer['trending_categories']->isNotEmpty())
        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color);">
            <h4 style="font-family: 'Outfit'; font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
                🔥 Trending Categories
            </h4>
            <div class="il-trending-chips">
                @foreach($explorer['trending_categories'] as $tCat)
                    <a href="{{ route('search.category', ['category_slug' => $tCat->slug]) }}" class="il-trending-chip">
                        {{ $tCat->name }}
                        <span class="il-chip-count">({{ $tCat->job_posts_count }})</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>
