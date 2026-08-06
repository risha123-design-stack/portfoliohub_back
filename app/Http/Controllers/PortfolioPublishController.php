<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckPortfolioSlugRequest;
use App\Http\Requests\PublishPortfolioRequest;
use App\Models\PortfolioPublication;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class PortfolioPublishController extends Controller
{
    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function show(): JsonResponse
    {
        $user = auth()->user();
        $publication = $user->portfolioPublication;

        $packageAccess = [
            'seo' => $this->packageAccessService->canUseSeo($user),
            'visibility' => [
                'public' => $this->packageAccessService
                    ->canUseVisibility($user, 'public'),
                'password' => $this->packageAccessService
                    ->canUseVisibility($user, 'password'),
                'private' => $this->packageAccessService
                    ->canUseVisibility($user, 'private'),
            ],
        ];

        if (!$publication) {
            return response()->json([
                'success' => true,
                'data' => [
                    'slug' => null,
                    'url' => null,
                    'is_published' => false,
                    'visibility' => 'public',
                    'published_at' => null,
                    'unpublished_at' => null,
                    'seo' => [
                        'title' => null,
                        'description' => null,
                        'keywords' => null,
                        'allow_search_engines' => true,
                    ],
                    'selected_template' => null,
                    'enabled_modules' => [],
                    'completion_percentage' => 0,
                    'package_access' => $packageAccess,
                ],
            ]);
        }

        $data = $this->formatPublication($publication);
        $data['package_access'] = $packageAccess;

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function checkSlug(
        CheckPortfolioSlugRequest $request
    ): JsonResponse {
        $slug = $request->validated('slug');

        $reservedSlugs = [
            'admin',
            'api',
            'dashboard',
            'login',
            'logout',
            'register',
            'portfolio',
            'support',
            'settings',
            'preview',
            'publish',
            'templates',
        ];

        if (in_array($slug, $reservedSlugs, true)) {
            return response()->json([
                'success' => true,
                'available' => false,
                'message' => 'This portfolio address is reserved.',
            ]);
        }

        $exists = PortfolioPublication::query()
            ->where('slug', $slug)
            ->where('user_id', '!=', auth()->id())
            ->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists
                ? 'This portfolio address is unavailable.'
                : 'This portfolio address is available.',
        ]);
    }

    public function publish(
        PublishPortfolioRequest $request
    ): JsonResponse {
        $user = auth()->user();
        $data = $request->validated();
        $visibility = $data['visibility'] ?? 'public';

        if (
            !$this->packageAccessService
                ->canUseVisibility($user, $visibility)
        ) {
            $requiredPackage = $visibility === 'private'
                ? 'Platinum'
                : 'Gold';

            return response()->json(
                $this->packageAccessService
                    ->upgradeResponse(
                        ucfirst($visibility)
                            . ' portfolio visibility requires '
                            . $requiredPackage . '.',
                        $requiredPackage,
                        [
                            'feature' => 'publish_visibility',
                            'visibility' => $visibility,
                        ]
                    ),
                403
            );
        }

        $hasSeoInput =
            filled(data_get($data, 'seo.title')) ||
            filled(data_get($data, 'seo.description')) ||
            filled(data_get($data, 'seo.keywords')) ||
            data_get($data, 'seo.allow_search_engines', true) === false;

        if (
            $hasSeoInput &&
            !$this->packageAccessService->canUseSeo($user)
        ) {
            return response()->json(
                $this->packageAccessService
                    ->upgradeResponse(
                        'SEO settings are available from Gold.',
                        'Gold',
                        ['feature' => 'seo']
                    ),
                403
            );
        }

        $template = $data['selected_template'] ?? [];

        $publicationData = [
            'slug' => $data['slug'],
            'is_published' => true,
            'completion_percentage' =>
                $data['completion_percentage'],
            'visibility' => $visibility,
            'template_id' => isset($template['id'])
                ? (string) $template['id']
                : null,
            'template_name' => $template['name'] ?? null,
            'template_category' => $template['category'] ?? null,
            'template_style' => $template['style'] ?? null,
            'template_package' => $template['packageName'] ?? null,
            'selected_template' => $template,
            'enabled_modules' => $data['enabled_modules'] ?? [],
            'seo_title' => $this->packageAccessService->canUseSeo($user)
                ? data_get($data, 'seo.title')
                : null,
            'seo_description' => $this->packageAccessService->canUseSeo($user)
                ? data_get($data, 'seo.description')
                : null,
            'seo_keywords' => $this->packageAccessService->canUseSeo($user)
                ? data_get($data, 'seo.keywords')
                : null,
            'allow_search_engines' => $this->packageAccessService->canUseSeo($user)
                ? (bool) data_get(
                    $data,
                    'seo.allow_search_engines',
                    true
                )
                : true,
            'published_at' => now(),
            'unpublished_at' => null,
        ];

        if ($visibility === 'password') {
            $publicationData['access_password'] =
                Hash::make($data['portfolio_password']);
        } else {
            $publicationData['access_password'] = null;
        }

        $publication = PortfolioPublication::updateOrCreate(
            ['user_id' => $user->id],
            $publicationData
        );

        return response()->json([
            'success' => true,
            'message' => 'Portfolio published successfully.',
            'data' => $this->formatPublication($publication),
        ]);
    }

    public function unpublish(): JsonResponse
    {
        $user = auth()->user();
        $publication = $user->portfolioPublication;

        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'No portfolio publication was found.',
            ], 404);
        }

        $publication->update([
            'is_published' => false,
            'unpublished_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Portfolio unpublished successfully.',
            'data' => $this->formatPublication(
                $publication->fresh()
            ),
        ]);
    }

    private function formatPublication(
        PortfolioPublication $publication
    ): array {
        $frontendUrl = rtrim(
            config('app.frontend_url', config('app.url')),
            '/'
        );

        return [
            'id' => $publication->id,
            'slug' => $publication->slug,
            'url' => $frontendUrl . '/' . $publication->slug,
            'is_published' => $publication->is_published,
            'completion_percentage' =>
                $publication->completion_percentage ?? 0,
            'visibility' => $publication->visibility,
            'published_at' =>
                $publication->published_at?->toISOString(),
            'unpublished_at' =>
                $publication->unpublished_at?->toISOString(),
            'seo' => [
                'title' => $publication->seo_title,
                'description' => $publication->seo_description,
                'keywords' => $publication->seo_keywords,
                'allow_search_engines' =>
                    $publication->allow_search_engines,
            ],
            'selected_template' =>
                $publication->selected_template,
            'enabled_modules' =>
                $publication->enabled_modules ?? [],
        ];
    }
}