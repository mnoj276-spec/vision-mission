<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($jobs as $job)
    @php
        $detailRoute = \App\Domains\Jobs\Controllers\SitemapController::getDetailRoute($job);
        $imageUrl = $defaultImage;
        $imageTitle = htmlspecialchars($job->title, ENT_XML1, 'UTF-8');
        $imageCaption = htmlspecialchars('Government recruitment notification: ' . $job->title, ENT_XML1, 'UTF-8');
    @endphp
    <url>
        <loc>{{ $detailRoute }}</loc>
        {{-- Default OG / Featured Image --}}
        <image:image>
            <image:loc>{{ $imageUrl }}</image:loc>
            <image:title>{{ $imageTitle }}</image:title>
            <image:caption>{{ $imageCaption }}</image:caption>
        </image:image>
        {{-- Notification PDF as downloadable document image --}}
        @if($job->notification_pdf_path)
        <image:image>
            <image:loc>{{ url($job->notification_pdf_path) }}</image:loc>
            <image:title>{{ htmlspecialchars($job->title . ' - Official Notification PDF', ENT_XML1, 'UTF-8') }}</image:title>
            <image:caption>{{ htmlspecialchars('Official notification document for ' . $job->title, ENT_XML1, 'UTF-8') }}</image:caption>
        </image:image>
        @endif
    </url>
    @endforeach
</urlset>
