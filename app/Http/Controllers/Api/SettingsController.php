<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioPublication;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $settings = $this->getOrCreateSettings(
            $user
        );

        $publication =
            $user->portfolioPublication;

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'full_name' =>
                        $user->name,
                    'email' =>
                        $user->email,
                    'phone' =>
                        $user->phone,
                    'profession' =>
                        $user->profession,
                    'career_goal' =>
                        $user->career_goal,
                    'bio' =>
                        $user->bio,
                ],

                'account' => [
                    'email' =>
                        $user->email,
                    'portfolio_slug' =>
                        $publication?->slug,
                    'visibility' =>
                        $publication?->visibility
                        ?? 'public',
                    'is_published' =>
                        (bool) (
                            $publication
                                ?->is_published
                            ?? false
                        ),
                ],

                'notifications' => [
                    'portfolio_views' =>
                        $settings
                            ->portfolio_views_notification,

                    'resume_downloads' =>
                        $settings
                            ->resume_downloads_notification,

                    'project_clicks' =>
                        $settings
                            ->project_clicks_notification,

                    'security_alerts' =>
                        $settings
                            ->security_alerts,

                    'product_updates' =>
                        $settings
                            ->product_updates,

                    'weekly_report' =>
                        $settings
                            ->weekly_report,
                ],

                'appearance' => [
                    'theme' =>
                        $settings->theme,

                    'compact_mode' =>
                        $settings->compact_mode,

                    'animations' =>
                        $settings->animations,
                ],

                'package_name' =>
                    $user->package_name
                    ?? 'Silver',
            ],
        ]);
    }

    public function updateProfile(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:25',
            ],

            'profession' => [
                'nullable',
                'string',
                'max:100',
            ],

            'career_goal' => [
                'nullable',
                'string',
                'max:150',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $user->update([
            'name' =>
                trim(
                    $validated['full_name']
                ),

            'email' =>
                strtolower(
                    trim(
                        $validated['email']
                    )
                ),

            'phone' =>
                $this->nullableString(
                    $validated['phone']
                    ?? null
                ),

            'profession' =>
                $this->nullableString(
                    $validated['profession']
                    ?? null
                ),

            'career_goal' =>
                $this->nullableString(
                    $validated['career_goal']
                    ?? null
                ),

            'bio' =>
                $this->nullableString(
                    $validated['bio']
                    ?? null
                ),
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Profile settings updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'fullName' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profession' =>
                        $user->profession,
                    'careerGoal' =>
                        $user->career_goal,
                    'career_goal' =>
                        $user->career_goal,
                    'bio' => $user->bio,
                    'packageName' =>
                        $user->package_name,
                    'package_name' =>
                        $user->package_name,
                ],
            ],
        ]);
    }

    public function updateAccount(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $publication =
            $user->portfolioPublication;

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'portfolio_slug' => [
                'required',
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(
                    'portfolio_publications',
                    'slug'
                )->ignore(
                    $publication?->id
                ),
            ],

            'visibility' => [
                'required',
                Rule::in([
                    'public',
                    'private',
                    'password',
                ]),
            ],
        ]);

        DB::transaction(function () use (
            $user,
            $publication,
            $validated
        ) {
            $user->update([
                'email' =>
                    strtolower(
                        trim(
                            $validated['email']
                        )
                    ),
            ]);

            PortfolioPublication::query()
                ->updateOrCreate(
                    [
                        'user_id' =>
                            $user->id,
                    ],
                    [
                        'slug' =>
                            $validated[
                                'portfolio_slug'
                            ],

                        'visibility' =>
                            $validated[
                                'visibility'
                            ],
                    ]
                );
        });

        return response()->json([
            'success' => true,
            'message' =>
                'Account settings updated successfully.',
            'data' => [
                'email' =>
                    $user->fresh()->email,

                'portfolio_slug' =>
                    $validated[
                        'portfolio_slug'
                    ],

                'visibility' =>
                    $validated[
                        'visibility'
                    ],
            ],
        ]);
    }

    public function updatePassword(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
        ]);

        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $user->password
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Current password is incorrect.',
                'errors' => [
                    'current_password' => [
                        'Current password is incorrect.',
                    ],
                ],
            ], 422);
        }

        $user->update([
            'password' =>
                $validated['new_password'],
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Password updated successfully.',
        ]);
    }

    public function updateNotifications(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'portfolio_views' => [
                'required',
                'boolean',
            ],
            'resume_downloads' => [
                'required',
                'boolean',
            ],
            'project_clicks' => [
                'required',
                'boolean',
            ],
            'security_alerts' => [
                'required',
                'boolean',
            ],
            'product_updates' => [
                'required',
                'boolean',
            ],
            'weekly_report' => [
                'required',
                'boolean',
            ],
        ]);

        $settings =
            $this->getOrCreateSettings(
                $request->user()
            );

        $settings->update([
            'portfolio_views_notification' =>
                $validated[
                    'portfolio_views'
                ],

            'resume_downloads_notification' =>
                $validated[
                    'resume_downloads'
                ],

            'project_clicks_notification' =>
                $validated[
                    'project_clicks'
                ],

            'security_alerts' =>
                $validated[
                    'security_alerts'
                ],

            'product_updates' =>
                $validated[
                    'product_updates'
                ],

            'weekly_report' =>
                $validated[
                    'weekly_report'
                ],
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Notification preferences updated.',
        ]);
    }

    public function updateAppearance(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'theme' => [
                'required',
                Rule::in([
                    'light',
                    'dark',
                    'system',
                ]),
            ],

            'compact_mode' => [
                'required',
                'boolean',
            ],

            'animations' => [
                'required',
                'boolean',
            ],
        ]);

        $settings =
            $this->getOrCreateSettings(
                $request->user()
            );

        $settings->update([
            'theme' =>
                $validated['theme'],

            'compact_mode' =>
                $validated[
                    'compact_mode'
                ],

            'animations' =>
                $validated[
                    'animations'
                ],
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Appearance preferences updated.',
            'data' => [
                'theme' =>
                    $settings->theme,
                'compact_mode' =>
                    $settings->compact_mode,
                'animations' =>
                    $settings->animations,
            ],
        ]);
    }

    public function destroyAccount(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
        ]);

        $user = $request->user();

        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $user->password
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Current password is incorrect.',
            ], 422);
        }

        DB::transaction(function () use (
            $user
        ) {
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' =>
                'Account deleted successfully.',
        ]);
    }

    private function getOrCreateSettings(
        User $user
    ): UserSetting {
        return UserSetting::query()
            ->firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'portfolio_views_notification' =>
                        true,
                    'resume_downloads_notification' =>
                        true,
                    'project_clicks_notification' =>
                        true,
                    'security_alerts' =>
                        true,
                    'product_updates' =>
                        false,
                    'weekly_report' =>
                        true,
                    'theme' => 'light',
                    'compact_mode' =>
                        false,
                    'animations' =>
                        true,
                ]
            );
    }

    private function nullableString(
        ?string $value
    ): ?string {
        $value =
            trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}