<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StorePhotographyPortfolioRequest;
use App\Http\Requests\UpdatePhotographyPortfolioRequest;
use App\Models\PhotographyPortfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotographyPortfolioController extends Controller
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
                    ['feature' => 'photography_portfolio']
                ),
                403
            );
        }

        $portfolio = $request->user()
            ->photographyPortfolios()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Photography portfolio retrieved successfully.',
            'data' => $portfolio,
        ]);
    }

    public function store(
        StorePhotographyPortfolioRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'photography_portfolio']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'photography_portfolio',
            $user->photographyPortfolios()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

$portfolio = $request->user()
            ->photographyPortfolios()
            ->create(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Portfolio project created successfully.',
            'data' => $portfolio,
        ], 201);
    }

    public function show(
        PhotographyPortfolio $photographyPortfolio,
        Request $request
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'photography_portfolio']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $photographyPortfolio,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Portfolio retrieved successfully.',
            'data' => $photographyPortfolio,
        ]);
    }

    public function update(
        UpdatePhotographyPortfolioRequest $request,
        PhotographyPortfolio $photographyPortfolio
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'photography_portfolio']
                ),
                403
            );
        }


        $this->ensureOwnership(
            $photographyPortfolio,
            $request
        );

        $photographyPortfolio->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Portfolio updated successfully.',
            'data' => $photographyPortfolio->fresh(),
        ]);
    }

    public function destroy(
        PhotographyPortfolio $photographyPortfolio,
        Request $request
    ): JsonResponse {

        $this->ensureOwnership(
            $photographyPortfolio,
            $request
        );

        $photographyPortfolio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio deleted successfully.',
        ]);
    }

    private function ensureOwnership(
        PhotographyPortfolio $photographyPortfolio,
        Request $request
    ): void {

        if (
            (int)$photographyPortfolio->user_id !==
            (int)$request->user()->id
        ) {

            abort(
                403,
                'Unauthorized.'
            );

        }

    }
}