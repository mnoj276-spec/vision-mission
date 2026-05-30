<?php

namespace Tests\Feature;

use App\Domains\Scrapers\Drivers\BankingScraperDriver;
use App\Domains\Scrapers\Drivers\DefaultHtmlScraperDriver;
use App\Domains\Scrapers\Drivers\DefenceScraperDriver;
use App\Domains\Scrapers\Drivers\PsuScraperDriver;
use App\Domains\Scrapers\Drivers\RailwayScraperDriver;
use App\Domains\Scrapers\Drivers\ScraperDriverManager;
use App\Domains\Scrapers\Drivers\SscScraperDriver;
use App\Domains\Scrapers\Drivers\StatePscScraperDriver;
use App\Domains\Scrapers\Drivers\UpscScraperDriver;
use App\Models\ScrapingSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScraperDriverManagerTest extends TestCase
{
    use RefreshDatabase;

    protected ScraperDriverManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(ScraperDriverManager::class);
    }

    /**
     * Test auto-detection and resolution of correct drivers based on URL/name keywords.
     */
    public function test_driver_manager_auto_detects_appropriate_drivers(): void
    {
        $upscSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://upsc.gov.in/feed',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(UpscScraperDriver::class, $this->manager->getDriverFor($upscSource));

        $sscSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://ssc.gov.in/active',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(SscScraperDriver::class, $this->manager->getDriverFor($sscSource));

        $rrbSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://rrbapply.gov.in/feed',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(RailwayScraperDriver::class, $this->manager->getDriverFor($rrbSource));

        $bankingSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://sbi.co.in/careers',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(BankingScraperDriver::class, $this->manager->getDriverFor($bankingSource));

        $psuSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://ntpccareers.net/feed',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(PsuScraperDriver::class, $this->manager->getDriverFor($psuSource));

        $statePscSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://gpsc.goa.gov.in/listings',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(StatePscScraperDriver::class, $this->manager->getDriverFor($statePscSource));

        $defenceSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://joinindianarmy.nic.in/feed',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(DefenceScraperDriver::class, $this->manager->getDriverFor($defenceSource));

        $fallbackSource = ScrapingSource::make([
            'name' => 'Gov Recruitment Board',
            'source_url' => 'https://some-random-govt-feed.gov.in/active',
            'selectors_config' => []
        ]);
        $this->assertInstanceOf(DefaultHtmlScraperDriver::class, $this->manager->getDriverFor($fallbackSource));
    }

    /**
     * Test that an explicit config setting override gets resolved regardless of URL keywords.
     */
    public function test_driver_manager_respects_explicit_driver_config_override(): void
    {
        $source = ScrapingSource::make([
            'name' => 'UPSC Feed',
            'source_url' => 'https://upsc.gov.in/feed',
            'selectors_config' => ['driver' => 'ssc'] // UPSC url but explicitly forced to SSC!
        ]);

        $driver = $this->manager->getDriverFor($source);
        $this->assertInstanceOf(SscScraperDriver::class, $driver);
    }

    /**
     * Verify that all concrete drivers return correct structures and realistic fallbacks.
     */
    public function test_concrete_drivers_return_realistic_fallbacks(): void
    {
        $source = ScrapingSource::make([
            'name' => 'Feed',
            'source_url' => 'https://generic-feed.gov.in',
            'selectors_config' => []
        ]);

        $drivers = [
            SscScraperDriver::class      => 'SSC',
            UpscScraperDriver::class     => 'UPSC',
            RailwayScraperDriver::class  => 'Railway',
            BankingScraperDriver::class  => 'RBI',
            PsuScraperDriver::class      => 'NTPC',
            StatePscScraperDriver::class => 'Goa PSC',
            DefenceScraperDriver::class  => 'Indian Army',
        ];

        foreach ($drivers as $driverClass => $keyword) {
            $driver = app($driverClass);
            $parsed = $driver->parse('', $source);

            $this->assertNotEmpty($parsed);
            $this->assertArrayHasKey('title', $parsed[0]);
            $this->assertArrayHasKey('deadline_raw', $parsed[0]);
            $this->assertArrayHasKey('raw_text', $parsed[0]);
            $this->assertStringContainsString($keyword, $parsed[0]['title'] . ' ' . $parsed[0]['raw_text']);
        }
    }
}
