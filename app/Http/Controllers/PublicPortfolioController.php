<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPublication;
use App\Services\PortfolioAnalyticsService;
use App\Services\PortfolioDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PublicPortfolioController extends Controller
{
    public function __construct(
        private PortfolioDataService $portfolioDataService,
        private PortfolioAnalyticsService $analyticsService
    ) {
    }

    public function show(
        string $slug
    ): JsonResponse {
        $publication = $this->findPublishedPortfolio(
            $slug
        );

        if (!$publication) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' =>
                    'Published portfolio not found.',
            ], 404);
        }

        if ($publication->visibility === 'private') {
            return response()->json([
                'success' => false,
                'status' => 'private',
                'message' =>
                    'This portfolio is private.',
            ], 403);
        }

        if (
            $publication->visibility === 'password'
        ) {
            return response()->json([
                'success' => true,
                'status' => 'password_required',
                'message' =>
                    'Password is required.',
            ]);
        }

        $this->analyticsService->record(
            $publication,
            request(),
            'portfolio_view'
        );

        return response()->json([
            'success' => true,
            'status' => 'public',
            'data' =>
                $this->portfolioDataService
                    ->buildForUser(
                        $publication->user,
                        $publication
                    ),
        ]);
    }

    public function verifyPassword(
        Request $request,
        string $slug
    ): JsonResponse {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $publication = PortfolioPublication::query()
            ->with('user')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where('visibility', 'password')
            ->first();

        if (!$publication) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' =>
                    'Password-protected portfolio not found.',
            ], 404);
        }

        if (
            !$publication->access_password ||
            !Hash::check(
                $validated['password'],
                $publication->access_password
            )
        ) {
            return response()->json([
                'success' => false,
                'status' => 'invalid_password',
                'message' =>
                    'Incorrect portfolio password.',
            ], 422);
        }

        $this->analyticsService->record(
            $publication,
            $request,
            'portfolio_view'
        );

        return response()->json([
            'success' => true,
            'status' => 'unlocked',
            'data' =>
                $this->portfolioDataService
                    ->buildForUser(
                        $publication->user,
                        $publication
                    ),
        ]);
    }

    public function share(
        string $slug
    ): View {
        $publication = PortfolioPublication::query()
            ->with([
                'user',
                'user.portfolioProfile',
                'user.portfolioAbout',
            ])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$publication) {
            abort(
                404,
                'Published portfolio not found.'
            );
        }

        if ($publication->visibility === 'private') {
            abort(
                403,
                'This portfolio is private.'
            );
        }

        $user = $publication->user;
        $profile = $user?->portfolioProfile;
        $about = $user?->portfolioAbout;

        $displayName =
            trim((string) (
                $profile?->display_name
                ?: $user?->name
                ?: 'Portfolio'
            ));

        $title =
            $this->cleanString(
                $publication->seo_title
            )
            ?: "{$displayName} | Professional Portfolio";

        $description =
            $this->cleanString(
                $publication->seo_description
            )
            ?: $this->cleanString(
                $about?->professional_headline
            )
            ?: $this->cleanString(
                $profile?->tagline
            )
            ?: $this->cleanString(
                $profile?->short_introduction
            )
            ?: 'View this professional portfolio.';

        $image =
            $profile?->profile_image
            ?? $user?->profile_photo;

        $frontendUrl = rtrim(
            config(
                'app.frontend_url',
                'http://localhost:5173'
            ),
            '/'
        );

        $portfolioUrl =
            $frontendUrl
            . '/'
            . rawurlencode($publication->slug);

        return view('portfolio-share', [
            'title' => $title,
            'description' => $description,
            'imageUrl' =>
                $this->shareImageUrl($image),
            'portfolioUrl' => $portfolioUrl,
            'canonicalUrl' =>
                url('/share/' . $publication->slug),
            'allowSearchEngines' =>
                (bool) $publication
                    ->allow_search_engines,
            'visibility' =>
                $publication->visibility,
        ]);
    }

    private function findPublishedPortfolio(
        string $slug
    ): ?PortfolioPublication {
        return PortfolioPublication::query()
            ->with('user')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }

    private function shareImageUrl(
        ?string $path
    ): string {
        $path = trim((string) $path);

        if ($path === '') {
            return asset(
                'images/default-portfolio-share.jpg'
            );
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://')
        ) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return url($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return url('/' . $path);
        }

        return asset(
            'storage/' . ltrim($path, '/')
        );
    }

    private function cleanString(
        ?string $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}