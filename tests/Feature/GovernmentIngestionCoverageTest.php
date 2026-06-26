<?php

namespace Tests\Feature;

use App\Domains\Scrapers\Drivers\HighCourtScraperDriver;
use App\Domains\Scrapers\Drivers\MunicipalScraperDriver;
use App\Domains\Scrapers\Drivers\AcademicScraperDriver;
use App\Domains\Scrapers\Drivers\NaturalResourcesScraperDriver;
use App\Domains\Scrapers\Drivers\PoliceScraperDriver;
use App\Domains\Scrapers\Drivers\ScraperDriverManager;
use App\Models\ScrapingSource;
use Database\Seeders\OfficialGovSourcesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentIngestionCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_official_sources(): void
    {
        // Assert table is empty before seeding
        $this->assertEquals(0, ScrapingSource::count());

        // Run Seeder
        $this->seed(OfficialGovSourcesSeeder::class);

        // Assert items are registered (21 items expected)
        $this->assertGreaterThanOrEqual(20, ScrapingSource::count());

        // Verify specific source attributes
        $source = ScrapingSource::where('name', 'Delhi High Court Opportunities')->first();
        $this->assertNotNull($source);
        $this->assertEquals('high_court', $source->selectors_config['driver']);
        $this->assertEquals('weekly', $source->selectors_config['update_frequency']);
        $this->assertEquals('high', $source->selectors_config['priority']);
    }

    public function test_driver_manager_resolves_all_custom_drivers(): void
    {
        $this->seed(OfficialGovSourcesSeeder::class);
        $manager = app(ScraperDriverManager::class);

        // Test Court
        $court = ScrapingSource::where('name', 'Delhi High Court Opportunities')->first();
        $this->assertInstanceOf(HighCourtScraperDriver::class, $manager->getDriverFor($court));

        // Test Municipal
        $municipal = ScrapingSource::where('name', 'Municipal Corporation of Delhi Careers')->first();
        $this->assertInstanceOf(MunicipalScraperDriver::class, $manager->getDriverFor($municipal));

        // Test Academic
        $academic = ScrapingSource::where('name', 'Delhi University Careers')->first();
        $this->assertInstanceOf(AcademicScraperDriver::class, $manager->getDriverFor($academic));

        // Test Resource
        $resource = ScrapingSource::where('name', 'Coal India Recruitment Ingest')->first();
        $this->assertInstanceOf(NaturalResourcesScraperDriver::class, $manager->getDriverFor($resource));

        // Test Police
        $police = ScrapingSource::where('name', 'UP Police Recruitment Board')->first();
        $this->assertInstanceOf(PoliceScraperDriver::class, $manager->getDriverFor($police));
    }

    public function test_mock_fallback_returns_valid_data(): void
    {
        $this->seed(OfficialGovSourcesSeeder::class);

        $drivers = [
            HighCourtScraperDriver::class => 'Delhi High Court',
            MunicipalScraperDriver::class => 'MCD Assistant Engineer',
            AcademicScraperDriver::class => 'Delhi University',
            NaturalResourcesScraperDriver::class => 'Coal India',
            PoliceScraperDriver::class => 'UP Police',
        ];

        foreach ($drivers as $driverClass => $keyword) {
            $driver = app($driverClass);
            $driverKey = strtolower(preg_replace('/ScraperDriver$/', '', class_basename($driverClass)));
            // Handle underscore converting for natural_resources and high_court
            if ($driverKey === 'naturalresources') {
                $driverKey = 'natural_resources';
            } elseif ($driverKey === 'highcourt') {
                $driverKey = 'high_court';
            }
            
            $source = ScrapingSource::where('selectors_config->driver', $driverKey)->first();
            $this->assertNotNull($source, "Source for driver key '{$driverKey}' not found.");
            
            $parsed = $driver->parse('', $source);
            $this->assertNotEmpty($parsed);
            $this->assertArrayHasKey('title', $parsed[0]);
            $this->assertArrayHasKey('deadline_raw', $parsed[0]);
            $this->assertStringContainsString($keyword, $parsed[0]['title'] . ' ' . $parsed[0]['raw_text']);
        }
    }
}
