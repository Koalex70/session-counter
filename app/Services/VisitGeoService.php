<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

class VisitGeoService
{
    /**
     * @return array{city: string, country: string|null}
     */
    public function resolveFromRequest(Request $request): array
    {
        return $this->resolve($this->clientIp($request));
    }

    /**
     * @return array{city: string, country: string|null}
     */
    public function resolve(string $ip): array
    {
        $ip = trim($ip);

        if ($this->isPrivateIp($ip)) {
            if (config('location.testing.enabled')) {
                $ip = (string) config('location.testing.ip', '8.8.8.8');
            } else {
                return ['city' => 'Unknown', 'country' => null];
            }
        }

        return $this->lookupIpWhois($ip)
            ?? $this->lookupLocationPackage($ip)
            ?? ['city' => 'Unknown', 'country' => null];
    }

    public function clientIp(Request $request): string
    {
        $candidates = array_filter([
            $request->header('CF-Connecting-IP'),
            $request->header('True-Client-IP'),
            $request->header('X-Real-IP'),
            ...$this->forwardedIps($request->header('X-Forwarded-For')),
            $request->ip(),
            $request->server('REMOTE_ADDR'),
        ]);

        foreach ($candidates as $ip) {
            $ip = trim($ip);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) && ! $this->isPrivateIp($ip)) {
                return $ip;
            }
        }

        foreach ($candidates as $ip) {
            $ip = trim($ip);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    /**
     * @return array{city: string, country: string|null}|null
     */
    private function lookupIpWhois(string $ip): ?array
    {
        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->get("https://ipwho.is/{$ip}");

            if (! $response->successful() || ! $response->json('success')) {
                return null;
            }

            $city = $response->json('city');
            $country = $response->json('country');

            if (! is_string($city) || $city === '') {
                return null;
            }

            return [
                'city' => $city,
                'country' => is_string($country) ? $country : null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{city: string, country: string|null}|null
     */
    private function lookupLocationPackage(string $ip): ?array
    {
        try {
            $position = Location::get($ip);

            if ($position instanceof Position && $position->cityName) {
                return [
                    'city' => $position->cityName,
                    'country' => $position->countryName,
                ];
            }
        } catch (\Throwable) {
            // Fall through to Unknown.
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function forwardedIps(?string $header): array
    {
        if ($header === null || $header === '') {
            return [];
        }

        return array_map('trim', explode(',', $header));
    }

    private function isPrivateIp(string $ip): bool
    {
        if ($ip === '' || $ip === '0.0.0.0') {
            return true;
        }

        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
