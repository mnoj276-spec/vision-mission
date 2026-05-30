<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
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
            <news:news>
                <news:publication>
                    <news:name>GovJobs Automated Feed</news:name>
                    <news:language>en</news:language>
                </news:publication>
                <news:publication_date>{{ $job->published_at ? $job->published_at->toAtomString() : now()->toAtomString() }}</news:publication_date>
                <news:title>{{ $job->title }}</news:title>
            </news:news>
        </url>
    @endforeach
</urlset>
