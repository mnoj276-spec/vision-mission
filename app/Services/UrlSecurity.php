<?php

namespace App\Services;

class UrlSecurity
{
    /**
     * Validate the given URL to prevent SSRF and restrict domains.
     * Only allows .gov.in, .nic.in and approved whitelist domains.
     * Blocks private/reserved/loopback IP address and hostnames.
     *
     * @param string|null $url
     * @return bool
     */
    public static function isSafeUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Validate URL structure
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);

        // Prevent SSRF: Block loopback and local hostnames
        if ($host === 'localhost' || $host === 'loopback' || $host === '127.0.0.1') {
            return false;
        }

        // Prevent SSRF: Block local IP addresses (RFC1918, Link-local, etc.)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // Rejects all IP addresses in the scraping context to prevent SSRF
            return false;
        }

        // Only allow .gov.in, .nic.in, or approved domains
        if ($host === 'gov.in' || $host === 'nic.in' || str_ends_with($host, '.gov.in') || str_ends_with($host, '.nic.in')) {
            return true;
        }

        // Approved domains list (includes generative AI, test targets, etc.)
        $approvedDomains = [
            'generativelanguage.googleapis.com',
            'api.openai.com',
            'test-upsc-portal.gov.in',
            'ssc.gov.in',
            'upsc.gov.in',
            'upsconline.nic.in',
            'github.com',
        ];

        if (in_array($host, $approvedDomains)) {
            return true;
        }

        return false;
    }
}
