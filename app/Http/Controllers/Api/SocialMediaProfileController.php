<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreSocialMediaProfileRequest;
use App\Http\Requests\UpdateSocialMediaProfileRequest;
use App\Models\SocialMediaProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class SocialMediaProfileController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    /**
     * Display all social media profiles
     * belonging to the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'social_media_profiles']
                ),
                403
            );
        }

        try {

            $profiles = SocialMediaProfile::query()
                ->where('user_id', auth('api')->id())
                ->latest()
                ->get()
                ->map(
                    fn (SocialMediaProfile $profile)
                        => $this->transform($profile)
                );

            return response()->json([
                'success' => true,
                'message' =>
                    'Social media profiles retrieved successfully.',
                'data' => $profiles,
            ]);

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to retrieve social media profiles.',
                'data' => [],
            ],500);

        }
    }

    /**
     * Store a new social media profile.
     */
    public function store(
        StoreSocialMediaProfileRequest $request
    ): JsonResponse
    {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'social_media_profiles']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'social_media_profiles',
            $user->socialMediaProfiles()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

try {

            $profile = DB::transaction(function () use ($request) {

                return SocialMediaProfile::create([

                    'user_id' => auth('api')->id(),

                    ...$request->validated(),

                ]);

            });

            $profile->refresh();

            return response()->json([

                'success' => true,

                'message' =>
                    'Social media profile created successfully.',

                'data' =>
                    $this->transform($profile),

            ],201);

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([

                'success' => false,

                'message' =>
                    'Failed to create social media profile.',

                'data' => null,

            ],500);

        }
    }

    /**
     * Display a specific social media profile.
     */
    public function show(
        int $id
    ): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'social_media_profiles']
                ),
                403
            );
        }

        try {

            $profile = $this->findOwnedProfile($id);

            if (!$profile) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Social media profile not found.',

                    'data' => null,

                ],404);

            }

            return response()->json([

                'success' => true,

                'message' =>
                    'Social media profile retrieved successfully.',

                'data' =>
                    $this->transform($profile),

            ]);

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([

                'success' => false,

                'message' =>
                    'Failed to retrieve social media profile.',

                'data' => null,

            ],500);

        }
    }

    /**
     * Update a social media profile.
     */
    public function update(
        UpdateSocialMediaProfileRequest $request,
        int $id
    ): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'social_media_profiles']
                ),
                403
            );
        }

        try {

            $profile = $this->findOwnedProfile($id);

            if (!$profile) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Social media profile not found.',

                    'data' => null,

                ],404);

            }

            DB::transaction(function () use (
                $profile,
                $request
            ) {

                $profile->update(
                    $request->validated()
                );

            });

            $profile->refresh();

            return response()->json([

                'success' => true,

                'message' =>
                    'Social media profile updated successfully.',

                'data' =>
                    $this->transform($profile),

            ]);

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([

                'success' => false,

                'message' =>
                    'Failed to update social media profile.',

                'data' => null,

            ],500);

        }
    }

    /**
     * Delete a social media profile.
     */
    public function destroy(
        int $id
    ): JsonResponse
    {
        try {

            $profile = $this->findOwnedProfile($id);

            if (!$profile) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Social media profile not found.',

                    'data' => null,

                ],404);

            }

            DB::transaction(function () use ($profile) {

                $profile->delete();

            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Social media profile deleted successfully.',

                'data' => null,

            ]);

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([

                'success' => false,

                'message' =>
                    'Failed to delete social media profile.',

                'data' => null,

            ],500);

        }
    }

    /**
     * Find profile owned by authenticated user.
     */
    private function findOwnedProfile(
        int $id
    ): ?SocialMediaProfile
    {
        return SocialMediaProfile::query()
            ->where('id',$id)
            ->where(
                'user_id',
                auth('api')->id()
            )
            ->first();
    }

    /**
     * Transform model into frontend format.
     */
    private function transform(
        SocialMediaProfile $profile
    ): array
    {
        return [

            'id' => $profile->id,

            'platform' =>
                $profile->platform,

            'username' =>
                $profile->username,

            'profileUrl' =>
                $profile->profile_url,

            'followers' =>
                (int) ($profile->followers ?? 0),

            'contentType' =>
                $profile->content_type,

            'status' =>
                $profile->status,

            'description' =>
                $profile->description,

            'createdAt' =>
                optional(
                    $profile->created_at
                )->toISOString(),

            'updatedAt' =>
                optional(
                    $profile->updated_at
                )->toISOString(),

        ];
    }
}