<?php

namespace App\Services;

use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

class VisitGeoService
{
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

        try {
            $position = Location::get($ip);

            if ($position instanceof Position) {
                return [
                    'city' => $position->cityName ?: 'Unknown',
                    'country' => $position->countryName,
                ];
            }
        } catch (\Throwable) {
            // GeoIP lookup failed — use fallback below.
        }

        return ['city' => 'Unknown', 'country' => null];
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
