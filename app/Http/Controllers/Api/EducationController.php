<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends Controller
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

        $educations = $user->educations()
            ->orderBy('display_order')
            ->orderByDesc('start_date')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'educations' => $educations,

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'education',
                    $educations->count()
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
                'education',
                $user->educations()->count(),
                $this->packageAccessService
                    ->nextPackage($user)
                    ?? 'Platinum'
            );

        if ($limitError) {
            return $limitError;
        }

        $validated = $request->validate(
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

        $education =
            Education::create(
                $validated
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Education created successfully.',

            'education' => $education,

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'education',
                    $user->educations()->count()
                ),
        ], 201);
    }

    public function update(
        Request $request,
        Education $education
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $education->user_id,
            $user->id
        );

        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $validated =
            $this->prepareData(
                $validated,
                $request
            );

        $education->update(
            $validated
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Education updated successfully.',

            'education' =>
                $education->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        Education $education
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $education->user_id,
            $user->id
        );

        $education->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Education deleted successfully.',

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'education',
                    $user->educations()->count()
                ),
        ]);
    }

    private function rules(): array
    {
        return [
            'institution_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'degree' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'field_of_study' => [
                'nullable',
                'string',
                'max:255',
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

            'currently_studying' => [
                'sometimes',
                'boolean',
            ],

            'grade' => [
                'nullable',
                'string',
                'max:100',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
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
            'institution_name.required' =>
                'Institution name is required.',

            'institution_name.min' =>
                'Institution name must contain at least 2 characters.',

            'degree.required' =>
                'Degree or qualification is required.',

            'degree.min' =>
                'Degree or qualification must contain at least 2 characters.',

            'start_date.before_or_equal' =>
                'Start date cannot be in the future.',

            'end_date.after_or_equal' =>
                'End date cannot be earlier than the start date.',

            'end_date.before_or_equal' =>
                'End date cannot be in the future.',

            'description.max' =>
                'Description may not exceed 3000 characters.',

            'display_order.min' =>
                'Display order must be 0 or greater.',
        ];
    }

    private function prepareData(
        array $validated,
        Request $request
    ): array {
        $validated['institution_name'] =
            trim(
                $validated[
                    'institution_name'
                ]
            );

        $validated['degree'] =
            trim(
                $validated['degree']
            );

        $validated['currently_studying'] =
            $request->boolean(
                'currently_studying'
            );

        $validated['display_order'] =
            $validated['display_order']
            ?? 0;

        if (
            $validated[
                'currently_studying'
            ]
        ) {
            $validated['end_date'] =
                null;
        }

        return $validated;
    }

    private function authorizeOwnership(
        int $ownerId,
        int $userId
    ): void {
        abort_unless(
            $ownerId === $userId,
            403,
            'You are not authorized to access this education record.'
        );
    }
}
