<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreTechStackRequest;
use App\Http\Requests\UpdateTechStackRequest;
use App\Models\TechStack;
use Illuminate\Http\JsonResponse;

class TechStackController extends Controller
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
                    ['feature' => 'tech_stacks']
                ),
                403
            );
        }

        $techStacks = auth()
            ->user()
            ->techStacks()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tech stacks retrieved successfully.',
            'data' => $techStacks,
        ]);
    }

    public function store(
        StoreTechStackRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'tech_stacks']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'tech_stacks',
            $user->techStacks()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$techStack = auth()
            ->user()
            ->techStacks()
            ->create(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Technology added successfully.',
            'data' => $techStack,
        ], 201);
    }

    public function show(
        TechStack $techStack
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'tech_stacks']
                ),
                403
            );
        }


        $this->authorizeUser(
            $techStack
        );

        return response()->json([
            'success' => true,
            'data' => $techStack,
        ]);
    }

    public function update(
        UpdateTechStackRequest $request,
        TechStack $techStack
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'tech_stacks']
                ),
                403
            );
        }


        $this->authorizeUser(
            $techStack
        );

        $techStack->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Technology updated successfully.',
            'data' => $techStack->fresh(),
        ]);
    }

    public function destroy(
        TechStack $techStack
    ): JsonResponse {

        $this->authorizeUser(
            $techStack
        );

        $techStack->delete();

        return response()->json([
            'success' => true,
            'message' => 'Technology deleted successfully.',
        ]);
    }

    private function authorizeUser(
        TechStack $techStack
    ): void {

        if (
            $techStack->user_id !== auth()->id()
        ) {
            abort(
                403,
                'Unauthorized.'
            );
        }
    }
}