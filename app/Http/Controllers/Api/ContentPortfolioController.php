<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksPackageLimits;
use App\Services\PackageAccessService;
use App\Http\Requests\StoreContentPortfolioRequest;
use App\Http\Requests\UpdateContentPortfolioRequest;
use App\Models\ContentPortfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ContentPortfolioController extends Controller
{
    use ChecksPackageLimits;

    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    /**
     * Display all content portfolio records
     * belonging to the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'content_portfolios']
                ),
                403
            );
        }

        try {
            $userId = auth('api')->id();

            $contentPortfolios = ContentPortfolio::query()
                ->where('user_id', $userId)
                ->latest()
                ->get()
                ->map(
                    fn (ContentPortfolio $contentPortfolio) =>
                        $this->transform($contentPortfolio)
                );

            return response()->json([
                'success' => true,
                'message' =>
                    'Content portfolio records retrieved successfully.',
                'data' => $contentPortfolios,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to retrieve content portfolio records.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Store a newly created content portfolio record.
     */
    public function store(
        StoreContentPortfolioRequest $request
    ): JsonResponse {
        $user = $request->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'content_portfolios']
                ),
                403
            );
        }

        $requiredPackage = $this->packageAccessService
            ->nextPackage($user) ?? 'Platinum';

        $limitError = $this->packageLimitError(
            $this->packageAccessService,
            $user,
            'content_portfolios',
            $user->contentPortfolios()->count(),
            $requiredPackage
        );

        if ($limitError) {
            return $limitError;
        }

try {
            $contentPortfolio = DB::transaction(
                function () use ($request) {
                    return ContentPortfolio::create([
                        'user_id' => auth('api')->id(),
                        ...$request->validated(),
                    ]);
                }
            );

            $contentPortfolio->refresh();

            return response()->json([
                'success' => true,
                'message' =>
                    'Content portfolio record created successfully.',
                'data' =>
                    $this->transform($contentPortfolio),
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to create the content portfolio record.',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Display a specific content portfolio record.
     */
    public function show(int $id): JsonResponse
    {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'content_portfolios']
                ),
                403
            );
        }

        try {
            $contentPortfolio =
                $this->findOwnedContentPortfolio($id);

            if (!$contentPortfolio) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Content portfolio record not found.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' =>
                    'Content portfolio record retrieved successfully.',
                'data' =>
                    $this->transform($contentPortfolio),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to retrieve the content portfolio record.',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update the specified content portfolio record.
     */
    public function update(
        UpdateContentPortfolioRequest $request,
        int $id
    ): JsonResponse {
        $user = auth('api')->user() ?? auth()->user();

        if (!$this->packageAccessService->canAccessProfessionModules($user)) {
            return response()->json(
                $this->packageAccessService->upgradeResponse(
                    'Profession-based modules are available from Gold.',
                    'Gold',
                    ['feature' => 'content_portfolios']
                ),
                403
            );
        }

        try {
            $contentPortfolio =
                $this->findOwnedContentPortfolio($id);

            if (!$contentPortfolio) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Content portfolio record not found.',
                    'data' => null,
                ], 404);
            }

            DB::transaction(
                function () use (
                    $contentPortfolio,
                    $request
                ) {
                    $contentPortfolio->update(
                        $request->validated()
                    );
                }
            );

            $contentPortfolio->refresh();

            return response()->json([
                'success' => true,
                'message' =>
                    'Content portfolio record updated successfully.',
                'data' =>
                    $this->transform($contentPortfolio),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to update the content portfolio record.',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified content portfolio record.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $contentPortfolio =
                $this->findOwnedContentPortfolio($id);

            if (!$contentPortfolio) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Content portfolio record not found.',
                    'data' => null,
                ], 404);
            }

            DB::transaction(
                function () use ($contentPortfolio) {
                    $contentPortfolio->delete();
                }
            );

            return response()->json([
                'success' => true,
                'message' =>
                    'Content portfolio record deleted successfully.',
                'data' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Failed to delete the content portfolio record.',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Find a record belonging to the authenticated user.
     */
    private function findOwnedContentPortfolio(
        int $id
    ): ?ContentPortfolio {
        return ContentPortfolio::query()
            ->where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();
    }

    /**
     * Transform database fields into the frontend format.
     *
     * @return array<string, mixed>
     */
    private function transform(
        ContentPortfolio $contentPortfolio
    ): array {
        return [
            'id' => $contentPortfolio->id,

            'title' =>
                $contentPortfolio->title,

            'platform' =>
                $contentPortfolio->platform,

            'contentType' =>
                $contentPortfolio->content_type,

            'category' =>
                $contentPortfolio->category,

            'publishDate' =>
                $contentPortfolio->publish_date
                    ? $contentPortfolio
                        ->publish_date
                        ->format('Y-m-d')
                    : null,

            'status' =>
                $contentPortfolio->status,

            'contentUrl' =>
                $contentPortfolio->content_url,

            'thumbnailUrl' =>
                $contentPortfolio->thumbnail_url,

            'views' =>
                (int) ($contentPortfolio->views ?? 0),

            'likes' =>
                (int) ($contentPortfolio->likes ?? 0),

            'description' =>
                $contentPortfolio->description,

            'createdAt' =>
                $contentPortfolio->created_at
                    ? $contentPortfolio
                        ->created_at
                        ->toISOString()
                    : null,

            'updatedAt' =>
                $contentPortfolio->updated_at
                    ? $contentPortfolio
                        ->updated_at
                        ->toISOString()
                    : null,
        ];
    }
}