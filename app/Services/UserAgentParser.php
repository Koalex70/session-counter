<?php

namespace App\Services;

class UserAgentParser
{
    /**
     * @return array{device_type: string, browser: string|null, os: string|null}
     */
    public static function parse(?string $userAgent): array
    {
        $ua = $userAgent ?? '';

        return [
            'device_type' => self::detectDeviceType($ua),
            'browser' => self::detectBrowser($ua),
            'os' => self::detectOs($ua),
        ];
    }

    private static function detectDeviceType(string $ua): string
    {
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function detectBrowser(string $ua): ?string
    {
        $patterns = [
            'Edge' => '/Edg\//i',
            'Opera' => '/OPR\//i',
            'Chrome' => '/Chrome\//i',
            'Firefox' => '/Firefox\//i',
            'Safari' => '/Safari\//i',
            'IE' => '/MSIE|Trident/i',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return null;
    }

    private static function detectOs(string $ua): ?string
    {
        $patterns = [
            'Windows' => '/Windows NT/i',
            'macOS' => '/Mac OS X/i',
            'iOS' => '/iPhone|iPad|iPod/i',
            'Android' => '/Android/i',
            'Linux' => '/Linux/i',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return null;
    }
}
