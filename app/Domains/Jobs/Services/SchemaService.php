<?php

namespace App\Domains\Jobs\Services;

use App\Models\JobPost;
use Illuminate\Support\Facades\Request;

class SchemaService
{
    /**
     * Load SEO settings from json cache with defaults.
     */
    protected function getSeoSettings(): array
    {
        $seoPath = storage_path('app/seo_settings.json');
        return file_exists($seoPath)
            ? json_decode(file_get_contents($seoPath), true)
            : [
                'meta_title'       => 'GovJobs - Premium Government Jobs Portal',
                'meta_description' => 'Browse and search live verified government recruitments across multiple departments.',
                'meta_keywords'    => 'government jobs, state recruitments, dynamic portal',
            ];
    }

    /**
     * Generate dynamic Organization Schema.
     */
    public function getOrganizationSchema(): array
    {
        $settings = $this->getSeoSettings();
        $name = str_replace(' - GovJobs', '', $settings['meta_title'] ?? 'GovJobs');
        $url = request()->getSchemeAndHttpHost();
        $logoUrl = $url . '/assets/images/icons/pwa-icon-512.png';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $url . '/#organization',
            'name' => $name,
            'url' => $url,
            'description' => $settings['meta_description'] ?? 'Premium automated government jobs portal with AI-verified recruitment alerts.',
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => $url . '/#logo',
                'url' => $logoUrl,
                'width' => 512,
                'height' => 512,
                'caption' => $name
            ],
            'image' => $logoUrl,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'availableLanguage' => ['English', 'Hindi'],
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'IN',
            ],
            'sameAs' => [
                'https://t.me/gov_job_alerts_mock',
            ]
        ];
    }

    /**
     * Generate dynamic WebSite Schema.
     */
    public function getWebSiteSchema(): array
    {
        $settings = $this->getSeoSettings();
        $name = str_replace(' - GovJobs', '', $settings['meta_title'] ?? 'GovJobs');
        $url = request()->getSchemeAndHttpHost();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $url . '/#website',
            'name' => $name,
            'url' => $url,
            'description' => $settings['meta_description'] ?? '',
            'publisher' => [
                '@id' => $url . '/#organization'
            ],
            'potentialAction' => [
                $this->getSearchActionSchema()
            ]
        ];
    }

    /**
     * Generate SearchAction Schema for Sitelinks Searchbox.
     */
    public function getSearchActionSchema(): array
    {
        $url = request()->getSchemeAndHttpHost();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $url . '/search?search={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ];
    }

    /**
     * Generate dynamic BreadcrumbList Schema.
     */
    public function getBreadcrumbListSchema(array $breadcrumbs): array
    {
        $listElement = [];
        $position = 1;
        $baseUrl = request()->getSchemeAndHttpHost();

        // Always prepend Home unless it's already there
        $listElement[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => $baseUrl
        ];

        foreach ($breadcrumbs as $label => $url) {
            if (empty($label)) continue;

            $itemUrl = $url;
            if ($itemUrl && !str_starts_with($itemUrl, 'http')) {
                $itemUrl = str_starts_with($itemUrl, '/') ? $baseUrl . $itemUrl : $baseUrl . '/' . $itemUrl;
            }

            if (!$itemUrl) {
                $itemUrl = request()->fullUrl();
            }

            $listElement[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $label,
                'item' => $itemUrl
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listElement
        ];
    }

    /**
     * Generate FAQPage Schema from dynamically supplied FAQs array.
     */
    public function getFAQPageSchema(array $faqs): array
    {
        $mainEntity = [];

        foreach ($faqs as $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $mainEntity[] = [
                    '@type' => 'Question',
                    'name' => trim($faq['question']),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($faq['answer'])
                    ]
                ];
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity
        ];
    }

    /**
     * Generate dynamic, highly rich JobPosting Schema.
     */
    public function getJobPostingSchema(JobPost $job): array
    {
        $baseUrl = request()->getSchemeAndHttpHost();
        $detailUrl = route('seo.job_detail', ['slug' => $job->slug]);
        $logoUrl = $baseUrl . '/assets/images/icons/pwa-icon-512.png';

        // Load approved AI content summary for a cleaner meta description if available
        $cleanDesc = null;
        if ($job->relationLoaded('aiContent') && $job->aiContent && $job->aiContent->status === 'approved' && !empty($job->aiContent->summary)) {
            $cleanDesc = strip_tags($job->aiContent->summary);
        }
        if (empty($cleanDesc)) {
            $cleanDesc = strip_tags($job->description);
        }
        // Fallback for short descriptions
        if (empty($cleanDesc)) {
            $cleanDesc = "Apply online for {$job->title}. Find age limits, application fees, qualification requirements, and useful links.";
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            '@id' => $detailUrl . '/#jobposting',
            'title' => $job->title,
            'description' => trim($cleanDesc),
            'datePosted' => $job->published_at ? $job->published_at->toDateString() : ($job->created_at ? $job->created_at->toDateString() : now()->toDateString()),
            'validThrough' => $job->last_date_to_apply ? $job->last_date_to_apply->toDateString() : now()->addDays(45)->toDateString(),
            'employmentType' => 'FULL_TIME',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $job->department->name ?? 'Government Recruitment Board',
                'sameAs' => $job->official_website_link ?? 'https://upsc.gov.in',
                'logo' => $logoUrl
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressRegion' => $job->state->name ?? 'Pan India',
                    'addressCountry' => 'IN'
                ]
            ],
            'applicantLocationRequirements' => [
                '@type' => 'Country',
                'name' => 'India'
            ],
            'industry' => 'Government',
            'occupationalCategory' => $job->category->name ?? 'Government Services',
            'baseSalary' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'INR',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $job->salary_min > 0 ? (float)$job->salary_min : 15600.00,
                    'maxValue' => $job->salary_max > 0 ? (float)$job->salary_max : 39100.00,
                    'unitText' => 'MONTH'
                ]
            ],
            'educationRequirements' => [
                '@type' => 'EducationalOccupationalCredential',
                'credentialCategory' => $job->qualification->name ?? 'Degree Required'
            ]
        ];

        if ($job->vacancy_count > 0) {
            $schema['totalJobOpenings'] = $job->vacancy_count;
        }

        if ($job->apply_link) {
            $schema['directApply'] = true;
        }

        return $schema;
    }

    /**
     * Generate dynamic Article Schema for results/syllabi/admit cards.
     */
    public function getArticleSchema(JobPost $job): array
    {
        $baseUrl = request()->getSchemeAndHttpHost();
        $detailUrl = route('seo.job_detail', ['slug' => $job->slug]);
        $logoUrl = $baseUrl . '/assets/images/icons/pwa-icon-512.png';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => $detailUrl . '/#article',
            'headline' => $job->title,
            'description' => strip_tags($job->description),
            'datePublished' => $job->published_at ? $job->published_at->toDateString() : ($job->created_at ? $job->created_at->toDateString() : now()->toDateString()),
            'dateModified' => $job->updated_at ? $job->updated_at->toDateString() : now()->toDateString(),
            'mainEntityOfPage' => $detailUrl,
            'author' => [
                '@type' => 'Organization',
                'name' => 'GovJobs Editorial Team',
                'url' => $baseUrl
            ],
            'publisher' => [
                '@id' => $baseUrl . '/#organization'
            ],
            'image' => $logoUrl
        ];
    }

    /**
     * Generate Speakable Schema for Google Assistant voice search.
     * Targets the title and description sections for voice readability.
     */
    public function getSpeakableSchema(string $title, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $url . '/#speakable',
            'name' => $title,
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => [
                    '.detail-header-block h1',
                    '.details-section p',
                    '.seo-hero h1',
                    '.seo-hero p',
                ]
            ],
            'url' => $url
        ];
    }

    /**
     * Generate GovernmentService Schema for government job portal compliance.
     */
    public function getGovernmentServiceSchema(JobPost $job): array
    {
        $baseUrl = request()->getSchemeAndHttpHost();
        $detailUrl = route('seo.job_detail', ['slug' => $job->slug]);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentService',
            'name' => $job->title,
            'serviceType' => 'Government Recruitment',
            'serviceOperator' => [
                '@type' => 'GovernmentOrganization',
                'name' => $job->department->name ?? 'Government of India',
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'India'
            ],
            'audience' => [
                '@type' => 'Audience',
                'audienceType' => 'Job Seekers'
            ],
            'provider' => [
                '@id' => $baseUrl . '/#organization'
            ],
            'url' => $detailUrl
        ];
    }

    /**
     * Generate dynamic WebPage Schema.
     */
    public function getWebPageSchema(string $title, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $url . '/#webpage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'isPartOf' => [
                '@id' => request()->getSchemeAndHttpHost() . '/#website'
            ]
        ];
    }
}
