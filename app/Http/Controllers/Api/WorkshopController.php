<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkshopController extends Controller
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
                    ['feature' => 'workshops']
                ),
                403
            );
        }

        $workshops = $request->user()
            ->workshops()
            ->latest('workshop_date')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Workshops retrieved successfully.',
            'data' => $workshops,
        ]);
    }

    public function store(
        StoreWorkshopRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'workshops']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'workshops',
            $user->workshops()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$workshop = $request->user()
            ->workshops()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Workshop created successfully.',
            'data' => $workshop,
        ], 201);
    }

    public function show(
        Workshop $workshop,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'workshops']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $workshop,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Workshop retrieved successfully.',
            'data' => $workshop,
        ]);
    }

    public function update(
        UpdateWorkshopRequest $request,
        Workshop $workshop
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'workshops']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $workshop,
            $request
        );

        $workshop->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Workshop updated successfully.',
            'data' => $workshop->fresh(),
        ]);
    }

    public function destroy(
        Workshop $workshop,
        Request $request
    ): JsonResponse {
        $this->ensureOwnership(
            $workshop,
            $request
        );

        $workshop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workshop deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        Workshop $workshop,
        Request $request
    ): void {
        if (
            (int) $workshop->user_id !==
            (int) $request->user()->id
        ) {
            abort(
                403,
                'You are not authorized to access this workshop.'
            );
        }
    }
}