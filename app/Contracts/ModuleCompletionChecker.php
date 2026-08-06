<?php

namespace App\Contracts;

use App\Models\User;

interface ModuleCompletionChecker
{
    /**
     * Determine whether the module is completed.
     */
    public function isCompleted(User $user): bool;

    /**
     * Return completion percentage (0-100).
     * Default implementations can return either 0 or 100,
     * while complex modules can return partial progress.
     */
    public function percentage(User $user): int;
}