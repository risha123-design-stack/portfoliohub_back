<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;

trait ChecksPackageLimits
{
    protected function packageLimitError(
        PackageAccessService $packageAccessService,
        User $user,
        string $feature,
        int $currentCount,
        string $requiredPackage = 'Gold'
    ): ?JsonResponse {
        if (
            !$packageAccessService
                ->hasReachedLimit(
                    $user,
                    $feature,
                    $currentCount
                )
        ) {
            return null;
        }

        $limit =
            $packageAccessService->limit(
                $user,
                $feature
            );

        return response()->json(
            $packageAccessService
                ->upgradeResponse(
                    "{$user->package_name} members can add up to {$limit} {$feature}.",
                    $requiredPackage,
                    [
                        'feature' =>
                            $feature,

                        'current_count' =>
                            $currentCount,

                        'limit' =>
                            $limit,
                    ]
                ),
            403
        );
    }
    protected function packageLimitData(
    PackageAccessService $packageAccessService,
    User $user,
    string $feature,
    int $currentCount
): array {
    $limit =
        $packageAccessService->limit(
            $user,
            $feature
        );

    $limitReached =
        $limit !== null &&
        $currentCount >= $limit;

    return [
        'feature' => $feature,
        'current_count' => $currentCount,
        'limit' => $limit,
        'unlimited' => $limit === null,
        'limit_reached' =>
            $limitReached,

        'required_package' =>
            $limitReached
                ? (
                    $packageAccessService
                        ->nextPackage($user)
                    ?? 'Platinum'
                )
                : null,
    ];
}
}