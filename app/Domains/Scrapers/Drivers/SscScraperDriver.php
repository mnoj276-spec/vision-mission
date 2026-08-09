<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class SscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'ssc.gov') || 
               str_contains($url, 'ssc.nic') || 
               str_contains($name, 'ssc') || 
               $driver === 'ssc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                // High-quality domain realistic simulation
                return [
                    [
                        'title'         => 'SSC CGL Tier 1 Combined Graduate Level Recruitment 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 100',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/apply',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Staff Selection Commission (SSC) Combined Graduate Level (CGL) Examination 2026. Vacancy Details: Inspector - 120, Assistant Section Officer - 80, Auditor - 45. Caste Breakdown: UR - 100, OBC - 80, SC - 40, ST - 25. Age: 18-30. Qualification: Bachelor degree. Apply online before 30-07-2026. Application Fee Rs 100.',
                    ],
                    [
                        'title'         => 'SSC CHSL (10+2) Vacancy 2026 Registration Open',
                        'deadline_raw'  => '12-08-2026',
                        'fee_raw'       => 'Rs 100',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/apply',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'SSC CHSL 10+2 Examination 2026. Vacancy Details: Lower Division Clerk - 90, Data Entry Operator - 40. Caste Breakdown: UR - 60, OBC - 40, SC - 20, ST - 10. Qualification: 12th pass. Apply before 12-08-2026. Fee Rs 100.',
                    ],
                    [
                        'title'         => 'SSC CGL Tier 1 Computer Based Exam Written Result 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/results',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'SSC CGL Tier-1 written exam results out. Merit list of shortlisted candidates qualified for Tier-2 examination.',
                    ],
                    [
                        'title'         => 'SSC GD Constable Exam Admit Card Download 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/admit-card',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Download e-admit card and hall ticket for SSC GD Constable physical test PET/PST call letter issued.',
                    ],
                    [
                        'title'         => 'SSC MTS OMR Response Sheet Provisional Answer Key 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/answer-keys',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Provisional key answers and response sheet for Multi-Tasking Staff exam published. Raise objections online.',
                    ],
                    [
                        'title'         => 'SSC CGL Syllabus and Scheme of Examination 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/syllabus',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Detailed curriculum and exam pattern for CGL Tier-2 descriptive paper and selection procedure stages.',
                    ],
                    [
                        'title'         => 'SSC Scientific Assistant Counseling Admission Schedule 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/admissions',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Counselling and seat allotment prospectus for meteorological assistant entry. Online allotment schedule.',
                    ],
                    [
                        'title'         => 'SSC Board Student Stipend Fellowship Scheme 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/scholarships',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Scholarship notification for meritorious candidates. Financial assistance and student grant stipend details.',
                    ],
                    [
                        'title'         => 'SSC CGL Recruitment Postponement and Cancellation Notice',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/notices',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Important circular: corrigendum notice, exam postponement, and revised schedules updates for CGL 2026.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
