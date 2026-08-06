<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Models\Achievement;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AchievementController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    /**
     * Return all achievements belonging to
     * the authenticated user.
     */
    public function index(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $accessError =
            $this->moduleAccessError($user);

        if ($accessError) {
            return $accessError;
        }

        $achievements = Achievement::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc('achievement_date')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'achievements' => $achievements,

            'package_limit' =>
                $this->packageLimitData(
                    $user,
                    $achievements->count()
                ),
        ]);
    }

    /**
     * Return one achievement.
     */
    public function show(
        Request $request,
        Achievement $achievement
    ): JsonResponse {
        $user = $request->user();

        $accessError =
            $this->moduleAccessError($user);

        if ($accessError) {
            return $accessError;
        }

        $this->authorizeOwnership(
            $achievement,
            $user->id
        );

        return response()->json([
            'success' => true,
            'achievement' => $achievement,
        ]);
    }

    /**
     * Store a new achievement.
     */
    public function store(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $accessError =
            $this->moduleAccessError($user);

        if ($accessError) {
            return $accessError;
        }

        $requiredPackage =
            $this->packageAccessService
                ->nextPackage($user)
            ?? 'Platinum';

        $limitError =
            $this->packageLimitError(
                $this->packageAccessService,
                $user,
                'achievements',
                $user->achievements()->count(),
                $requiredPackage
            );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate(
            $this->validationRules()
        );

        $validated = $this->prepareData(
            $validated,
            $request
        );

        $validated['user_id'] =
            $user->id;

        if ($request->hasFile('evidence_file')) {
            $file = $request->file(
                'evidence_file'
            );

            $validated['evidence_file'] =
                $file->store(
                    'achievements/' . $user->id,
                    'public'
                );

            $validated['original_file_name'] =
                $file->getClientOriginalName();
        }

        $achievement = Achievement::create(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Achievement created successfully.',
            'achievement' =>
                $achievement->fresh(),

            'package_limit' =>
                $this->packageLimitData(
                    $user,
                    $user->achievements()->count()
                ),
        ], 201);
    }

    /**
     * Update an existing achievement.
     */
    public function update(
        Request $request,
        Achievement $achievement
    ): JsonResponse {
        $user = $request->user();

        $accessError =
            $this->moduleAccessError($user);

        if ($accessError) {
            return $accessError;
        }

        $this->authorizeOwnership(
            $achievement,
            $user->id
        );

        $validated = $request->validate(
            $this->validationRules()
        );

        $validated = $this->prepareData(
            $validated,
            $request
        );

        $removeEvidenceFile =
            $request->boolean(
                'remove_evidence_file'
            );

        if (
            $removeEvidenceFile &&
            $achievement->evidence_file
        ) {
            Storage::disk('public')->delete(
                $achievement->evidence_file
            );

            $validated['evidence_file'] = null;
            $validated['original_file_name'] =
                null;
        }

        if ($request->hasFile('evidence_file')) {
            if ($achievement->evidence_file) {
                Storage::disk('public')->delete(
                    $achievement->evidence_file
                );
            }

            $file = $request->file(
                'evidence_file'
            );

            $validated['evidence_file'] =
                $file->store(
                    'achievements/' . $user->id,
                    'public'
                );

            $validated['original_file_name'] =
                $file->getClientOriginalName();
        }

        unset(
            $validated['remove_evidence_file']
        );

        $achievement->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Achievement updated successfully.',
            'achievement' =>
                $achievement->fresh(),
        ]);
    }

    /**
     * Delete achievement and uploaded evidence.
     */
    public function destroy(
        Request $request,
        Achievement $achievement
    ): JsonResponse {
        $user = $request->user();

        /*
         * Delete remains allowed for downgraded users
         * so they can clean up old records.
         */

        $this->authorizeOwnership(
            $achievement,
            $user->id
        );

        if ($achievement->evidence_file) {
            Storage::disk('public')->delete(
                $achievement->evidence_file
            );
        }

        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Achievement deleted successfully.',

            'package_limit' =>
                $this->packageLimitData(
                    $user,
                    $user->achievements()->count()
                ),
        ]);
    }

    /**
     * Shared validation rules.
     */
    private function validationRules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'achievement_type' => [
                'nullable',
                'string',
                'max:150',
            ],

            'organization' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'position' => [
                'nullable',
                'string',
                'max:150',
            ],

            'level' => [
                'nullable',
                Rule::in([
                    'Institution',
                    'School',
                    'University',
                    'Company',
                    'District',
                    'Provincial',
                    'National',
                    'International',
                    'Online',
                    'Other',
                ]),
            ],

            'achievement_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'achievement_url' => [
                'nullable',
                'url:http,https',
                'max:1000',
            ],

            'evidence_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'remove_evidence_file' => [
                'nullable',
                'boolean',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    /**
     * Clean and normalize request values.
     */
    private function prepareData(
        array $validated,
        Request $request
    ): array {
        $validated['title'] = trim(
            $validated['title']
        );

        $validated['achievement_type'] =
            isset($validated['achievement_type'])
                ? trim(
                    $validated['achievement_type']
                )
                : null;

        $validated['organization'] =
            isset($validated['organization'])
                ? trim(
                    $validated['organization']
                )
                : null;

        $validated['description'] =
            isset($validated['description'])
                ? trim(
                    $validated['description']
                )
                : null;

        $validated['position'] =
            isset($validated['position'])
                ? trim(
                    $validated['position']
                )
                : null;

        $validated['achievement_url'] =
            isset($validated['achievement_url'])
                ? trim(
                    $validated['achievement_url']
                )
                : null;

        $validated['is_featured'] =
            $request->boolean('is_featured');

        $validated['display_order'] =
            $validated['display_order']
            ?? 0;

        return $validated;
    }

    /**
     * Silver users cannot access this module.
     */
    private function moduleAccessError(
        $user
    ): ?JsonResponse {
        if (
            $this->packageAccessService
                ->canAccessModule(
                    $user,
                    'achievements'
                )
        ) {
            return null;
        }

        return response()->json(
            $this->packageAccessService
                ->upgradeResponse(
                    'Achievements module is available from Gold.',
                    'Gold',
                    [
                        'feature' =>
                            'achievements',
                    ]
                ),
            403
        );
    }

    /**
     * Current package usage information.
     */
    private function packageLimitData(
        $user,
        int $currentCount
    ): array {
        $limit =
            $this->packageAccessService
                ->limit(
                    $user,
                    'achievements'
                );

        $unlimited =
            $limit === null;

        $limitReached =
            !$unlimited &&
            $currentCount >= $limit;

        return [
            'feature' =>
                'achievements',

            'current_count' =>
                $currentCount,

            'limit' =>
                $limit,

            'unlimited' =>
                $unlimited,

            'limit_reached' =>
                $limitReached,

            'required_package' =>
                $limitReached
                    ? (
                        $this
                            ->packageAccessService
                            ->nextPackage($user)
                        ?? 'Platinum'
                    )
                    : null,
        ];
    }

    /**
     * Verify record ownership.
     */
    private function authorizeOwnership(
        Achievement $achievement,
        int $userId
    ): void {
        abort_unless(
            (int) $achievement->user_id ===
                (int) $userId,
            403,
            'You are not authorized to access this achievement.'
        );
    }
}