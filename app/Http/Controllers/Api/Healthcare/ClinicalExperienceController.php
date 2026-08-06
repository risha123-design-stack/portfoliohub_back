<?php

namespace App\Http\Controllers\Api\Healthcare;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\Healthcare\StoreClinicalExperienceRequest;
use App\Http\Requests\Healthcare\UpdateClinicalExperienceRequest;
use App\Models\ClinicalExperience;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClinicalExperienceController extends Controller
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
                    ['feature' => 'clinical_experiences']
                ),
                403
            );
        }

        $experiences = ClinicalExperience::query()
            ->where('user_id', auth('api')->id())
            ->latest('start_date')
            ->latest('id')
            ->get()
            ->map(fn (ClinicalExperience $experience) =>
                $this->formatExperience($experience)
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Clinical experiences retrieved successfully.',
            'data' => $experiences,
        ]);
    }

    public function store(
        StoreClinicalExperienceRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'clinical_experiences']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'clinical_experiences',
            $user->clinicalExperiences()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

try {
            $experience = DB::transaction(function () use ($request) {
                return ClinicalExperience::create([
                    'user_id' => auth('api')->id(),
                    'hospital' => $request->hospital,
                    'department' => $request->department,
                    'role' => $request->role,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'status' => $request->status,
                    'description' => $request->description,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Clinical experience created successfully.',
                'data' => $this->formatExperience($experience),
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to create clinical experience.',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'clinical_experiences']
                ),
                403
            );
        }

        $experience = $this->findUserExperience($id);

        return response()->json([
            'success' => true,
            'message' =>
                'Clinical experience retrieved successfully.',
            'data' => $this->formatExperience($experience),
        ]);
    }

    public function update(
        UpdateClinicalExperienceRequest $request,
        int $id
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'clinical_experiences']
                ),
                403
            );
        }

        $experience = $this->findUserExperience($id);

        try {
            DB::transaction(function () use ($request, $experience) {
                $experience->update([
                    'hospital' => $request->hospital,
                    'department' => $request->department,
                    'role' => $request->role,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'status' => $request->status,
                    'description' => $request->description,
                ]);
            });

            $experience->refresh();

            return response()->json([
                'success' => true,
                'message' =>
                    'Clinical experience updated successfully.',
                'data' => $this->formatExperience($experience),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to update clinical experience.',
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $experience = $this->findUserExperience($id);

        try {
            DB::transaction(function () use ($experience) {
                $experience->delete();
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Clinical experience deleted successfully.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to delete clinical experience.',
            ], 500);
        }
    }

    private function findUserExperience(
        int $id
    ): ClinicalExperience {
        return ClinicalExperience::query()
            ->where('user_id', auth('api')->id())
            ->whereKey($id)
            ->firstOrFail();
    }

    private function formatExperience(
        ClinicalExperience $experience
    ): array {
        return [
            'id' => $experience->id,
            'hospital' => $experience->hospital,
            'department' => $experience->department,
            'role' => $experience->role,

            'startDate' => $experience->start_date
                ? $experience->start_date->format('Y-m-d')
                : '',

            'endDate' => $experience->end_date
                ? $experience->end_date->format('Y-m-d')
                : 'Present',

            'status' => $experience->status,
            'description' => $experience->description ?? '',

            'createdAt' => $experience->created_at
                ? $experience->created_at->toISOString()
                : null,

            'updatedAt' => $experience->updated_at
                ? $experience->updated_at->toISOString()
                : null,
        ];
    }
}