<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $experiences =
            $user->experiences()
                ->orderBy(
                    'display_order'
                )
                ->orderByDesc(
                    'start_date'
                )
                ->latest()
                ->get();

        return response()->json([
            'success' => true,
            'experiences' => $experiences,

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'experience',
                    $experiences->count()
                ),
        ]);
    }

    public function store(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $limitError =
            $this->packageLimitError(
                $this->packageAccessService,
                $user,
                'experience',
                $user->experiences()->count(),
                $this->packageAccessService
                    ->nextPackage($user)
                    ?? 'Platinum'
            );

        if ($limitError) {
            return $limitError;
        }

        $validated =
            $request->validate(
                $this->rules(),
                $this->messages()
            );

        $validated =
            $this->prepareData(
                $validated,
                $request
            );

        $validated['user_id'] =
            $user->id;

        $experience =
            Experience::create(
                $validated
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Experience created successfully.',

            'experience' =>
                $experience,

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'experience',
                    $user->experiences()->count()
                ),
        ], 201);
    }

    public function update(
        Request $request,
        Experience $experience
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $experience->user_id,
            $user->id
        );

        $validated =
            $request->validate(
                $this->rules(),
                $this->messages()
            );

        $validated =
            $this->prepareData(
                $validated,
                $request
            );

        $experience->update(
            $validated
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Experience updated successfully.',

            'experience' =>
                $experience->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        Experience $experience
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $experience->user_id,
            $user->id
        );

        $experience->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Experience deleted successfully.',

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'experience',
                    $user->experiences()->count()
                ),
        ]);
    }

    private function rules(): array
    {
        return [
            'organization_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'position_title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'employment_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'industry' => [
                'nullable',
                'string',
                'max:255',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'location_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'start_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                'before_or_equal:today',
            ],

            'currently_working' => [
                'sometimes',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'achievements' => [
                'nullable',
                'array',
                'max:30',
            ],

            'achievements.*' => [
                'string',
                'min:2',
                'max:500',
                'distinct',
            ],

            'skills' => [
                'nullable',
                'array',
                'max:50',
            ],

            'skills.*' => [
                'string',
                'min:1',
                'max:100',
                'distinct',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'organization_name.required' =>
                'Organization name is required.',

            'organization_name.min' =>
                'Organization name must contain at least 2 characters.',

            'position_title.required' =>
                'Position title is required.',

            'position_title.min' =>
                'Position title must contain at least 2 characters.',

            'start_date.before_or_equal' =>
                'Start date cannot be in the future.',

            'end_date.after_or_equal' =>
                'End date cannot be earlier than the start date.',

            'end_date.before_or_equal' =>
                'End date cannot be in the future.',

            'achievements.max' =>
                'You may add up to 30 achievements to one experience.',

            'achievements.*.distinct' =>
                'Duplicate achievements are not allowed.',

            'skills.max' =>
                'You may add up to 50 skills to one experience.',

            'skills.*.distinct' =>
                'Duplicate skills are not allowed.',

            'description.max' =>
                'Description may not exceed 5000 characters.',

            'display_order.min' =>
                'Display order must be 0 or greater.',
        ];
    }

    private function prepareData(
        array $validated,
        Request $request
    ): array {
        $validated[
            'organization_name'
        ] = trim(
            $validated[
                'organization_name'
            ]
        );

        $validated[
            'position_title'
        ] = trim(
            $validated[
                'position_title'
            ]
        );

        $validated[
            'currently_working'
        ] = $request->boolean(
            'currently_working'
        );

        $validated['display_order'] =
            $validated['display_order']
            ?? 0;

        $validated['achievements'] =
            $this->cleanArray(
                $validated[
                    'achievements'
                ] ?? []
            );

        $validated['skills'] =
            $this->cleanArray(
                $validated['skills']
                ?? []
            );

        if (
            $validated[
                'currently_working'
            ]
        ) {
            $validated['end_date'] =
                null;
        }

        return $validated;
    }

    private function cleanArray(
        array $values
    ): array {
        $cleanValues = array_map(
            fn ($value) =>
                trim((string) $value),
            $values
        );

        $cleanValues = array_filter(
            $cleanValues,
            fn ($value) =>
                $value !== ''
        );

        return array_values(
            array_unique(
                $cleanValues
            )
        );
    }

    private function authorizeOwnership(
        int $ownerId,
        int $userId
    ): void {
        abort_unless(
            $ownerId === $userId,
            403,
            'You are not authorized to access this experience.'
        );
    }
}
