<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreDesignToolRequest;
use App\Http\Requests\UpdateDesignToolRequest;
use App\Models\DesignTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignToolController extends Controller
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
                    ['feature' => 'design_tools']
                ),
                403
            );
        }

        $designTools = DesignTool::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $designTools,
        ]);
    }

    public function store(
        StoreDesignToolRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'design_tools']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'design_tools',
            $user->designTools()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$designTool = DesignTool::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Design tool created successfully.',
            'data' => $designTool,
        ], 201);
    }

    public function show(
        Request $request,
        DesignTool $designTool
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'design_tools']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $request,
            $designTool
        );

        return response()->json([
            'success' => true,
            'data' => $designTool,
        ]);
    }

    public function update(
        UpdateDesignToolRequest $request,
        DesignTool $designTool
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'design_tools']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $request,
            $designTool
        );

        $designTool->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Design tool updated successfully.',
            'data' => $designTool->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        DesignTool $designTool
    ): JsonResponse {
        $this->ensureOwnership(
            $request,
            $designTool
        );

        $designTool->delete();

        return response()->json([
            'success' => true,
            'message' => 'Design tool deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        Request $request,
        DesignTool $designTool
    ): void {
        abort_unless(
            (int) $designTool->user_id ===
            (int) $request->user()->id,
            403,
            'You are not authorised to access this design tool.'
        );
    }
}