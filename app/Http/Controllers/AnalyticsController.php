<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPublication;
use App\Models\Project;
use App\Services\PackageAccessService;
use App\Services\PortfolioAnalyticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class AnalyticsController extends Controller
{
    private const TRACKABLE_EVENTS = [
        'portfolio_view',
        'project_click',
        'resume_view',
        'resume_download',
        'social_link_click',
        'certificate_view',
        'email_click',
        'phone_click',
        'whatsapp_click',
    ];

    public function __construct(
        private readonly PortfolioAnalyticsService $analyticsService,
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function summary(): JsonResponse
    {
        $user = auth()->user();

        if (
            !$this->packageAccessService
                ->canAccessAnalytics($user)
        ) {
            return response()->json(
                $this->packageAccessService
                    ->upgradeResponse(
                        'Portfolio analytics are available from Gold.',
                        'Gold'
                    ),
                403
            );
        }

        $publication = $user->portfolioPublication;

        if (!$publication) {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $this->emptySummary(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $this->buildSummary(
                    $publication->analytics()
                ),
            ],
        ]);
    }

    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => [
                'required',
                'string',
                'max:100',
            ],
            'event_type' => [
                'required',
                'string',
                Rule::in(self::TRACKABLE_EVENTS),
            ],
            'event_target' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $publication = PortfolioPublication::query()
            ->where('slug', $validated['slug'])
            ->where('is_published', true)
            ->first();

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Published portfolio not found.',
            ], 404);
        }

        $this->analyticsService->record(
            $publication,
            $request,
            $validated['event_type'],
            $validated['event_target'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Analytics event recorded.',
        ]);
    }

    public function details(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (
            !$this->packageAccessService
                ->canAccessAnalytics($user)
        ) {
            return response()->json(
                $this->packageAccessService
                    ->upgradeResponse(
                        'Portfolio analytics are available from Gold.',
                        'Gold'
                    ),
                403
            );
        }

        $validated = $request->validate([
            'period' => [
                'nullable',
                Rule::in([
                    '7-days',
                    '30-days',
                    '90-days',
                ]),
            ],
        ]);

        $period = $validated['period'] ?? '30-days';

        $requestedDays = match ($period) {
            '7-days' => 7,
            '90-days' => 90,
            default => 30,
        };

        $allowedHistory =
            $this->packageAccessService
                ->analyticsHistoryDays($user);

        if ($requestedDays > $allowedHistory) {
            return response()->json(
                $this->packageAccessService
                    ->upgradeResponse(
                        '90-day analytics history requires Platinum.',
                        'Platinum',
                        [
                            'requested_period' => $period,
                            'allowed_history_days' => $allowedHistory,
                        ]
                    ),
                403
            );
        }

        $days = $requestedDays;

        $startDate = now()
            ->subDays($days - 1)
            ->startOfDay();

        $publication = $user->portfolioPublication;

        if (!$publication) {
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'summary' => $this->emptySummary(),
                    'chart' => [],
                    'traffic_sources' => [],
                    'devices' => [],
                    'browsers' => [],
                    'operating_systems' => [],
                    'countries' => [],
                    'top_projects' => [],
                    'recent_visitors' => [],
                    'engagement' => [],
                ],
            ]);
        }

        $analyticsQuery = $publication
            ->analytics()
            ->where('created_at', '>=', $startDate);

        $chartRows = (clone $analyticsQuery)
            ->where('event_type', 'portfolio_view')
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as views'),
                DB::raw(
                    'COUNT(DISTINCT visitor_id) as visitors'
                )
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chart = collect(range(0, $days - 1))
            ->map(function ($offset) use (
                $startDate,
                $chartRows
            ) {
                $date = $startDate
                    ->copy()
                    ->addDays($offset);

                $key = $date->format('Y-m-d');
                $row = $chartRows->get($key);

                return [
                    'day' => $key,
                    'label' => $date->format('M d'),
                    'views' =>
                        (int) ($row?->views ?? 0),
                    'visitors' =>
                        (int) ($row?->visitors ?? 0),
                ];
            })
            ->values();

        $portfolioViews = (clone $analyticsQuery)
            ->where('event_type', 'portfolio_view')
            ->get();

        $trafficSources = $portfolioViews
            ->groupBy(
                fn ($item) =>
                    $this->formatTrafficSource(
                        $item->referrer
                    )
            )
            ->map(function ($items, $label) {
                return [
                    'label' => $label,
                    'visits' => $items->count(),
                ];
            })
            ->sortByDesc('visits')
            ->values();

        $devices = $portfolioViews
            ->groupBy(
                fn ($item) =>
                    $item->device_type ?: 'Unknown'
            )
            ->map(function ($items, $device) {
                return [
                    'device' => $device,
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,

                'summary' => $this->buildSummary(
                    clone $analyticsQuery
                ),

                'chart' => $chart,

                'traffic_sources' => $trafficSources,

                'devices' => $devices,

                'browsers' => $this->groupFieldCounts(
                    clone $analyticsQuery,
                    'browser'
                ),

                'operating_systems' =>
                    $this->groupFieldCounts(
                        clone $analyticsQuery,
                        'operating_system',
                        'operating_system'
                    ),

                'countries' => $this->groupFieldCounts(
                    clone $analyticsQuery,
                    'country'
                ),

                'top_projects' => $this->getTopProjects(
                    $publication,
                    $startDate
                ),

                'recent_visitors' =>
                    (clone $analyticsQuery)
                        ->where(
                            'event_type',
                            'portfolio_view'
                        )
                        ->latest()
                        ->limit(10)
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'location' =>
                                    $item->country
                                    ?: 'Unknown',
                                'source' =>
                                    $this->formatTrafficSource(
                                        $item->referrer
                                    ),
                                'page' => data_get(
                                    $item->metadata,
                                    'page',
                                    'Home'
                                ),
                                'duration' => data_get(
                                    $item->metadata,
                                    'duration',
                                    '—'
                                ),
                                'device' =>
                                    $item->device_type
                                    ?: 'Unknown',
                                'browser' =>
                                    $item->browser
                                    ?: 'Unknown',
                                'operating_system' =>
                                    $item->operating_system
                                    ?: 'Unknown',
                                'time' =>
                                    $item->created_at
                                        ?->diffForHumans(),
                            ];
                        })
                        ->values(),

                'engagement' => $this->buildEngagement(
                    clone $analyticsQuery
                ),
            ],
        ]);
    }

    /**
     * Kept for compatibility if an older frontend route
     * still calls /analytics/resume-download.
     */
    public function trackResumeDownload(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'slug' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $publication = PortfolioPublication::query()
            ->where('slug', $validated['slug'])
            ->where('is_published', true)
            ->first();

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Published portfolio not found.',
            ], 404);
        }

        $this->analyticsService->record(
            $publication,
            $request,
            'resume_download'
        );

        return response()->json([
            'success' => true,
        ]);
    }

    private function emptySummary(): array
    {
        return [
            'portfolio_views' => 0,
            'unique_visitors' => 0,
            'resume_views' => 0,
            'resume_downloads' => 0,
            'project_clicks' => 0,
            'social_link_clicks' => 0,
            'certificate_views' => 0,
            'email_clicks' => 0,
            'phone_clicks' => 0,
            'whatsapp_clicks' => 0,
        ];
    }

    private function buildSummary(
    $query
): array {
        return [
            'portfolio_views' =>
                (clone $query)
                    ->where(
                        'event_type',
                        'portfolio_view'
                    )
                    ->count(),

            'unique_visitors' =>
                (clone $query)
                    ->where(
                        'event_type',
                        'portfolio_view'
                    )
                    ->whereNotNull('visitor_id')
                    ->distinct()
                    ->count('visitor_id'),

            'resume_views' =>
                $this->eventCount(
                    clone $query,
                    'resume_view'
                ),

            'resume_downloads' =>
                $this->eventCount(
                    clone $query,
                    'resume_download'
                ),

            'project_clicks' =>
                $this->eventCount(
                    clone $query,
                    'project_click'
                ),

            'social_link_clicks' =>
                $this->eventCount(
                    clone $query,
                    'social_link_click'
                ),

            'certificate_views' =>
                $this->eventCount(
                    clone $query,
                    'certificate_view'
                ),

            'email_clicks' =>
                $this->eventCount(
                    clone $query,
                    'email_click'
                ),

            'phone_clicks' =>
                $this->eventCount(
                    clone $query,
                    'phone_click'
                ),

            'whatsapp_clicks' =>
                $this->eventCount(
                    clone $query,
                    'whatsapp_click'
                ),
        ];
    }

    private function eventCount(
    $query,
    string $eventType
): int {
        return $query
            ->where('event_type', $eventType)
            ->count();
    }

    private function buildEngagement(
    $query
): array {
        return (clone $query)
            ->whereIn('event_type', [
                'resume_view',
                'resume_download',
                'project_click',
                'social_link_click',
                'certificate_view',
                'email_click',
                'phone_click',
                'whatsapp_click',
            ])
            ->select(
                'event_type',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('event_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'event_type' => $item->event_type,
                'total' => (int) $item->total,
            ])
            ->values()
            ->all();
    }

    private function groupFieldCounts(
    $query,
    string $field,
    ?string $responseKey = null
) {
        $responseKey ??= $field;

        return $query
            ->where('event_type', 'portfolio_view')
            ->selectRaw(
                "COALESCE(NULLIF({$field}, ''), 'Unknown') as grouped_value"
            )
            ->selectRaw('COUNT(*) as visits')
            ->groupBy('grouped_value')
            ->orderByDesc('visits')
            ->get()
            ->map(fn ($item) => [
                $responseKey =>
                    $item->grouped_value ?: 'Unknown',
                'visits' => (int) $item->visits,
            ])
            ->values();
    }

    private function formatTrafficSource(
        ?string $referrer
    ): string {
        $referrer = strtolower(
            trim($referrer ?? '')
        );

        if ($referrer === '') {
            return 'Direct';
        }

        if (
            Str::contains($referrer, [
                'localhost',
                '127.0.0.1',
            ])
        ) {
            return 'Direct';
        }

        if (Str::contains($referrer, 'linkedin')) {
            return 'LinkedIn';
        }

        if (Str::contains($referrer, 'google')) {
            return 'Google Search';
        }

        if (Str::contains($referrer, 'github')) {
            return 'GitHub';
        }

        if (Str::contains($referrer, 'facebook')) {
            return 'Facebook';
        }

        if (
            Str::contains($referrer, [
                'whatsapp',
                'wa.me',
            ])
        ) {
            return 'WhatsApp';
        }

        return 'Other';
    }

    private function getTopProjects(
        PortfolioPublication $publication,
        $startDate
    ): array {
        $clickData = $publication
            ->analytics()
            ->where('created_at', '>=', $startDate)
            ->where('event_type', 'project_click')
            ->whereNotNull('event_target')
            ->select(
                'event_target',
                DB::raw('COUNT(*) as clicks')
            )
            ->groupBy('event_target')
            ->orderByDesc('clicks')
            ->limit(5)
            ->get();

        if ($clickData->isEmpty()) {
            return [];
        }

        $projectIds = $clickData
            ->pluck('event_target')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $projects = Project::query()
            ->where(
                'user_id',
                $publication->user_id
            )
            ->whereIn('id', $projectIds)
            ->get()
            ->keyBy('id');

        return $clickData
            ->map(function ($item) use ($projects) {
                $projectId =
                    (int) $item->event_target;

                $project = $projects->get($projectId);

                if (!$project) {
                    return null;
                }

                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'category' =>
                        $project->category
                        ?: 'Professional Project',
                    'clicks' =>
                        (int) $item->clicks,
                    'views' => 0,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}