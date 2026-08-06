<?php

namespace App\Http\Controllers\Api\Healthcare;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\Healthcare\StoreMedicalCertificationRequest;
use App\Http\Requests\Healthcare\UpdateMedicalCertificationRequest;
use App\Models\MedicalCertification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class MedicalCertificationController extends Controller
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
                    ['feature' => 'medical_certifications']
                ),
                403
            );
        }

        $certifications = MedicalCertification::query()
            ->where('user_id', auth('api')->id())
            ->latest('issue_date')
            ->latest('id')
            ->get()
            ->map(fn (MedicalCertification $certification) =>
                $this->formatCertification($certification)
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Medical certifications retrieved successfully.',
            'data' => $certifications,
        ]);
    }

    public function store(
        StoreMedicalCertificationRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'medical_certifications']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'medical_certifications',
            $user->medicalCertifications()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

try {
            $certification = DB::transaction(function () use ($request) {
                return MedicalCertification::create([
                    'user_id' => auth('api')->id(),
                    'title' => $request->title,
                    'issuer' => $request->issuer,
                    'category' => $request->category,
                    'issue_date' => $request->issueDate,
                    'expiry_date' => $request->expiryDate,
                    'status' => $request->status,
                    'description' => $request->description,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Medical certification created successfully.',
                'data' =>
                    $this->formatCertification($certification),
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to create medical certification.',
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
                    ['feature' => 'medical_certifications']
                ),
                403
            );
        }

        $certification = $this->findUserCertification($id);

        return response()->json([
            'success' => true,
            'message' =>
                'Medical certification retrieved successfully.',
            'data' =>
                $this->formatCertification($certification),
        ]);
    }

    public function update(
        UpdateMedicalCertificationRequest $request,
        int $id
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'medical_certifications']
                ),
                403
            );
        }

        $certification = $this->findUserCertification($id);

        try {
            DB::transaction(function () use (
                $request,
                $certification
            ) {
                $certification->update([
                    'title' => $request->title,
                    'issuer' => $request->issuer,
                    'category' => $request->category,
                    'issue_date' => $request->issueDate,
                    'expiry_date' => $request->expiryDate,
                    'status' => $request->status,
                    'description' => $request->description,
                ]);
            });

            $certification->refresh();

            return response()->json([
                'success' => true,
                'message' =>
                    'Medical certification updated successfully.',
                'data' =>
                    $this->formatCertification($certification),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to update medical certification.',
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $certification = $this->findUserCertification($id);

        try {
            DB::transaction(function () use ($certification) {
                $certification->delete();
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Medical certification deleted successfully.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to delete medical certification.',
            ], 500);
        }
    }

    private function findUserCertification(
        int $id
    ): MedicalCertification {
        return MedicalCertification::query()
            ->where('user_id', auth('api')->id())
            ->whereKey($id)
            ->firstOrFail();
    }

    private function formatCertification(
        MedicalCertification $certification
    ): array {
        return [
            'id' => $certification->id,
            'title' => $certification->title,
            'issuer' => $certification->issuer,
            'category' => $certification->category,

            'issueDate' => $certification->issue_date
                ? $certification->issue_date->format('Y-m-d')
                : '',

            'expiryDate' => $certification->expiry_date
                ? $certification->expiry_date->format('Y-m-d')
                : '',

            'status' => $certification->status,
            'description' => $certification->description ?? '',

            'createdAt' => $certification->created_at
                ? $certification->created_at->toISOString()
                : null,

            'updatedAt' => $certification->updated_at
                ? $certification->updated_at->toISOString()
                : null,
        ];
    }
}