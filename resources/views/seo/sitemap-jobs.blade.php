<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($jobs as $job)
    @php
        $detailRoute = \App\Domains\Jobs\Controllers\SitemapController::getDetailRoute($job);
        $lastmod = $job->updated_at ?? $job->published_at ?? now();
    @endphp
    <url>
        <loc>{{ $detailRoute }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse($lastmod)->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
