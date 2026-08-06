<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'package' => [
                'nullable',
                Rule::in([
                    'Silver',
                    'Gold',
                    'Platinum',
                ]),
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'profession' => [
                'nullable',
                'string',
                'max:100',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:5',
                'max:100',
            ],
        ]);

        $query = User::query()
            ->where('role', '!=', 'admin')
            ->withCount([
                'projects',
                'skills',
                'certificates',
            ])
            ->with([
                'portfolioPublication:id,user_id,slug,is_published,visibility,completion_percentage',
            ]);

        if (!empty($validated['search'])) {
            $search = trim(
                $validated['search']
            );

            $query->where(
                function ($builder) use (
                    $search
                ): void {
                    $builder
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (!empty($validated['package'])) {
            $query->where(
                'package_name',
                $validated['package']
            );
        }

        if (!empty($validated['profession'])) {
            $query->where(
                'profession',
                $validated['profession']
            );
        }

        if (!empty($validated['status'])) {
            $query->where(
                'is_active',
                $validated['status'] ===
                    'active'
            );
        }

        $users = $query
            ->latest()
            ->paginate(
                $validated['per_page'] ?? 10
            );

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function show(
        User $user
    ): JsonResponse {
        $this->ensureNormalUser($user);

        $user->loadCount([
            'projects',
            'skills',
            'certificates',
            'educations',
            'experiences',
            'achievements',
            'languages',
            'resumes',
            'socialLinks',
        ]);

        $user->load([
            'portfolioPublication',
            'payments' => function (
                $query
            ): void {
                $query
                    ->latest()
                    ->limit(10);
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function updatePackage(
        Request $request,
        User $user
    ): JsonResponse {
        $this->ensureNormalUser($user);

        $validated = $request->validate([
            'package_name' => [
                'required',
                Rule::in([
                    'Silver',
                    'Gold',
                    'Platinum',
                ]),
            ],
        ]);

        $user->update([
            'package_name' =>
                $validated['package_name'],
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                "User package changed to {$user->package_name}.",

            'data' => $user->fresh(),
        ]);
    }

    public function updateStatus(
        Request $request,
        User $user
    ): JsonResponse {
        $this->ensureNormalUser($user);

        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $user->update([
            'is_active' =>
                $validated['is_active'],
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                $user->is_active
                    ? 'User account activated.'
                    : 'User account deactivated.',

            'data' => $user->fresh(),
        ]);
    }

    public function destroy(
        User $user
    ): JsonResponse {
        $this->ensureNormalUser($user);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'User deleted successfully.',
        ]);
    }

    private function ensureNormalUser(
        User $user
    ): void {
        abort_if(
            $user->role === 'admin',
            403,
            'Administrator accounts cannot be managed from this endpoint.'
        );
    }
}
