<?php

namespace App\Services;

use App\Enums\ModuleId;
use App\Enums\ModuleStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleCompletionService
{
    public function getModuleStatus(
        User $user,
        ModuleId|string $module
    ): array {
        $moduleId = $module instanceof ModuleId
            ? $module->value
            : $module;

        $moduleEnum = ModuleId::tryFrom($moduleId);

        $rule = config("module_completion.{$moduleId}");

        if (!$rule) {
            return [
                'id' => $moduleId,
                'label' => $moduleEnum?->label() ?? $moduleId,
                'status' => ModuleStatus::PENDING->value,
                'current_count' => 0,
                'required_count' => 1,
            ];
        }

        $table = $rule['table'];
        $minimum = (int) ($rule['minimum'] ?? 1);
        $userColumn = $rule['user_column'] ?? 'user_id';

        $currentCount = $this->getRecordCount(
            $user,
            $table,
            $userColumn
        );

        $status = $this->calculateStatus(
            $currentCount,
            $minimum
        );

        return [
            'id' => $moduleId,
            'label' => $moduleEnum?->label() ?? $moduleId,
            'status' => $status->value,
            'current_count' => $currentCount,
            'required_count' => $minimum,
        ];
    }

    public function getModulesWithStatus(
        User $user,
        array $requiredModules
    ): array {
        return collect($requiredModules)
            ->map(function ($module) use ($user) {
                return $this->getModuleStatus($user, $module);
            })
            ->values()
            ->all();
    }

    public function getNextSteps(
        User $user,
        array $requiredModules
    ): array {
        return collect(
            $this->getModulesWithStatus($user, $requiredModules)
        )
            ->reject(function (array $module) {
                return $module['status']
                    === ModuleStatus::COMPLETED->value;
            })
            ->values()
            ->all();
    }

    public function getCompletedModules(
        User $user,
        array $requiredModules
    ): array {
        return collect(
            $this->getModulesWithStatus($user, $requiredModules)
        )
            ->filter(function (array $module) {
                return $module['status']
                    === ModuleStatus::COMPLETED->value;
            })
            ->values()
            ->all();
    }

    private function getRecordCount(
        User $user,
        string $table,
        string $userColumn
    ): int {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        if (!Schema::hasColumn($table, $userColumn)) {
            return 0;
        }

        return DB::table($table)
            ->where($userColumn, $user->id)
            ->count();
    }

    private function calculateStatus(
        int $currentCount,
        int $minimum
    ): ModuleStatus {
        if ($currentCount === 0) {
            return ModuleStatus::PENDING;
        }

        if ($currentCount < $minimum) {
            return ModuleStatus::DRAFT;
        }

        return ModuleStatus::COMPLETED;
    }
}