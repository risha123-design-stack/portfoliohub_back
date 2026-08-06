<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreDeveloperProjectRequest;
use App\Http\Requests\UpdateDeveloperProjectRequest;
use App\Models\DeveloperProject;
use Illuminate\Http\JsonResponse;

class DeveloperProjectController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'developer_projects']
                ),
                403
            );
        }

        $projects = auth()
            ->user()
            ->developerProjects()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Developer projects retrieved successfully.',
            'data' => $projects,
        ]);
    }

    public function store(
        StoreDeveloperProjectRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'developer_projects']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'developer_projects',
            $user->developerProjects()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$project = auth()
            ->user()
            ->developerProjects()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Developer project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function show(
        DeveloperProject $developerProject
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'developer_projects']
                ),
                403
            );
        }

        $this->ensureProjectBelongsToUser(
            $developerProject
        );

        return response()->json([
            'success' => true,
            'message' => 'Developer project retrieved successfully.',
            'data' => $developerProject,
        ]);
    }

    public function update(
        UpdateDeveloperProjectRequest $request,
        DeveloperProject $developerProject
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'developer_projects']
                ),
                403
            );
        }

        $this->ensureProjectBelongsToUser(
            $developerProject
        );

        $developerProject->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Developer project updated successfully.',
            'data' => $developerProject->fresh(),
        ]);
    }

    public function destroy(
        DeveloperProject $developerProject
    ): JsonResponse {
        $this->ensureProjectBelongsToUser(
            $developerProject
        );

        $developerProject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Developer project deleted successfully.',
        ]);
    }

    private function ensureProjectBelongsToUser(
        DeveloperProject $developerProject
    ): void {
        if ($developerProject->user_id !== auth()->id()) {
            abort(
                403,
                'You are not authorised to access this project.'
            );
        }
    }
}