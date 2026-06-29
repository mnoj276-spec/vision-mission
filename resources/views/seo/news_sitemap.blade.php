<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($jobs as $job)
    @php
        $detailRoute = \App\Domains\Jobs\Controllers\SitemapController::getDetailRoute($job);

        // Build keywords from tags, category, and state
        $keywords = collect();
        if ($job->tags && $job->tags->count() > 0) {
            $keywords = $keywords->merge($job->tags->pluck('name'));
        }
        if ($job->category) {
            $keywords->push($job->category->name);
        }
        if ($job->state) {
            $keywords->push($job->state->name);
        }
        $keywords->push('Government Jobs');
        $keywordsString = htmlspecialchars($keywords->unique()->implode(', '), ENT_XML1, 'UTF-8');

        $newsTitle = htmlspecialchars($job->title, ENT_XML1, 'UTF-8');
        $imageUrl = config('app.url') . '/assets/images/icons/pwa-icon-512.png';
    @endphp
    <url>
        <loc>{{ $detailRoute }}</loc>
        <news:news>
            <news:publication>
                <news:name>GovJobs Automated Feed</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>{{ $job->published_at ? $job->published_at->toAtomString() : now()->toAtomString() }}</news:publication_date>
            <news:title>{{ $newsTitle }}</news:title>
            <news:keywords>{{ $keywordsString }}</news:keywords>
        </news:news>
        <image:image>
            <image:loc>{{ $imageUrl }}</image:loc>
            <image:title>{{ $newsTitle }}</image:title>
        </image:image>
    </url>
    @endforeach
</urlset>
