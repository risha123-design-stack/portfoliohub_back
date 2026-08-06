<?php

namespace App\Services;

use App\Enums\ModuleId;
use InvalidArgumentException;

class PortfolioPriorityService
{
    /**
     * Generate the ordered required-module list for a user.
     *
     * @return array<int, ModuleId>
     */
    public function generate(
        string $profession,
        string $careerGoal
    ): array {
        $goalModules = $this->getGoalModules($careerGoal);
        $professionRules = $this->getProfessionRules($profession);

        $modules = $this->applyProfessionRules(
            $goalModules,
            $professionRules
        );

        return $this->removeDuplicates($modules);
    }

    /**
     * Generate frontend-friendly module details.
     *
     * @return array<int, array{id: string, label: string}>
     */
    public function generateDetails(
        string $profession,
        string $careerGoal
    ): array {
        return array_map(
            static fn (ModuleId $module): array => [
                'id' => $module->value,
                'label' => $module->label(),
            ],
            $this->generate($profession, $careerGoal)
        );
    }

    /**
     * Return base modules configured for the selected career goal.
     *
     * @return array<int, ModuleId>
     */
    private function getGoalModules(string $careerGoal): array
    {
        $goals = config('portfolio_priorities.goals', []);

        if (!array_key_exists($careerGoal, $goals)) {
            throw new InvalidArgumentException(
                "Unsupported career goal: {$careerGoal}"
            );
        }

        return $this->validateModules(
            $goals[$careerGoal],
            "career goal '{$careerGoal}'"
        );
    }

    /**
     * Return insertion rules configured for the selected profession.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getProfessionRules(string $profession): array
    {
        $professions = config(
            'portfolio_priorities.professions',
            []
        );

        if (!array_key_exists($profession, $professions)) {
            throw new InvalidArgumentException(
                "Unsupported profession: {$profession}"
            );
        }

        $rules = $professions[$profession];

        if (!is_array($rules)) {
            throw new InvalidArgumentException(
                "Invalid priority configuration for profession: {$profession}"
            );
        }

        return $rules;
    }

    /**
     * Insert profession modules into the goal-based module list.
     *
     * @param array<int, ModuleId> $modules
     * @param array<int, array<string, mixed>> $rules
     * @return array<int, ModuleId>
     */
    private function applyProfessionRules(
        array $modules,
        array $rules
    ): array {
        foreach ($rules as $rule) {
            $modules = $this->applyRule($modules, $rule);
        }

        return $modules;
    }

    /**
     * Apply one before/after insertion rule.
     *
     * @param array<int, ModuleId> $modules
     * @param array<string, mixed> $rule
     * @return array<int, ModuleId>
     */
    private function applyRule(array $modules, array $rule): array
    {
        $placement = $rule['placement'] ?? 'after';
        $target = $rule['target'] ?? null;
        $professionModules = $rule['modules'] ?? [];

        if (!in_array($placement, ['before', 'after'], true)) {
            throw new InvalidArgumentException(
                "Invalid module placement: {$placement}"
            );
        }

        if (!$target instanceof ModuleId) {
            throw new InvalidArgumentException(
                'Profession rule target must be a ModuleId.'
            );
        }

        $professionModules = $this->validateModules(
            $professionModules,
            "profession rule targeting '{$target->value}'"
        );

        $targetIndex = $this->findModuleIndex($modules, $target);

        /*
         * When the target module is not part of the goal list,
         * append the profession modules at the end.
         */
        if ($targetIndex === null) {
            return [
                ...$modules,
                ...$professionModules,
            ];
        }

        $insertIndex = $placement === 'before'
            ? $targetIndex
            : $targetIndex + 1;

        array_splice(
            $modules,
            $insertIndex,
            0,
            $professionModules
        );

        return $modules;
    }

    /**
     * Find a module's position in the ordered list.
     *
     * @param array<int, ModuleId> $modules
     */
    private function findModuleIndex(
        array $modules,
        ModuleId $target
    ): ?int {
        foreach ($modules as $index => $module) {
            if ($module === $target) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Remove duplicate module IDs while preserving the original order.
     *
     * @param array<int, ModuleId> $modules
     * @return array<int, ModuleId>
     */
    private function removeDuplicates(array $modules): array
    {
        $uniqueModules = [];
        $seen = [];

        foreach ($modules as $module) {
            if (isset($seen[$module->value])) {
                continue;
            }

            $seen[$module->value] = true;
            $uniqueModules[] = $module;
        }

        return $uniqueModules;
    }

    /**
     * Ensure all configured values are valid ModuleId enum instances.
     *
     * @param mixed $modules
     * @return array<int, ModuleId>
     */
    private function validateModules(
        mixed $modules,
        string $context
    ): array {
        if (!is_array($modules)) {
            throw new InvalidArgumentException(
                "Configured modules for {$context} must be an array."
            );
        }

        foreach ($modules as $module) {
            if (!$module instanceof ModuleId) {
                throw new InvalidArgumentException(
                    "Invalid module configured for {$context}."
                );
            }
        }

        return array_values($modules);
    }
}