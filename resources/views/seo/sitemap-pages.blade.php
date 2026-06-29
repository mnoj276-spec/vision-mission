<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Homepage --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Static SEO Landing Pages --}}
    @php
        $staticPages = [
            ['url' => '/ssc-jobs',    'priority' => '0.9'],
            ['url' => '/railway-jobs','priority' => '0.9'],
            ['url' => '/upsc-jobs',   'priority' => '0.9'],
            ['url' => '/state-jobs',  'priority' => '0.9'],
            ['url' => '/search',      'priority' => '0.8'],
            ['url' => '/eligibility-checker', 'priority' => '0.7'],
            ['url' => '/salary-information',  'priority' => '0.7'],
        ];
    @endphp
    @foreach($staticPages as $page)
    <url>
        <loc>{{ url($page['url']) }}</loc>
        <changefreq>daily</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
    @endforeach

    {{-- Programmatic SEO Category Landing Pages --}}
    @php
        $seoLandingRoutes = [
            'seo.dynamic_railway', 'seo.dynamic_banking', 'seo.dynamic_ssc',
            'seo.dynamic_upsc', 'seo.dynamic_defence', 'seo.dynamic_psu',
        ];
    @endphp
    @foreach($seoLandingRoutes as $routeName)
    <url>
        <loc>{{ route($routeName) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    @endforeach

    {{-- Programmatic SEO Utility Landing Pages --}}
    @php
        $utilityRoutes = [
            'seo.results', 'seo.admit_cards', 'seo.answer_keys', 'seo.syllabus',
            'seo.cutoffs', 'seo.exam_calendars', 'seo.previous_year_papers',
        ];
    @endphp
    @foreach($utilityRoutes as $routeName)
    <url>
        <loc>{{ route($routeName) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    @endforeach

    {{-- Dynamic Category Search Landing Pages --}}
    @foreach($categories as $cat)
    <url>
        <loc>{{ route('search.category', ['category_slug' => $cat->slug]) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    @endforeach

    {{-- Programmatic SEO Location Landing Pages (States + Districts) --}}
    @foreach($states as $st)
    <url>
        <loc>{{ route('seo.dynamic_state', ['state_slug' => $st->slug]) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.80</priority>
    </url>
    @foreach($st->districts as $dist)
    @if($dist->slug)
    <url>
        <loc>{{ route('seo.dynamic_district', ['state_slug' => $st->slug, 'district_slug' => $dist->slug]) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.75</priority>
    </url>
    @endif
    @endforeach
    @endforeach
</urlset>
