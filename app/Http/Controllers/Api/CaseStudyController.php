<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreCaseStudyRequest;
use App\Http\Requests\UpdateCaseStudyRequest;
use App\Models\CaseStudy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
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
                    ['feature' => 'case_studies']
                ),
                403
            );
        }

        $caseStudies = CaseStudy::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $caseStudies,
        ]);
    }

    public function store(
        StoreCaseStudyRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'case_studies']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'case_studies',
            $user->caseStudies()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$caseStudy = CaseStudy::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Case study created successfully.',
            'data' => $caseStudy,
        ], 201);
    }

    public function show(
        Request $request,
        CaseStudy $caseStudy
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'case_studies']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $request,
            $caseStudy
        );

        return response()->json([
            'success' => true,
            'data' => $caseStudy,
        ]);
    }

    public function update(
        UpdateCaseStudyRequest $request,
        CaseStudy $caseStudy
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'case_studies']
                ),
                403
            );
        }

        $this->ensureOwnership(
            $request,
            $caseStudy
        );

        $caseStudy->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Case study updated successfully.',
            'data' => $caseStudy->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        CaseStudy $caseStudy
    ): JsonResponse {
        $this->ensureOwnership(
            $request,
            $caseStudy
        );

        $caseStudy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Case study deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        Request $request,
        CaseStudy $caseStudy
    ): void {
        abort_unless(
            (int) $caseStudy->user_id ===
            (int) $request->user()->id,
            403,
            'You are not authorised to access this case study.'
        );
    }
}