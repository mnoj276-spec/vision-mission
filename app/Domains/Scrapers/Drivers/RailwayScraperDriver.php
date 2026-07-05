<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class RailwayScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'railway') || 
               str_contains($url, 'rrb') || 
               str_contains($name, 'railway') || 
               str_contains($name, 'rrb') || 
               $driver === 'railway';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'Railway RRB ALP Assistant Loco Pilot Recruitment 2026',
                        'deadline_raw'  => '28-09-2026',
                        'fee_raw'       => 'Rs 500',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Railway Recruitment Board (RRB) Assistant Loco Pilot (ALP) Ingestion 2026. Vacancy Details: Assistant Loco Pilot - 120, Junior Engineer - 80, Clerk - 45. Caste Breakdown: UR - 100, OBC - 80, SC - 40, ST - 25. Age: 18-30. Required 10th pass or ITI. Apply online by 28-09-2026. Application Fee Rs 500.',
                    ],
                    [
                        'title'         => 'Railway RRB NTPC Non-Technical Popular Categories 2026',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 500',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Railway RRB NTPC recruitment for Under Graduate & Graduate posts. Vacancy Details: Station Master - 150, Goods Guard - 95. Caste Breakdown: UR - 120, OBC - 80, SC - 30, ST - 15. Apply before 15-10-2026. Fee Rs 500.',
                    ],
                    [
                        'title'         => 'Railway RRB NTPC Phase 1 CBT Exam Result 2026',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/results',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Written exam results of computer based test out. Candidates shortlisted for typing test and document verification.',
                    ],
                    [
                        'title'         => 'Railway RRB Group D Written Exam Admit Card 2026',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/admit-card',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Download hall ticket and call letter for physical efficiency test. Group D e-admit cards live now.',
                    ],
                    [
                        'title'         => 'Railway RRB ALP CBT 2 Final Answer Key 2026',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/answer-keys',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Final answer keys and objection decision report for ALP technical trades computer test.',
                    ],
                    [
                        'title'         => 'Railway RRB Junior Engineer Syllabus and Exam Pattern',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/syllabus',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Syllabus and scheme of examination for Junior Engineer technical section. Curriculum information.',
                    ],
                    [
                        'title'         => 'Railway Apprentice Training Admission Counseling Schedule',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/admissions',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Seat allotment and admissions counseling schedule for ITI apprentices seat allotment prospectus.',
                    ],
                    [
                        'title'         => 'Railway Board Employee Children Scholarship Grant 2026',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/scholarships',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Fellowship scheme and student stipend details. Financial assistance grant application details.',
                    ],
                    [
                        'title'         => 'Railway RRB NTPC Exam Schedule Postponement Notice',
                        'deadline_raw'  => '15-10-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://indianrailways.gov.in',
                        'apply_link'    => 'https://rrbapply.gov.in/notices',
                        'category_name' => 'Railways (RRB)',
                        'department_name'=> 'Railway Recruitment Board',
                        'raw_text'      => 'Important circular: corrigendum notice and date extension announcement for computer examination.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Railway scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
