<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
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

        $projects = $user->projects()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'projects' => $projects,

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'projects',
                    $projects->count()
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
                'projects',
                $user->projects()->count(),
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

        $project =
            Project::create($validated);

        return response()->json([
            'success' => true,

            'message' =>
                'Project created successfully.',

            'project' => $project,

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'projects',
                    $user->projects()->count()
                ),
        ], 201);
    }

    public function update(
        Request $request,
        Project $project
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $project,
            $user->id
        );

        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $project->update(
            $this->prepareData(
                $validated,
                $request
            )
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Project updated successfully.',

            'project' =>
                $project->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        Project $project
    ): JsonResponse {
        $user = $request->user();

        $this->authorizeOwnership(
            $project,
            $user->id
        );

        $project->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Project deleted successfully.',

            'package_limit' =>
                $this->packageLimitData(
                    $this->packageAccessService,
                    $user,
                    'projects',
                    $user->projects()->count()
                ),
        ]);
    }

    private function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'image' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'github_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'live_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'role' => [
                'nullable',
                'string',
                'max:100',
            ],

            'team_size' => [
                'nullable',
                'string',
                'max:50',
            ],

            'technologies' => [
                'nullable',
                'array',
                'max:50',
            ],

            'technologies.*' => [
                'string',
                'min:1',
                'max:100',
                'distinct',
            ],

            'start_date' => [
                'required_if:status,In Progress,Completed',
                'nullable',
                'date',
            ],

            'end_date' => [
                'required_if:status,Completed',
                'prohibited_unless:status,Completed',
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'required',
                Rule::in([
                    'Planned',
                    'In Progress',
                    'Completed',
                ]),
            ],

            'featured' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' =>
                'Project title is required.',

            'title.min' =>
                'Project title must contain at least 2 characters.',

            'image.url' =>
                'Project image URL must be a valid HTTP or HTTPS URL.',

            'github_url.url' =>
                'GitHub URL must be a valid HTTP or HTTPS URL.',

            'live_url.url' =>
                'Live demo URL must be a valid HTTP or HTTPS URL.',

            'start_date.required_if' =>
                'Start date is required for an in-progress or completed project.',

            'end_date.required_if' =>
                'End date is required for a completed project.',

            'end_date.prohibited_unless' =>
                'End date can only be entered for a completed project.',

            'end_date.after_or_equal' =>
                'End date cannot be earlier than the start date.',

            'technologies.max' =>
                'You may add up to 50 technologies.',

            'technologies.*.distinct' =>
                'Duplicate technologies are not allowed.',
        ];
    }

    private function prepareData(
        array $validated,
        Request $request
    ): array {
        $validated['title'] =
            trim($validated['title']);

        $validated['technologies'] =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            fn ($item) =>
                                trim(
                                    (string) $item
                                ),
                            $validated[
                                'technologies'
                            ] ?? []
                        )
                    )
                )
            );

        $validated['featured'] =
            $request->boolean(
                'featured'
            );

        if (
            $validated['status'] !==
            'Completed'
        ) {
            $validated['end_date'] =
                null;
        }

        return $validated;
    }

    private function authorizeOwnership(
        Project $project,
        int $userId
    ): void {
        abort_unless(
            (int) $project->user_id ===
                (int) $userId,
            403,
            'You are not authorized to access this project.'
        );
    }
}
