<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreTeachingSubjectRequest;
use App\Http\Requests\UpdateTeachingSubjectRequest;
use App\Models\TeachingSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeachingSubjectController extends Controller
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
                    ['feature' => 'teaching_subjects']
                ),
                403
            );
        }

        $subjects = $request->user()
            ->teachingSubjects()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Teaching subjects retrieved successfully.',
            'data' => $subjects,
        ]);
    }

    public function store(
        StoreTeachingSubjectRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'teaching_subjects']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'teaching_subjects',
            $user->teachingSubjects()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$subject = $request->user()
            ->teachingSubjects()
            ->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Teaching subject created successfully.',
            'data' => $subject,
        ], 201);
    }

    public function show(
        TeachingSubject $teachingSubject,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'teaching_subjects']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $teachingSubject,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Teaching subject retrieved successfully.',
            'data' => $teachingSubject,
        ]);
    }

    public function update(
        UpdateTeachingSubjectRequest $request,
        TeachingSubject $teachingSubject
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'teaching_subjects']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $teachingSubject,
            $request
        );

        $teachingSubject->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Teaching subject updated successfully.',
            'data' => $teachingSubject,
        ]);
    }

    public function destroy(
        TeachingSubject $teachingSubject,
        Request $request
    ): JsonResponse {

        $this->ensureOwnership(
            $teachingSubject,
            $request
        );

        $teachingSubject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teaching subject deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        TeachingSubject $teachingSubject,
        Request $request
    ): void {

        if (
            $teachingSubject->user_id !==
            $request->user()->id
        ) {
            abort(403, 'Unauthorized');
        }

    }
}