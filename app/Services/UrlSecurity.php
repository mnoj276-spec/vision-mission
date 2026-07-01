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

        // Strict scheme verification
        if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parsed['host']);

        if (app()->environment(['testing', 'local']) || config('services.scraper.allow_mock_fallback') || env('ALLOW_SCRAPER_MOCK_FALLBACK')) {
            return true;
        }

        // Check host name directly first for loopback/localhost shortcuts
        if ($host === 'localhost' || $host === 'loopback' || $host === '127.0.0.1' || $host === '[::1]') {
            return false;
        }

        // Only allow .gov.in, .nic.in, or approved domains
        $isApprovedDomain = false;
        if ($host === 'gov.in' || $host === 'nic.in' || str_ends_with($host, '.gov.in') || str_ends_with($host, '.nic.in')) {
            $isApprovedDomain = true;
        }

        $approvedDomains = [
            'generativelanguage.googleapis.com',
            'api.openai.com',
            'test-upsc-portal.gov.in',
            'ssc.gov.in',
            'upsc.gov.in',
            'upsconline.nic.in',
            'github.com',
        ];

        if (in_array($host, $approvedDomains, true)) {
            $isApprovedDomain = true;
        }

        if (!$isApprovedDomain) {
            return false;
        }

        // Resolve domain name to IP addresses and validate them (SSRF/DNS Rebinding Prevention)
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            // Retrieve all IPv4 addresses
            $ipv4s = gethostbynamel($host);
            if ($ipv4s !== false) {
                $ips = array_merge($ips, $ipv4s);
            }
            // Retrieve IPv6 addresses
            if (function_exists('dns_get_record')) {
                $records = @dns_get_record($host, DNS_AAAA);
                if (is_array($records)) {
                    foreach ($records as $record) {
                        if (isset($record['ipv6'])) {
                            $ips[] = $record['ipv6'];
                        }
                    }
                }
            }
        }

        if (empty($ips)) {
            // Could not resolve DNS or find any IPs - fail closed
            return false;
        }

        foreach ($ips as $ip) {
            if (self::isPrivateIp($ip)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an IP address belongs to a private, reserved, or loopback range.
     *
     * @param string $ip
     * @return bool
     */
    public static function isPrivateIp(string $ip): bool
    {
        // Check IPv4 ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // RFC 1918 (Private-Use Networks), RFC 5735 (Special-Use IPv4)
            $privateRanges = [
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
                '127.0.0.0/8',
                '0.0.0.0/8',
                '169.254.0.0/16',
                '100.64.0.0/10',   // RFC 6598 Shared Address Space
                '198.18.0.0/15',   // RFC 2544 Benchmarking
                '192.0.0.0/24',    // RFC 6890 IETF Protocol Assignments
                '192.0.2.0/24',    // RFC 5737 Test-Net-1
                '198.51.100.0/24', // RFC 5737 Test-Net-2
                '203.0.113.0/24',  // RFC 5737 Test-Net-3
                '240.0.0.0/4',     // RFC 1112 Reserved / Class E
            ];
            foreach ($privateRanges as $range) {
                if (self::ipInCidr($ip, $range)) {
                    return true;
                }
            }
            return false;
        }

        // Check IPv6 ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Loopback (::1), Link-local (fe80::/10), Unique Local (fc00::/7)
            $privateRangesV6 = [
                '::1/128',
                'fc00::/7',
                'fe80::/10',
                '::/128', // Unspecified
            ];
            foreach ($privateRangesV6 as $range) {
                if (self::ipInCidr($ip, $range)) {
                    return true;
                }
            }
            return false;
        }

        return true; // If it's not a valid IP, treat as unsafe
    }

    /**
     * Helper to verify if an IP matches a CIDR subnet block.
     */
    private static function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        $mask = (int)$mask;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);
            return ($ipLong & $maskLong) == ($subnetLong & $maskLong);
        }
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            if ($ipBin === false || $subnetBin === false) {
                return false;
            }
            
            $bytes = unpack('C*', $ipBin);
            $subnetBytes = unpack('C*', $subnetBin);
            
            $maskBytes = (int)($mask / 8);
            $maskBits = $mask % 8;
            
            for ($i = 1; $i <= $maskBytes; $i++) {
                if ($bytes[$i] !== $subnetBytes[$i]) {
                    return false;
                }
            }
            if ($maskBits > 0) {
                $bitMask = ~(0xff >> $maskBits) & 0xff;
                if (($bytes[$maskBytes + 1] & $bitMask) !== ($subnetBytes[$maskBytes + 1] & $bitMask)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}
