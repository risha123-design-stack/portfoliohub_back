<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SocialLinkController extends Controller
{
    use ChecksPackageLimits;

    private const PLATFORMS = [
        'LinkedIn', 'GitHub', 'GitLab', 'Portfolio', 'Behance',
        'Dribbble', 'Kaggle', 'LeetCode', 'HackerRank',
        'Codeforces', 'Stack Overflow', 'Medium', 'YouTube',
        'ResearchGate', 'Google Scholar', 'ORCID', 'Facebook',
        'Instagram', 'X', 'Custom',
    ];

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $query = SocialLink::query()
            ->where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('platform', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('platform') && $request->platform !== 'all') {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('visibility') && $request->visibility !== 'all') {
            $query->where(
                'is_visible',
                $request->visibility === 'visible'
            );
        }

        $socialLinks = $query
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'social_links' => $socialLinks,
            'package_limit' => $this->packageLimitData(
                $user,
                $socialLinks->count()
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'social_links',
            $user->socialLinks()->count(),
            $this->requiredUpgradePackage($user)
        );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate([
            'platform' => [
                'required',
                'string',
                Rule::in(self::PLATFORMS),
            ],
            'label' => 'nullable|string|max:150',
            'username' => 'nullable|string|max:150',
            'url' => 'required|url:http,https|max:2048',
            'is_visible' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'display_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['user_id'] = $user->id;
        $validated['label'] = $validated['label'] ?? null;
        $validated['username'] = $validated['username'] ?? null;
        $validated['is_visible'] = $request->boolean('is_visible', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['clicks'] = 0;

        $socialLink = SocialLink::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Social link created successfully.',
            'social_link' => $socialLink,
            'package_limit' => $this->packageLimitData(
                $user,
                $user->socialLinks()->count()
            ),
        ], 201);
    }

    public function show(
        Request $request,
        SocialLink $socialLink
    ): JsonResponse {
        $user = $request->user();

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $this->authorizeOwnership($socialLink, $user->id);

        return response()->json([
            'success' => true,
            'social_link' => $socialLink,
        ]);
    }

    public function update(
        Request $request,
        SocialLink $socialLink
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership($socialLink, $user->id);

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $validated = $request->validate([
            'platform' => [
                'required',
                'string',
                Rule::in(self::PLATFORMS),
            ],
            'label' => 'nullable|string|max:150',
            'username' => 'nullable|string|max:150',
            'url' => 'required|url:http,https|max:2048',
            'is_visible' => 'required|boolean',
            'is_featured' => 'required|boolean',
            'display_order' => 'required|integer|min:0|max:9999',
        ]);

        $socialLink->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Social link updated successfully.',
            'social_link' => $socialLink->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        SocialLink $socialLink
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership($socialLink, $user->id);
        $socialLink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social link deleted successfully.',
            'package_limit' => $this->packageLimitData(
                $user,
                $user->socialLinks()->count()
            ),
        ]);
    }

    public function toggleVisibility(
        Request $request,
        SocialLink $socialLink
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership($socialLink, $user->id);

        if ($accessError = $this->moduleAccessError($user)) {
            return $accessError;
        }

        $socialLink->update([
            'is_visible' => !$socialLink->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visibility updated successfully.',
            'social_link' => $socialLink->fresh(),
        ]);
    }

    public function trackClick(SocialLink $socialLink): JsonResponse
    {
        if (!$socialLink->is_visible) {
            return response()->json([
                'success' => false,
                'message' => 'This social link is not publicly available.',
            ], 404);
        }

        $socialLink->increment('clicks');

        return response()->json([
            'success' => true,
            'url' => $socialLink->url,
            'clicks' => $socialLink->fresh()->clicks,
        ]);
    }

    private function moduleAccessError($user): ?JsonResponse
    {
        if (
            $this->packageAccessService
                ->canAccessModule($user, 'social_links')
        ) {
            return null;
        }

        return response()->json(
            $this->packageAccessService->upgradeResponse(
                'Social Links module is available from Gold.',
                'Gold',
                ['feature' => 'social_links']
            ),
            403
        );
    }

    private function packageLimitData(
        $user,
        int $currentCount
    ): array {
        $limit = $this->packageAccessService
            ->limit($user, 'social_links');

        $limitReached = $limit !== null && $currentCount >= $limit;

        return [
            'feature' => 'social_links',
            'current_count' => $currentCount,
            'limit' => $limit,
            'unlimited' => $limit === null,
            'limit_reached' => $limitReached,
            'required_package' => $limitReached
                ? $this->requiredUpgradePackage($user)
                : null,
        ];
    }

    private function requiredUpgradePackage($user): string
    {
        return $this->packageAccessService->nextPackage($user)
            ?? 'Platinum';
    }

    private function authorizeOwnership(
        SocialLink $socialLink,
        int $userId
    ): void {
        abort_unless(
            (int) $socialLink->user_id === (int) $userId,
            403,
            'You are not authorized to access this social link.'
        );
    }
}