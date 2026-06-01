<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Internal Linking System Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the automated internal linking engine that powers related
    | content suggestions, cross-type navigation, and crawl optimization
    | across all SEO pages.
    |
    */

    'enabled' => env('INTERNAL_LINKING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => env('INTERNAL_LINKING_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Link Limits Per Section
    |--------------------------------------------------------------------------
    |
    | Maximum number of links to render in each section. Keep these
    | reasonable to avoid overwhelming crawlers and users.
    |
    */

    'max_related_jobs'          => 6,
    'max_related_results'       => 4,
    'max_related_admit_cards'   => 4,
    'max_related_categories'    => 8,
    'max_state_recommendations' => 6,
    'max_cross_type_links'      => 4,

    /*
    |--------------------------------------------------------------------------
    | Dynamic Anchor Text
    |--------------------------------------------------------------------------
    */

    'anchor_max_length' => 65,  // Google SERP title cutoff

    /*
    |--------------------------------------------------------------------------
    | Multi-Signal Relevance Scoring Weights
    |--------------------------------------------------------------------------
    |
    | These weights determine how related content is ranked. Higher
    | weight = stronger signal for relevance scoring.
    |
    */

    'scoring' => [
        'same_department'   => 30,
        'same_category'     => 25,
        'same_state'        => 20,
        'same_qualification'=> 15,
        'shared_tags'       => 10,  // Per shared tag
        'recency_bonus'     => 10,  // Published within 7 days
        'vacancy_bonus'     => 5,   // High vacancy count
    ],

    /*
    |--------------------------------------------------------------------------
    | Crawl Optimization
    |--------------------------------------------------------------------------
    */

    'crawl_optimization' => [
        'preload_headers'       => true,   // Send Link: <url>; rel=preload
        'canonical_enforcement' => true,   // Add canonical Link header
        'max_links_per_page'    => 100,    // Crawl budget guard
        'nofollow_external'     => true,   // Auto rel=nofollow on outbound links
        'x_robots_tag'          => 'max-snippet:-1, max-image-preview:large, max-video-preview:-1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Post Type Route Mapping
    |--------------------------------------------------------------------------
    |
    | Maps post_type values to their named routes for URL generation.
    |
    */

    'post_type_routes' => [
        'job'        => 'seo.job_detail',
        'result'     => 'seo.result_detail',
        'admit_card' => 'seo.admit_card_detail',
        'answer_key' => 'seo.answer_key_detail',
        'syllabus'   => 'seo.syllabus_detail',
        'cutoff'     => 'seo.cutoff_detail',
        'exam_calendar' => 'seo.exam_calendar_detail',
        'prev_paper' => 'seo.prev_paper_detail',
    ],

    /*
    |--------------------------------------------------------------------------
    | Post Type Display Names
    |--------------------------------------------------------------------------
    |
    */

    'post_type_labels' => [
        'job'        => 'Latest Jobs',
        'result'     => 'Exam Results',
        'admit_card' => 'Admit Cards',
        'answer_key' => 'Answer Keys',
        'syllabus'   => 'Syllabus',
        'notice'     => 'Notices',
        'admission'  => 'Admissions',
        'scholarship'=> 'Scholarships',
        'cutoff'     => 'Cutoffs',
        'exam_calendar' => 'Exam Calendar',
        'prev_paper' => 'Previous Year Papers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Sector Mapping (for landing page routes)
    |--------------------------------------------------------------------------
    */

    'sector_routes' => [
        'railway' => ['route' => 'seo.dynamic_railway', 'label' => 'Railway Jobs',  'icon' => '🚂'],
        'banking' => ['route' => 'seo.dynamic_banking', 'label' => 'Banking Jobs',  'icon' => '🏦'],
        'ssc'     => ['route' => 'seo.dynamic_ssc',     'label' => 'SSC Jobs',      'icon' => '📋'],
        'upsc'    => ['route' => 'seo.dynamic_upsc',     'label' => 'UPSC Exams',    'icon' => '🏛️'],
        'defence' => ['route' => 'seo.dynamic_defence',  'label' => 'Defence Jobs',  'icon' => '🎖️'],
        'psu'     => ['route' => 'seo.dynamic_psu',      'label' => 'PSU Jobs',      'icon' => '🏗️'],
    ],

];
