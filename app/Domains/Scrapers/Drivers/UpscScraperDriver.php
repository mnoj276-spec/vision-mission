<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class UpscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'upsc.gov') || 
               str_contains($url, 'upsc.nic') || 
               str_contains($name, 'upsc') || 
               $driver === 'upsc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'UPSC Civil Services IAS Preliminary Exam 2026',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 100',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsconline.nic.in',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'UPSC Civil Services Examination 2026. IAS, IPS, IFS recruitment. Required Graduate Degree. Last date: 15-08-2026. Fee Rs 100.',
                    ],
                    [
                        'title'         => 'UPSC Combined Defence Services Exam 2026',
                        'deadline_raw'  => '10-11-2026',
                        'fee_raw'       => 'Rs 200',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsconline.nic.in',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'UPSC Combined Defence Services Examination II 2026. Graduate required. Last date: 10-11-2026. Fee Rs 200.',
                    ],
                    [
                        'title'         => 'UPSC Civil Services Written Examination Results 2026',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsc.gov.in/results',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Union Public Service Commission civil services mains exam written results are out. Candidates list of qualified officers published.',
                    ],
                    [
                        'title'         => 'UPSC Geoscientist Exam Admit Card Download 2026',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsconline.nic.in/admit-card',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Download e-admit card for UPSC Geoscientist combined exam. Hall tickets and call letters are available on online portal.',
                    ],
                    [
                        'title'         => 'UPSC Engineering Services Preliminary Key Answers 2026',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsc.gov.in/answer-keys',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Provisional answer key and response sheets for Engineering Services exam 2026 released. Raise objections online before deadline.',
                    ],
                    [
                        'title'         => 'UPSC Civil Services Mains Syllabus and Exam Pattern',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsc.gov.in/syllabus',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Syllabus and scheme of examination for IAS mains exam. Complete curriculum and selection procedure detail available.',
                    ],
                    [
                        'title'         => 'UPSC Combined Medical Services Exam Admission Notice',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsc.gov.in/admissions',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Entrance examination and counselling schedule for medical services seat allotment and admissions portal updates.',
                    ],
                    [
                        'title'         => 'UPSC IFS Fellowship Research Grant Scheme 2026',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsc.gov.in/scholarships',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Scholarship notification for forest service fellowship. Financial assistance and research grant allocations details.',
                    ],
                    [
                        'title'         => 'UPSC Civil Services Corrigendum Date Extension Notice',
                        'deadline_raw'  => '15-08-2026',
                        'fee_raw'       => 'Rs 0',
                        'official_link' => 'https://upsc.gov.in',
                        'apply_link'    => 'https://upsc.gov.in/notices',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Union Public Service Commission',
                        'raw_text'      => 'Important circular: corrigendum notice and application date extension guidelines for civil services recruitment 2026.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("UPSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
