<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreStudentCertificationRequest;
use App\Http\Requests\UpdateStudentCertificationRequest;
use App\Models\StudentCertification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCertificationController extends Controller
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
                    ['feature' => 'student_certifications']
                ),
                403
            );
        }

        $certifications = $request->user()
            ->studentCertifications()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Student certifications retrieved successfully.',
            'data' => $certifications,
        ]);
    }

    public function store(
        StoreStudentCertificationRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'student_certifications']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'student_certifications',
            $user->studentCertifications()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$certification = $request->user()
            ->studentCertifications()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student certification created successfully.',
            'data' => $certification,
        ], 201);
    }

    public function show(
        StudentCertification $studentCertification,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'student_certifications']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $studentCertification,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Student certification retrieved successfully.',
            'data' => $studentCertification,
        ]);
    }

    public function update(
        UpdateStudentCertificationRequest $request,
        StudentCertification $studentCertification
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'student_certifications']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $studentCertification,
            $request
        );

        $studentCertification->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Student certification updated successfully.',
            'data' => $studentCertification->fresh(),
        ]);
    }

    public function destroy(
        StudentCertification $studentCertification,
        Request $request
    ): JsonResponse {
        $this->ensureOwnership(
            $studentCertification,
            $request
        );

        $studentCertification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student certification deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        StudentCertification $studentCertification,
        Request $request
    ): void {
        if (
            (int) $studentCertification->user_id !==
            (int) $request->user()->id
        ) {
            abort(
                403,
                'You are not authorised to access this student certification.'
            );
        }
    }
}