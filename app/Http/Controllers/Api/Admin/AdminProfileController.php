<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    /**
     * Return the authenticated administrator profile.
     */
    public function show(
        Request $request
    ): JsonResponse {
        $admin = $request->user();

        return response()->json([
            'success' => true,

            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'role' => $admin->role,
                'is_active' =>
                    (bool) $admin->is_active,

                'created_at' =>
                    $admin->created_at
                        ?->toISOString(),

                'updated_at' =>
                    $admin->updated_at
                        ?->toISOString(),

                'password_changed_at' =>
                    $admin->password_changed_at
                        ?->toISOString(),
            ],
        ]);
    }

    /**
     * Update the authenticated administrator profile.
     */
    public function update(
        Request $request
    ): JsonResponse {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],

            'email' => [
                'required',
                'email',
                'max:150',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($admin->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
        ]);

        $admin->update([
            'name' => trim(
                $validated['name']
            ),

            'email' => strtolower(
                trim($validated['email'])
            ),

            'phone' => isset(
                $validated['phone']
            )
                ? trim(
                    $validated['phone']
                )
                : null,
        ]);

        $freshAdmin = $admin->fresh();

        return response()->json([
            'success' => true,

            'message' =>
                'Admin profile updated successfully.',

            'data' => [
                'id' => $freshAdmin->id,
                'name' => $freshAdmin->name,
                'email' => $freshAdmin->email,
                'phone' => $freshAdmin->phone,
                'role' => $freshAdmin->role,
                'is_active' =>
                    (bool) $freshAdmin->is_active,

                'created_at' =>
                    $freshAdmin->created_at
                        ?->toISOString(),

                'updated_at' =>
                    $freshAdmin->updated_at
                        ?->toISOString(),

                'password_changed_at' =>
                    $freshAdmin
                        ->password_changed_at
                        ?->toISOString(),
            ],
        ]);
    }

    /**
     * Change the authenticated administrator password.
     */
    public function changePassword(
        Request $request
    ): JsonResponse {
        $admin = $request->user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'confirmed',

                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.confirmed' =>
                'The new password confirmation does not match.',
        ]);

        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $admin->password
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'The current password is incorrect.',

                'errors' => [
                    'current_password' => [
                        'The current password is incorrect.',
                    ],
                ],
            ], 422);
        }

        if (
            Hash::check(
                $validated['password'],
                $admin->password
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'The new password must be different from the current password.',

                'errors' => [
                    'password' => [
                        'The new password must be different from the current password.',
                    ],
                ],
            ], 422);
        }

        $admin->update([
            'password' =>
                $validated['password'],

            'password_changed_at' =>
                now(),
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Password changed successfully.',
        ]);
    }
}
