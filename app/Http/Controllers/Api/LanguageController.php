<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Models\Language;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    /**
     * Return all languages belonging to
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

        $languages = Language::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('language')
            ->get();

        return response()->json([
            'success' => true,
            'languages' => $languages,

            'package_limit' =>
                $this->packageLimitData(
                    $user,
                    $languages->count()
                ),
        ]);
    }

    /**
     * Return one language record.
     */
    public function show(
        Request $request,
        Language $language
    ): JsonResponse {
        $user = $request->user();

        $accessError =
            $this->moduleAccessError($user);

        if ($accessError) {
            return $accessError;
        }

        $this->authorizeOwnership(
            $language,
            $user->id
        );

        return response()->json([
            'success' => true,
            'language' => $language,
        ]);
    }

    /**
     * Store a new language record.
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
                'languages',
                $user->languages()->count(),
                $requiredPackage
            );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate([
            'language' => [
                'required',
                'string',
                'min:2',
                'max:100',

                Rule::unique(
                    'languages',
                    'language'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'user_id',
                            $user->id
                        )
                ),
            ],

            'proficiency' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'reading_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'writing_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'speaking_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'certificate_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'certificate_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'is_native' => [
                'sometimes',
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
        ]);

        $validated['user_id'] =
            $user->id;

        $validated['is_native'] =
            $request->boolean('is_native');

        $validated['is_featured'] =
            $request->boolean('is_featured');

        $validated['display_order'] =
            $validated['display_order']
            ?? 0;

        $language = Language::create(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Language added successfully.',
            'language' => $language,

            'package_limit' =>
                $this->packageLimitData(
                    $user,
                    $user->languages()->count()
                ),
        ], 201);
    }

    /**
     * Update an existing language record.
     */
    public function update(
        Request $request,
        Language $language
    ): JsonResponse {
        $user = $request->user();

        $accessError =
            $this->moduleAccessError($user);

        if ($accessError) {
            return $accessError;
        }

        $this->authorizeOwnership(
            $language,
            $user->id
        );

        $validated = $request->validate([
            'language' => [
                'required',
                'string',
                'min:2',
                'max:100',

                Rule::unique(
                    'languages',
                    'language'
                )
                    ->ignore($language->id)
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'user_id',
                                $user->id
                            )
                    ),
            ],

            'proficiency' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'reading_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'writing_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'speaking_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'certificate_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'certificate_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'is_native' => [
                'sometimes',
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
        ]);

        $validated['is_native'] =
            $request->boolean('is_native');

        $validated['is_featured'] =
            $request->boolean('is_featured');

        $validated['display_order'] =
            $validated['display_order']
            ?? $language->display_order
            ?? 0;

        $language->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Language updated successfully.',
            'language' =>
                $language->fresh(),
        ]);
    }

    /**
     * Delete an existing language record.
     */
    public function destroy(
        Request $request,
        Language $language
    ): JsonResponse {
        $user = $request->user();

        /*
         * Existing locked records can still be deleted.
         * This allows a downgraded user to clean old data.
         */

        $this->authorizeOwnership(
            $language,
            $user->id
        );

        $language->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Language deleted successfully.',

            'package_limit' =>
                $this->packageLimitData(
                    $user,
                    $user->languages()->count()
                ),
        ]);
    }

    /**
     * Block the language module for Silver users.
     */
    private function moduleAccessError(
        $user
    ): ?JsonResponse {
        if (
            $this->packageAccessService
                ->canAccessModule(
                    $user,
                    'languages'
                )
        ) {
            return null;
        }

        return response()->json(
            $this->packageAccessService
                ->upgradeResponse(
                    'Languages module is available from Gold.',
                    'Gold',
                    [
                        'feature' =>
                            'languages',
                    ]
                ),
            403
        );
    }

    /**
     * Return current package usage information.
     */
    private function packageLimitData(
        $user,
        int $currentCount
    ): array {
        $limit =
            $this->packageAccessService
                ->limit(
                    $user,
                    'languages'
                );

        $unlimited =
            $limit === null;

        $limitReached =
            !$unlimited &&
            $currentCount >= $limit;

        return [
            'feature' => 'languages',

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
     * Verify that the record belongs
     * to the authenticated user.
     */
    private function authorizeOwnership(
        Language $language,
        int $userId
    ): void {
        abort_unless(
            (int) $language->user_id ===
                (int) $userId,
            403,
            'You are not authorized to access this language.'
        );
    }
}