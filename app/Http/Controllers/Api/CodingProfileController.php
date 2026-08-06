<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\CodingProfileRequest;
use App\Models\CodingProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodingProfileController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'coding_profiles']
                ),
                403
            );
        }

        $profiles = CodingProfile::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (CodingProfile $profile) =>
                $this->formatProfile($profile)
            );

        return response()->json([
            'success' => true,
            'message' => 'Coding profiles retrieved successfully.',
            'data' => $profiles,
        ]);
    }

    public function store(
        CodingProfileRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'coding_profiles']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'coding_profiles',
            $user->codingProfiles()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$validated = $request->validated();

        $profile = CodingProfile::create([
            'user_id' => $request->user()->id,
            'platform' => $validated['platform'],
            'username' => $validated['username'],
            'rating' => $validated['rating'] ?? null,
            'solved' => $validated['solved'] ?? null,
            'rank' => $validated['rank'] ?? null,
            'stars' => $validated['stars'] ?? null,
            'profile_url' => $validated['profileUrl'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coding profile created successfully.',
            'data' => $this->formatProfile($profile),
        ], 201);
    }

    public function show(
        Request $request,
        CodingProfile $codingProfile
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'coding_profiles']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $request,
            $codingProfile
        );

        return response()->json([
            'success' => true,
            'message' => 'Coding profile retrieved successfully.',
            'data' => $this->formatProfile($codingProfile),
        ]);
    }

    public function update(
        CodingProfileRequest $request,
        CodingProfile $codingProfile
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'coding_profiles']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $request,
            $codingProfile
        );

        $validated = $request->validated();

        $codingProfile->update([
            'platform' => $validated['platform'],
            'username' => $validated['username'],
            'rating' => $validated['rating'] ?? null,
            'solved' => $validated['solved'] ?? null,
            'rank' => $validated['rank'] ?? null,
            'stars' => $validated['stars'] ?? null,
            'profile_url' => $validated['profileUrl'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coding profile updated successfully.',
            'data' => $this->formatProfile(
                $codingProfile->fresh()
            ),
        ]);
    }

    public function destroy(
        Request $request,
        CodingProfile $codingProfile
    ): JsonResponse {
        $this->ensureOwnership(
            $request,
            $codingProfile
        );

        $codingProfile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coding profile deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        Request $request,
        CodingProfile $codingProfile
    ): void {
        abort_if(
            $codingProfile->user_id !== $request->user()->id,
            403,
            'You are not allowed to access this coding profile.'
        );
    }

    private function formatProfile(
        CodingProfile $profile
    ): array {
        return [
            'id' => $profile->id,
            'platform' => $profile->platform,
            'username' => $profile->username,
            'rating' => $profile->rating,
            'solved' => $profile->solved,
            'rank' => $profile->rank,
            'stars' => $profile->stars,
            'profileUrl' => $profile->profile_url,
            'createdAt' => $profile->created_at?->toISOString(),
            'updatedAt' => $profile->updated_at?->toISOString(),
        ];
    }
}