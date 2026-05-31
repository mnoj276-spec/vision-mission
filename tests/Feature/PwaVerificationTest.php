<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PwaVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the manifest.json file exists, is valid JSON, and declares appropriate PWA metadata.
     */
    public function test_manifest_file_exists_and_contains_valid_pwa_metadata(): void
    {
        $manifestPath = public_path('manifest.json');
        
        $this->assertTrue(File::exists($manifestPath), 'manifest.json is missing in the public directory.');
        
        $content = File::get($manifestPath);
        $manifest = json_decode($content, true);
        
        $this->assertIsArray($manifest, 'manifest.json does not contain valid JSON.');
        $this->assertEquals('GovJobs Government Recruitment Portal', $manifest['name']);
        $this->assertEquals('GovJobs', $manifest['short_name']);
        $this->assertEquals('/', $manifest['start_url']);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertEquals('portrait', $manifest['orientation']);
        $this->assertEquals('#090d16', $manifest['background_color']);
        
        // Assert launcher icons are defined in manifest
        $this->assertArrayHasKey('icons', $manifest);
        $this->assertNotEmpty($manifest['icons']);
        
        // Ensure standard sizes are covered and contain a maskable purpose
        $has192 = false;
        $has512 = false;
        foreach ($manifest['icons'] as $icon) {
            if ($icon['sizes'] === '192x192') {
                $has192 = true;
                $this->assertStringContainsString('maskable', $icon['purpose']);
            }
            if ($icon['sizes'] === '512x512') {
                $has512 = true;
                $this->assertStringContainsString('maskable', $icon['purpose']);
            }
        }
        
        $this->assertTrue($has192, 'Manifest is missing standard 192x192 icon with maskable purpose.');
        $this->assertTrue($has512, 'Manifest is missing standard 512x512 icon with maskable purpose.');
    }

    /**
     * Test that the service worker sw.js file exists and contains essential lifecycle listeners.
     */
    public function test_service_worker_file_exists_and_declares_pwa_event_listeners(): void
    {
        $swPath = public_path('sw.js');
        
        $this->assertTrue(File::exists($swPath), 'sw.js is missing in the public directory.');
        
        $swContent = File::get($swPath);
        
        // Verify key Service Worker events and logic signatures
        $this->assertStringContainsString('govjobs-pwa-v1', $swContent, 'sw.js cache name mismatch.');
        $this->assertStringContainsString("self.addEventListener('install'", $swContent, 'sw.js is missing install listener.');
        $this->assertStringContainsString("self.addEventListener('activate'", $swContent, 'sw.js is missing activate listener.');
        $this->assertStringContainsString("self.addEventListener('fetch'", $swContent, 'sw.js is missing fetch interceptor.');
        $this->assertStringContainsString("self.addEventListener('push'", $swContent, 'sw.js is missing push notification listener.');
        $this->assertStringContainsString("self.addEventListener('notificationclick'", $swContent, 'sw.js is missing notificationclick handler.');
        $this->assertStringContainsString("self.addEventListener('sync'", $swContent, 'sw.js is missing background sync event listener.');
        
        // Verify offline route cached assets
        $this->assertStringContainsString('/offline', $swContent, 'sw.js is missing /offline fallback cache target.');
        $this->assertStringContainsString('/assets/css/portal.css', $swContent, 'sw.js is missing portal stylesheet precache.');
        
        // Verify offline alert synchronization configuration
        $this->assertStringContainsString('sync-subscriptions', $swContent, 'sw.js background sync tag mismatch.');
        $this->assertStringContainsString('govjobs_offline_db', $swContent, 'sw.js offline IndexedDB database name mismatch.');
        $this->assertStringContainsString('/api/growth/subscribe', $swContent, 'sw.js offline sync route target mismatch.');
    }

    /**
     * Test that the /offline fallback view renders correctly under Laravel router.
     */
    public function test_offline_fallback_page_renders_with_diagnostics(): void
    {
        $response = $this->get('/offline');
        
        $response->assertStatus(200);
        $response->assertSee('Connection Interrupted');
        $response->assertSee('PWA Core Standby Diagnostics');
        $response->assertSee('You are Offline');
        $response->assertSee('Simulate Connection Retry');
        $response->assertSee('govjobs_offline_db');
    }

    /**
     * Test that the app layout loads manifest configuration, apple meta headers, and sw registration.
     */
    public function test_app_layout_registers_pwa_bootloader_and_meta_tags(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Assert HTML head contains link to manifest and mobile app tags
        $response->assertSee('<link rel="manifest" href="/manifest.json">', false);
        $response->assertSee('<meta name="apple-mobile-web-app-capable" content="yes">', false);
        $response->assertSee('<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">', false);
        $response->assertSee('<meta name="apple-mobile-web-app-title" content="GovJobs">', false);
        $response->assertSee('<link rel="apple-touch-icon" href="/assets/images/icons/pwa-icon-192.png">', false);
        $response->assertSee('<meta name="theme-color" content="#2563eb">', false);
        
        // Assert service worker registration bootloader is embedded
        $response->assertSee("navigator.serviceWorker.register('/sw.js')", false);
        $response->assertSee('pwaInstallBanner', false);
        $response->assertSee('govjobs_offline_db', false);
    }

    /**
     * Test that the launcher icons are present in the public folder and have valid file attributes.
     */
    public function test_pwa_launcher_icons_are_fully_generated_and_sized(): void
    {
        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];
        
        foreach ($sizes as $size) {
            $path = public_path("assets/images/icons/pwa-icon-{$size}.png");
            $this->assertTrue(File::exists($path), "Launcher icon pwa-icon-{$size}.png is missing.");
            $this->assertGreaterThan(0, File::size($path), "Launcher icon pwa-icon-{$size}.png has empty contents.");
        }
    }
}
