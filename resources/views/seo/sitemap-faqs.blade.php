<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- FAQ Sitemap — URLs of all pages containing AI-generated FAQs --}}
    {{-- Helps Google discover FAQ-rich pages for rich snippet eligibility --}}
    @foreach($aiContents as $ai)
    @if($ai->jobPost)
    @php
        $detailRoute = \App\Domains\Jobs\Controllers\SitemapController::getDetailRoute($ai->jobPost);
        $lastmod = $ai->jobPost->updated_at ?? $ai->updated_at ?? now();
        $faqCount = is_array($ai->faqs) ? count($ai->faqs) : 0;
    @endphp
    @if($faqCount > 0)
    <url>
        <loc>{{ $detailRoute }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse($lastmod)->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
    @endif
    @endif
    @endforeach
</urlset>
