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
        if ($this->isPrivateIp($ip)) {
            return ['city' => 'Local', 'country' => null];
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
        }

        return ['city' => 'Unknown', 'country' => null];
    }

    private function isPrivateIp(string $ip): bool
    {
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
