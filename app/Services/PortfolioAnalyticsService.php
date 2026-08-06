<?php

namespace App\Services;

use App\Models\PortfolioAnalytic;
use App\Models\PortfolioPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class PortfolioAnalyticsService
{
    public function record(
        PortfolioPublication $publication,
        Request $request,
        string $eventType,
        ?string $target = null,
        array $metadata = []
    ): void {

        $visitorId =
            $request->cookie('portfolio_visitor_id')
            ?? Str::uuid()->toString();

            $agent = new Agent();

$agent->setUserAgent(
    $request->userAgent()
);

        PortfolioAnalytic::create([
    'portfolio_publication_id' =>
        $publication->id,

    'user_id' =>
        $publication->user_id,

    'event_type' =>
        $eventType,

    'event_target' =>
        $target,

    'visitor_id' =>
        $visitorId,

    'ip_address' =>
        $request->ip(),

    'user_agent' =>
        $request->userAgent(),

    'device_type' =>
        $agent->isDesktop()
            ? 'Desktop'
            : (
                $agent->isTablet()
                    ? 'Tablet'
                    : 'Mobile'
            ),

    'browser' =>
        $agent->browser() ?: 'Unknown',

    'operating_system' =>
        $agent->platform() ?: 'Unknown',

    'country' =>
        $this->detectCountry($request),

    'referrer' =>
        $request->headers->get('referer'),

    'metadata' =>
        $metadata,
]);
    }
    private function detectCountry(Request $request): string
{
    $countryCode =
        $request->header('CF-IPCountry')
        ?? $request->header('X-Country-Code')
        ?? $request->header('X-AppEngine-Country');

    if (
        !$countryCode ||
        strtoupper($countryCode) === 'XX'
    ) {
        return 'Unknown';
    }

    return strtoupper($countryCode);
}
}