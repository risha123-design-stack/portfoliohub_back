<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreCameraEquipmentRequest;
use App\Http\Requests\UpdateCameraEquipmentRequest;
use App\Models\CameraEquipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CameraEquipmentController extends Controller
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
                    ['feature' => 'camera_equipment']
                ),
                403
            );
        }

        $equipment = $request->user()
            ->cameraEquipment()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Camera equipment retrieved successfully.',
            'data' => $equipment,
        ]);
    }

    public function store(
        StoreCameraEquipmentRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'camera_equipment']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'camera_equipment',
            $user->cameraEquipment()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$equipment = $request->user()
            ->cameraEquipment()
            ->create(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Camera equipment added successfully.',
            'data' => $equipment,
        ], 201);
    }

    public function show(
        CameraEquipment $cameraEquipment,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'camera_equipment']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $cameraEquipment,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Camera equipment retrieved successfully.',
            'data' => $cameraEquipment,
        ]);
    }

    public function update(
        UpdateCameraEquipmentRequest $request,
        CameraEquipment $cameraEquipment
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'camera_equipment']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $cameraEquipment,
            $request
        );

        $cameraEquipment->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Camera equipment updated successfully.',
            'data' => $cameraEquipment->fresh(),
        ]);
    }

    public function destroy(
        CameraEquipment $cameraEquipment,
        Request $request
    ): JsonResponse {

        $this->ensureOwnership(
            $cameraEquipment,
            $request
        );

        $cameraEquipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Camera equipment deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        CameraEquipment $cameraEquipment,
        Request $request
    ): void {

        if (
            (int)$cameraEquipment->user_id !==
            (int)$request->user()->id
        ) {

            abort(
                403,
                'Unauthorized.'
            );

        }

    }
}