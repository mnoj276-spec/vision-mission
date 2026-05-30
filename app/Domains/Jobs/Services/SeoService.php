<?php

namespace App\Domains\Jobs\Services;

class SeoService
{
    /**
     * Get dynamic SEO metadata for a page.
     *
     * @param string $type Page context type (state, district, railway, banking, ssc, upsc, defence, psu, results, admit_cards, answer_keys, syllabus)
     * @param array $params Dynamic parameters (state, district, category, job, etc.)
     * @return array
     */
    public function getMetadata(string $type, array $params = []): array
    {
        $year = date('Y');
        $title = 'GovJobs - Premium Automated Government Jobs Portal';
        $h1 = 'Government Recruitments & Exams';
        $description = 'Discover real-time, highly validated recruitment alerts verified by AI. Fast, responsive, and fully automated.';
        $breadcrumbs = [];

        switch ($type) {
            case 'state':
                $stateName = $params['state_name'] ?? 'State';
                $title = "State-Wise Government Jobs in {$stateName} {$year} - GovJobs";
                $h1 = "Latest {$stateName} Government Jobs & PSC Board Recruitments";
                $description = "Find active, verified {$stateName} state government jobs (PSC boards, local departments, etc.). Detailed vacancy announcements, eligibility, and direct apply links.";
                $breadcrumbs = [
                    'State Jobs' => route('seo.state'),
                    $stateName => null
                ];
                break;

            case 'district':
                $stateName = $params['state_name'] ?? 'State';
                $districtName = $params['district_name'] ?? 'District';
                $stateSlug = $params['state_slug'] ?? '#';
                $title = "Government Jobs in {$districtName}, {$stateName} (Active Vacancies) - GovJobs";
                $h1 = "Government Jobs & Recruitments in {$districtName} District";
                $description = "Apply online for active government jobs in {$districtName}, {$stateName}. Get real-time updates on district-level posts, local administrative departments, and salary ranges.";
                $breadcrumbs = [
                    'State Jobs' => route('seo.state'),
                    $stateName => route('seo.dynamic_state', ['state_slug' => $stateSlug]),
                    $districtName => null
                ];
                break;

            case 'railway':
                $title = "Latest Indian Railway (RRB / NTPC / ALP) Jobs {$year} - GovJobs";
                $h1 = "Indian Railway (RRB) Government Jobs";
                $description = "Get real-time updates on Indian Railways recruitment boards (RRB). Active vacancies for ALP, NTPC, Group D, and technical cadres. Apply online today!";
                $breadcrumbs = ['Railway Jobs' => null];
                break;

            case 'banking':
                $title = "Banking Government Jobs {$year} - Apply for SBI, IBPS, RBI - GovJobs";
                $h1 = "Banking & Finance Government Jobs";
                $description = "Browse live recruitments for public sector banks (SBI, RBI, IBPS, etc.). Active vacancies for Probationary Officers, Clerks, Specialist Officers, and Grade B Officers.";
                $breadcrumbs = ['Banking Jobs' => null];
                break;

            case 'ssc':
                $title = "Latest SSC (Staff Selection Commission) Jobs {$year} - GovJobs";
                $h1 = "SSC Government Jobs & Recruitments";
                $description = "Find active, verified Staff Selection Commission (SSC) job alerts, CGL, CHSL, MTS, and GD constable recruitments. Complete syllabus, exam dates, and salary range details.";
                $breadcrumbs = ['SSC Jobs' => null];
                break;

            case 'upsc':
                $title = "Latest UPSC (Union Public Service Commission) Jobs {$year} - GovJobs";
                $h1 = "UPSC Government Jobs & Civil Services";
                $description = "Browse live Union Public Service Commission (UPSC) recruitment campaigns. Direct alerts for Civil Services IAS, IFS, NDA, CDS, and specialist officers.";
                $breadcrumbs = ['UPSC Jobs' => null];
                break;

            case 'defence':
                $title = "Defence & Police Government Jobs {$year} - Apply Online - GovJobs";
                $h1 = "Defence & Police Government Jobs";
                $description = "Explore active recruitments in Army, Navy, Air Force, and state police organizations. Get eligibility criteria, age limits, physical standards, and apply online.";
                $breadcrumbs = ['Defence Jobs' => null];
                break;

            case 'psu':
                $title = "Latest PSU (Public Sector Undertaking) Jobs {$year} - GovJobs";
                $h1 = "PSU (Public Sector Undertaking) Government Jobs";
                $description = "Find high-paying jobs in Maharatna, Navratna, and Miniratna PSUs (ONGC, NTPC, BHEL, IOCL). Detailed updates on executive trainees and officer recruitments.";
                $breadcrumbs = ['PSU Jobs' => null];
                break;

            case 'results':
                $title = "Sarkari Results {$year} - Latest Exam Selection Lists & Cutoffs - GovJobs";
                $h1 = "Sarkari Exam Results & Merit Lists";
                $description = "Check your Sarkari exam results, qualifying lists, and cutoff marks. Instant updates for UPSC, SSC, RRB, Banking, and State PSC boards.";
                $breadcrumbs = ['Exam Results' => null];
                break;

            case 'admit_cards':
                $title = "Latest Sarkari Admit Cards {$year} - Download Hall Tickets - GovJobs";
                $h1 = "Download Sarkari Admit Cards & Entry Cards";
                $description = "Download your call letters, hall tickets, and exam schedules instantly. Direct download links for all major competitive government examinations.";
                $breadcrumbs = ['Admit Cards' => null];
                break;

            case 'answer_keys':
                $title = "Sarkari Answer Keys {$year} - Download Question Papers - GovJobs";
                $h1 = "Sarkari Answer Keys & Cutoff Keys";
                $description = "Get official exam answer keys, solved question papers, and raise objections. Instant download for UPSC, SSC, Banking, and Railway exams.";
                $breadcrumbs = ['Answer Keys' => null];
                break;

            case 'syllabus':
                $title = "Sarkari Exam Syllabus {$year} - Complete PDF Patterns - GovJobs";
                $h1 = "Government Exam Syllabus & Marking Schemes";
                $description = "Download complete exam syllabus, marking schemes, selection processes, and exam patterns in PDF format. Pre-plan your prep with our comprehensive guides.";
                $breadcrumbs = ['Syllabus Hub' => null];
                break;

            case 'detail':
                $job = $params['job'] ?? null;
                if ($job) {
                    $postTypeName = $this->getPostTypeName($job->post_type);
                    $title = "{$job->title} - Syllabus, Pattern & Salary - GovJobs";
                    $h1 = $job->title;
                    $description = "Get full details for {$job->title}. Salary range: ₹" . number_format($job->salary_min, 0) . " - " . number_format($job->salary_max, 0) . ". Apply before: " . ($job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A') . ".";
                    
                    // Breadcrumbs build
                    if ($job->state && $job->state->code !== 'CENTRAL') {
                        $breadcrumbs['State Jobs'] = route('seo.state');
                        $breadcrumbs[$job->state->name] = route('seo.dynamic_state', ['state_slug' => $job->state->slug]);
                        if ($job->district) {
                            $breadcrumbs[$job->district->name] = route('seo.dynamic_district', ['state_slug' => $job->state->slug, 'district_slug' => $job->district->slug]);
                        }
                    } elseif ($job->category) {
                        $breadcrumbs[$job->category->name] = url("/jobs/{$job->category->slug}");
                    }
                    $breadcrumbs[$postTypeName] = null;
                }
                break;
        }

        return [
            'meta_title' => $title,
            'page_header' => $h1,
            'meta_description' => $description,
            'meta_keywords' => strtolower(str_replace(' - GovJobs', '', $title)) . ', sarkari job, govt recruitment, apply online, eligibility ' . $year,
            'breadcrumbs' => $breadcrumbs
        ];
    }

    /**
     * Generate Schema Markup for a single JobPost.
     *
     * @param \App\Models\JobPost $job
     * @return array
     */
    public function getJobSchema(\App\Models\JobPost $job): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => strip_tags($job->description),
            'datePosted' => $job->published_at ? $job->published_at->toDateString() : now()->toDateString(),
            'validThrough' => $job->last_date_to_apply ? $job->last_date_to_apply->toDateString() : now()->addDays(30)->toDateString(),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $job->department->name ?? 'Government Recruitment Board',
                'sameAs' => $job->official_website_link ?? 'https://upsc.gov.in'
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressRegion' => $job->state->name ?? 'Pan India',
                    'addressCountry' => 'IN'
                ]
            ],
            'baseSalary' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'INR',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => (float)$job->salary_min,
                    'maxValue' => (float)$job->salary_max,
                    'unitText' => 'MONTH'
                ]
            ]
        ];
    }

    /**
     * Helper to return clean post type names.
     */
    protected function getPostTypeName(string $postType): string
    {
        return match ($postType) {
            'result' => 'Results',
            'admit_card' => 'Admit Card',
            'answer_key' => 'Answer Key',
            'syllabus' => 'Syllabus',
            'notice' => 'Notices',
            'admission' => 'Admissions',
            'scholarship' => 'Scholarships',
            default => 'Jobs',
        };
    }
}
