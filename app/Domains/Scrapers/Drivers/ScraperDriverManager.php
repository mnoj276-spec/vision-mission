<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class ScraperDriverManager
{
    /**
     * Active scraper drivers.
     *
     * @var string[]
     */
    protected array $drivers = [
        SscScraperDriver::class,
        UpscScraperDriver::class,
        RailwayScraperDriver::class,
        BankingScraperDriver::class,
        PsuScraperDriver::class,
        StatePscScraperDriver::class,
        DefenceScraperDriver::class,
    ];

    /**
     * Fallback driver when no custom driver matches.
     */
    protected string $fallbackDriver = DefaultHtmlScraperDriver::class;

    /**
     * Resolve the appropriate ScraperDriverInterface instance for the given source.
     */
    public function getDriverFor(ScrapingSource $source): ScraperDriverInterface
    {
        // 1. Check if a driver is explicitly configured in selectors_config
        $explicitDriver = $source->selectors_config['driver'] ?? null;
        if ($explicitDriver) {
            foreach ($this->drivers as $driverClass) {
                $driver = app($driverClass);
                if (strtolower(class_basename($driverClass)) === strtolower($explicitDriver . 'ScraperDriver')) {
                    return $driver;
                }
            }
        }

        // 2. Iterate through registered drivers and check supports()
        foreach ($this->drivers as $driverClass) {
            $driver = app($driverClass);
            if ($driver->supports($source)) {
                return $driver;
            }
        }

        // 3. Return fallback
        return app($this->fallbackDriver);
    }

    /**
     * Register a new custom scraper driver.
     */
    public function registerDriver(string $driverClass): void
    {
        if (is_subclass_of($driverClass, ScraperDriverInterface::class)) {
            $this->drivers[] = $driverClass;
        }
    }
}
