<?php

namespace App\Services;

use App\Models\User;

class PackageAccessService
{
    public function packageName(
        User|string|null $userOrPackage
    ): string {
        $package = $userOrPackage instanceof User
            ? $userOrPackage->package_name
            : $userOrPackage;

        $package = ucfirst(
            strtolower(
                trim((string) $package)
            )
        );

        return in_array(
            $package,
            [
                'Silver',
                'Gold',
                'Platinum',
            ],
            true
        )
            ? $package
            : 'Silver';
    }

    public function rules(
        User|string|null $userOrPackage
    ): array {
        $package = $this->packageName(
            $userOrPackage
        );

        return config(
            "package_features.{$package}",
            []
        );
    }

    public function packageLevel(
        User|string|null $userOrPackage
    ): int {
        $package = $this->packageName(
            $userOrPackage
        );

        return (int) config(
            "package_features.hierarchy.{$package}",
            1
        );
    }

    public function hasPackage(
        User|string|null $userOrPackage,
        string $requiredPackage
    ): bool {
        return $this->packageLevel(
            $userOrPackage
        ) >= $this->packageLevel(
            $requiredPackage
        );
    }

    public function canAccessModule(
        User $user,
        string $moduleId
    ): bool {
        if (
            $this->isProfessionModule(
                $moduleId
            )
        ) {
            return $this
                ->canAccessProfessionModules(
                    $user
                );
        }

        return in_array(
            $moduleId,
            $this->rules($user)[
                'common_modules'
            ] ?? [],
            true
        );
    }

    public function canAccessProfessionModules(
        User $user
    ): bool {
        return (bool) (
            $this->rules($user)[
                'profession_modules'
            ] ?? false
        );
    }
public function nextPackage(
    User|string|null $userOrPackage
): ?string {
    return match (
        $this->packageName(
            $userOrPackage
        )
    ) {
        'Silver' => 'Gold',
        'Gold' => 'Platinum',
        'Platinum' => null,
        default => 'Gold',
    };
}
    public function isProfessionModule(
        string $moduleId
    ): bool {
        return in_array(
            $moduleId,
            config(
                'package_features.profession_module_ids',
                []
            ),
            true
        );
    }

    public function moduleRequiredPackage(
        string $moduleId
    ): string {
        if (
            $this->isProfessionModule(
                $moduleId
            )
        ) {
            return 'Gold';
        }

        return config(
            "package_features.module_requirements.{$moduleId}",
            'Silver'
        );
    }

    public function limit(
        User $user,
        string $feature
    ): ?int {
        $limits =
            $this->rules($user)[
                'limits'
            ] ?? [];

        if (
            array_key_exists(
                $feature,
                $limits
            )
        ) {
            return $limits[$feature];
        }

        if (
            $this->isProfessionModule(
                $feature
            )
        ) {
            return $limits[
                'profession_modules'
            ] ?? 0;
        }

        return 0;
    }

    public function isUnlimited(
        User $user,
        string $feature
    ): bool {
        return $this->limit(
            $user,
            $feature
        ) === null;
    }

    public function hasReachedLimit(
        User $user,
        string $feature,
        int $currentCount
    ): bool {
        $limit = $this->limit(
            $user,
            $feature
        );

        if ($limit === null) {
            return false;
        }

        return $currentCount >= $limit;
    }

    public function canUseDashboardRecommendations(
        User $user
    ): bool {
        return (bool) (
            $this->rules($user)[
                'dashboard_recommendations'
            ] ?? false
        );
    }

    public function canAccessAnalytics(
        User $user
    ): bool {
        return (bool) (
            $this->rules($user)[
                'analytics'
            ]['enabled'] ?? false
        );
    }

    public function analyticsHistoryDays(
        User $user
    ): int {
        return (int) (
            $this->rules($user)[
                'analytics'
            ]['history_days'] ?? 0
        );
    }

    public function canUseSeo(
        User $user
    ): bool {
        return (bool) (
            $this->rules($user)[
                'publish'
            ]['seo'] ?? false
        );
    }

    public function canUseVisibility(
        User $user,
        string $visibility
    ): bool {
        return (bool) (
            $this->rules($user)[
                'publish'
            ][$visibility] ?? false
        );
    }

    public function allowedThemes(
        User $user
    ): array {
        return $this->rules($user)[
            'appearance'
        ]['themes'] ?? ['light'];
    }

    public function canUseCompactMode(
        User $user
    ): bool {
        return (bool) (
            $this->rules($user)[
                'appearance'
            ]['compact_mode'] ?? false
        );
    }

    public function canControlAnimations(
        User $user
    ): bool {
        return (bool) (
            $this->rules($user)[
                'appearance'
            ]['animations'] ?? false
        );
    }

    public function upgradeResponse(
        string $message,
        string $requiredPackage,
        ?array $extra = null
    ): array {
        return array_merge(
            [
                'success' => false,
                'code' =>
                    'PACKAGE_UPGRADE_REQUIRED',
                'upgrade_required' => true,
                'required_package' =>
                    $requiredPackage,
                'message' => $message,
            ],
            $extra ?? []
        );
    }

    public function accessSummary(
        User $user
    ): array {
        $package =
            $this->packageName($user);

        return [
            'package' => $package,

            'dashboard_recommendations' =>
                $this
                    ->canUseDashboardRecommendations(
                        $user
                    ),

            'profession_modules' =>
                $this
                    ->canAccessProfessionModules(
                        $user
                    ),

            'analytics' => [
                'enabled' =>
                    $this
                        ->canAccessAnalytics(
                            $user
                        ),

                'history_days' =>
                    $this
                        ->analyticsHistoryDays(
                            $user
                        ),
            ],

            'publish' => [
                'seo' =>
                    $this->canUseSeo(
                        $user
                    ),

                'public' =>
                    $this->canUseVisibility(
                        $user,
                        'public'
                    ),

                'password' =>
                    $this->canUseVisibility(
                        $user,
                        'password'
                    ),

                'private' =>
                    $this->canUseVisibility(
                        $user,
                        'private'
                    ),
            ],

            'appearance' => [
                'themes' =>
                    $this->allowedThemes(
                        $user
                    ),

                'compact_mode' =>
                    $this
                        ->canUseCompactMode(
                            $user
                        ),

                'animations' =>
                    $this
                        ->canControlAnimations(
                            $user
                        ),
            ],
        ];
    }
}