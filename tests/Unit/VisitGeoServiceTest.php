<?php

namespace Tests\Unit;

use App\Services\VisitGeoService;
use Illuminate\Http\Request;
use Tests\TestCase;

class VisitGeoServiceTest extends TestCase
{
    public function test_resolves_city_for_public_ip(): void
    {
        $geo = (new VisitGeoService)->resolve('8.8.8.8');

        $this->assertNotSame('Unknown', $geo['city']);
        $this->assertNotEmpty($geo['city']);
    }

    public function test_uses_forwarded_ip_from_request(): void
    {
        $request = Request::create('/api/track', 'POST', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '8.8.8.8',
        ]);

        $ip = (new VisitGeoService)->clientIp($request);

        $this->assertSame('8.8.8.8', $ip);
    }
}
