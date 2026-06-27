<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ $baseUrl }}/sitemaps/sitemap-pages.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ $baseUrl }}/sitemaps/sitemap-jobs.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ $baseUrl }}/sitemaps/sitemap-images.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ $baseUrl }}/sitemaps/sitemap-videos.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ $baseUrl }}/sitemaps/sitemap-faqs.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ $baseUrl }}/news-sitemap.xml</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
</sitemapindex>
