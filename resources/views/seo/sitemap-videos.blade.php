<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
    {{--
        Video Sitemap — Auto-discovers embedded YouTube/Vimeo URLs in job descriptions.
        When video_url column is added to job_posts table, this will auto-include those too.
    --}}
    @foreach($jobs as $job)
    @php
        $detailRoute = \App\Domains\Jobs\Controllers\SitemapController::getDetailRoute($job);
        $videoTitle = htmlspecialchars($job->title, ENT_XML1, 'UTF-8');
        $videoDescription = htmlspecialchars(
            \Illuminate\Support\Str::limit(strip_tags($job->description ?? ''), 200),
            ENT_XML1,
            'UTF-8'
        );

        // Extract first video URL from description
        $videoUrl = null;
        if (preg_match('/(https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/|vimeo\.com\/)[^\s"<>]+)/i', $job->description ?? '', $matches)) {
            $videoUrl = $matches[1];
        }

        // Convert YouTube watch URL to embed thumbnail
        $thumbnailUrl = $baseUrl . '/assets/images/icons/pwa-icon-512.png';
        if ($videoUrl && preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $idMatch)) {
            $thumbnailUrl = 'https://img.youtube.com/vi/' . $idMatch[1] . '/hqdefault.jpg';
        }
    @endphp
    @if($videoUrl)
    <url>
        <loc>{{ $detailRoute }}</loc>
        <video:video>
            <video:thumbnail_loc>{{ $thumbnailUrl }}</video:thumbnail_loc>
            <video:title>{{ $videoTitle }}</video:title>
            <video:description>{{ $videoDescription }}</video:description>
            <video:content_loc>{{ $videoUrl }}</video:content_loc>
            <video:publication_date>{{ $job->published_at ? $job->published_at->toAtomString() : now()->toAtomString() }}</video:publication_date>
            <video:family_friendly>yes</video:family_friendly>
        </video:video>
    </url>
    @endif
    @endforeach
</urlset>
