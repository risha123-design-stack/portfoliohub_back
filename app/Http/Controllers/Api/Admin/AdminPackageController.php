<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminPackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packageNames = [
            'Silver',
            'Gold',
            'Platinum',
        ];

        $prices = [
            'Silver' => 0,
            'Gold' => 2500,
            'Platinum' => 5000,
        ];

        $packages = collect(
            $packageNames
        )->map(
            function (
                string $packageName
            ) use ($prices): array {
                $settings = config(
                    "package_access.{$packageName}",
                    []
                );

                return [
                    'name' => $packageName,

                    'price' =>
                        $prices[$packageName],

                    'currency' => 'LKR',

                    'user_count' =>
                        User::query()
                            ->where(
                                'role',
                                '!=',
                                'admin'
                            )
                            ->where(
                                'package_name',
                                $packageName
                            )
                            ->count(),

                    'common_modules' =>
                        $settings[
                            'common_modules'
                        ] ?? [],

                    'profession_modules' =>
                        (bool) (
                            $settings[
                                'profession_modules'
                            ] ?? false
                        ),

                    'limits' =>
                        $settings['limits'] ?? [],

                    'analytics' =>
                        $settings[
                            'analytics'
                        ] ?? [],

                    'publish' =>
                        $settings[
                            'publish'
                        ] ?? [],
                ];
            }
        )->values();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }
}
