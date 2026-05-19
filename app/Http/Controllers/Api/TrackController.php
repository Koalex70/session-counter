<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Services\UserAgentParser;
use App\Services\VisitGeoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TrackController extends Controller
{
    public function __construct(
        private readonly VisitGeoService $geoService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'page_url' => ['required', 'url', 'max:2048'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'screen_width' => ['nullable', 'integer', 'min:0'],
            'screen_height' => ['nullable', 'integer', 'min:0'],
            'language' => ['nullable', 'string', 'max:32'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'user_agent' => ['nullable', 'string', 'max:512'],
        ]);

        $visitorId = $request->cookie('visitor_id') ?? (string) Str::uuid();
        $ip = $this->geoService->clientIp($request);
        $geo = $this->geoService->resolve($ip);
        $userAgent = $request->userAgent() ?? $validated['user_agent'] ?? '';
        $device = UserAgentParser::parse($userAgent);

        Visit::create([
            'visitor_id' => $visitorId,
            'ip_address' => $ip,
            'city' => $geo['city'],
            'country' => $geo['country'],
            'device_type' => $device['device_type'],
            'browser' => $device['browser'],
            'os' => $device['os'],
            'page_url' => $validated['page_url'],
            'referrer' => $validated['referrer'] ?? null,
            'user_agent' => $userAgent,
            'visited_at' => now(),
        ]);

        return response()
            ->noContent()
            ->cookie(
                'visitor_id',
                $visitorId,
                60 * 24 * 365,
                '/',
                null,
                (bool) config('tracker.cookie_secure'),
                false,
                false,
                config('tracker.cookie_samesite', 'lax')
            );
    }
}
