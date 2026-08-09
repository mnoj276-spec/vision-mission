<?php

namespace Tests\Feature;

use App\Domains\Scrapers\Enums\NotificationType;
use App\Domains\Scrapers\Services\NotificationClassifier;
use Tests\TestCase;

class NotificationClassifierTest extends TestCase
{
    protected NotificationClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = app(NotificationClassifier::class);
    }

    public function test_regex_classification_rules(): void
    {
        // 1. Admit Card
        $this->assertEquals(
            NotificationType::ADMIT_CARD->value,
            $this->classifier->classify('UPSC CSE Prelims Admit Card Download 2026')
        );

        // 2. Final Answer Key
        $this->assertEquals(
            NotificationType::FINAL_ANSWER_KEY->value,
            $this->classifier->classify('SSC CGL Final Answer Key Solutions')
        );

        // 3. Document Verification
        $this->assertEquals(
            NotificationType::DV->value,
            $this->classifier->classify('RRB ALP Document Verification Schedule')
        );

        // 4. Walk-in Interview
        $this->assertEquals(
            NotificationType::WALK_IN->value,
            $this->classifier->classify('Walk-in Interview for Assistant Professor DU')
        );

        // 5. Sports Quota
        $this->assertEquals(
            NotificationType::SPORTS_QUOTA->value,
            $this->classifier->classify('Indian Railways Outstanding Sportsperson Vacancy')
        );

        // 6. EWS Entry
        $this->assertEquals(
            NotificationType::EWS->value,
            $this->classifier->classify('Economically Weaker Section Special Recruitment Drive')
        );

        // 7. Promotion
        $this->assertEquals(
            NotificationType::PROMOTION->value,
            $this->classifier->classify('Departmental Promotion LDCE Exam Notification')
        );
    }

    public function test_new_categories_classification(): void
    {
        // 1. Syllabus
        $this->assertEquals(
            NotificationType::SYLLABUS->value,
            $this->classifier->classify('UPSC CSE Civil Services Syllabus & Scheme of Exam')
        );

        // 2. Scholarship
        $this->assertEquals(
            NotificationType::SCHOLARSHIP->value,
            $this->classifier->classify('National Merit Scholarship Notification Fellowship 2026')
        );

        // 3. Admission
        $this->assertEquals(
            NotificationType::ADMISSION->value,
            $this->classifier->classify('IIT Admissions Counseling Seat Allotment Schedule')
        );

        // 4. Exam Notice
        $this->assertEquals(
            NotificationType::EXAM_NOTICE->value,
            $this->classifier->classify('SSC CGL Written Exam CBT Date Schedule Notice')
        );
    }

    public function test_safety_fallback_on_unknown_content(): void
    {
        // Random irrelevant title should return unknown to prevent false positives
        $this->assertEquals(
            NotificationType::UNKNOWN->value,
            $this->classifier->classify('Some completely random text about office cleaning schedule')
        );
    }

    public function test_scraping_service_post_type_resolver(): void
    {
        $scrapingService = app(\App\Domains\Scrapers\Services\ScrapingService::class);

        // Test that admit card maps correctly
        $this->assertEquals(
            'admit_card',
            $scrapingService->classifyPostType('UPSC Admit Card 2026', '')
        );

        // Test that normal recruitment maps to 'job' (backward compatibility check)
        $this->assertEquals(
            'job',
            $scrapingService->classifyPostType('UPSC Assistant Commissioner Recruitment 2026', '')
        );

        // Test that completely unknown title maps back to 'job' default
        $this->assertEquals(
            'job',
            $scrapingService->classifyPostType('Irrelevant title text', '')
        );

        // Test new category mappings
        $this->assertEquals(
            'syllabus',
            $scrapingService->classifyPostType('UPSC Civil Services Syllabus 2026', '')
        );
        $this->assertEquals(
            'scholarship',
            $scrapingService->classifyPostType('Fellowship stipend details', '')
        );
        $this->assertEquals(
            'admission',
            $scrapingService->classifyPostType('IIT Counselling updates', '')
        );
        $this->assertEquals(
            'notice',
            $scrapingService->classifyPostType('SSC CBT Exam Date Schedule', '')
        );
    }
}
