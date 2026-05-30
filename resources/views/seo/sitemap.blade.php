<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Landing Page Routes -->
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/ssc-jobs') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/railway-jobs') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/upsc-jobs') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/state-jobs') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Programmatic SEO Category Landing Pages -->
    <url>
        <loc>{{ route('seo.dynamic_railway') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.dynamic_banking') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.dynamic_ssc') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.dynamic_upsc') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.dynamic_defence') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.dynamic_psu') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>

    <!-- Programmatic SEO Utility Landing Pages -->
    <url>
        <loc>{{ route('seo.results') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.admit_cards') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.answer_keys') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('seo.syllabus') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>

    <!-- Programmatic SEO Location Landing Pages -->
    @php
        $dbStates = \App\Models\State::with('districts')->get();
    @endphp
    @foreach($dbStates as $st)
        @if($st->slug)
            <url>
                <loc>{{ route('seo.dynamic_state', ['state_slug' => $st->slug]) }}</loc>
                <changefreq>daily</changefreq>
                <priority>0.80</priority>
            </url>

            @foreach($st->districts as $dist)
                <url>
                    <loc>{{ route('seo.dynamic_district', ['state_slug' => $st->slug, 'district_slug' => $dist->slug]) }}</loc>
                    <changefreq>daily</changefreq>
                    <priority>0.75</priority>
                </url>
            @endforeach
        @endif
    @endforeach

    <!-- Dynamic Jobs Standalone Details Pages -->
    @foreach($jobs as $job)
        @php
            $detailRoute = match($job->post_type) {
                'result' => route('seo.result_detail', ['slug' => $job->slug]),
                'admit_card' => route('seo.admit_card_detail', ['slug' => $job->slug]),
                'answer_key' => route('seo.answer_key_detail', ['slug' => $job->slug]),
                'syllabus' => route('seo.syllabus_detail', ['slug' => $job->slug]),
                default => route('seo.job_detail', ['slug' => $job->slug]),
            };
        @endphp
        <url>
            <loc>{{ $detailRoute }}</loc>
            <lastmod>{{ $job->published_at ? $job->published_at->toAtomString() : now()->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
