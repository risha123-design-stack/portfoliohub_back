<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PortfolioPublication;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $userQuery = User::query()
            ->where('role', '!=', 'admin');

        $recentUsers = User::query()
            ->where('role', '!=', 'admin')
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'name',
                'email',
                'profession',
                'package_name',
                'is_active',
                'created_at',
            ]);

        $recentPayments = Payment::query()
            ->with([
                'user:id,name,email',
            ])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,

            'data' => [
                'summary' => [
                    'total_users' =>
                        (clone $userQuery)->count(),

                    'active_users' =>
                        (clone $userQuery)
                            ->where(
                                'is_active',
                                true
                            )
                            ->count(),

                    'silver_users' =>
                        (clone $userQuery)
                            ->where(
                                'package_name',
                                'Silver'
                            )
                            ->count(),

                    'gold_users' =>
                        (clone $userQuery)
                            ->where(
                                'package_name',
                                'Gold'
                            )
                            ->count(),

                    'platinum_users' =>
                        (clone $userQuery)
                            ->where(
                                'package_name',
                                'Platinum'
                            )
                            ->count(),

                    'published_portfolios' =>
                        PortfolioPublication::query()
                            ->where(
                                'is_published',
                                true
                            )
                            ->count(),

                    'completed_payments' =>
                        Payment::query()
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count(),

                    'pending_payments' =>
                        Payment::query()
                            ->where(
                                'status',
                                'pending'
                            )
                            ->count(),

                    'total_revenue' =>
                        (float) Payment::query()
                            ->where(
                                'status',
                                'completed'
                            )
                            ->sum('amount'),
                ],

                'recent_users' =>
                    $recentUsers,

                'recent_payments' =>
                    $recentPayments,
            ],
        ]);
    }
}
