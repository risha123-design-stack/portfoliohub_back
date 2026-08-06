<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\PackageAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function __construct(
        private readonly PackageAccessService
        $packageAccessService
    ) {
    }

    /**
     * Return recent notifications
     * for the authenticated user.
     */
    public function index(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        if (
            !$this->packageAccessService
                ->canAccessNotifications($user)
        ) {
            return response()->json(
                $this->packageAccessService
                    ->upgradeResponse(
                        'Activity notifications are available from Gold.',
                        'Gold',
                        [
                            'feature' =>
                                'notifications',
                        ]
                    ),
                403
            );
        }

        $validated = $request->validate([
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $limit = $validated['limit'] ?? 10;

        $notifications = $user
            ->userNotifications()
            ->limit($limit)
            ->get()
            ->map(
                fn (
                    UserNotification $notification
                ): array =>
                    $this->formatNotification(
                        $notification
                    )
            );

        return response()->json([
            'success' => true,

            'data' => [
                'notifications' =>
                    $notifications,

                'unread_count' =>
                    $user
                        ->userNotifications()
                        ->where(
                            'is_read',
                            false
                        )
                        ->count(),
            ],
        ]);
    }

    /**
     * Return unread notification count.
     */
    public function unreadCount(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        if (
            !$this->packageAccessService
                ->canAccessNotifications($user)
        ) {
            return response()->json([
                'success' => true,

                'data' => [
                    'unread_count' => 0,
                    'locked' => true,
                    'required_package' =>
                        'Gold',
                ],
            ]);
        }

        return response()->json([
            'success' => true,

            'data' => [
                'unread_count' =>
                    $user
                        ->userNotifications()
                        ->where(
                            'is_read',
                            false
                        )
                        ->count(),

                'locked' => false,
            ],
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function markAsRead(
        Request $request,
        UserNotification $notification
    ): JsonResponse {
        $user = $request->user();

        $this->ensureAccess($user);

        $this->authorizeOwnership(
            $notification,
            $user->id
        );

        $notification->markAsRead();

        return response()->json([
            'success' => true,

            'message' =>
                'Notification marked as read.',

            'data' =>
                $this->formatNotification(
                    $notification->fresh()
                ),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $this->ensureAccess($user);

        $user->userNotifications()
            ->where(
                'is_read',
                false
            )
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,

            'message' =>
                'All notifications marked as read.',

            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }

    /**
     * Delete one notification.
     */
    public function destroy(
        Request $request,
        UserNotification $notification
    ): JsonResponse {
        $user = $request->user();

        $this->ensureAccess($user);

        $this->authorizeOwnership(
            $notification,
            $user->id
        );

        $notification->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Notification deleted successfully.',
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function clearRead(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $this->ensureAccess($user);

        $deletedCount =
            $user->userNotifications()
                ->where(
                    'is_read',
                    true
                )
                ->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Read notifications cleared successfully.',

            'data' => [
                'deleted_count' =>
                    $deletedCount,
            ],
        ]);
    }

    private function ensureAccess(
        $user
    ): void {
        abort_unless(
            $this->packageAccessService
                ->canAccessNotifications($user),
            403,
            'Activity notifications are available from Gold.'
        );
    }

    private function authorizeOwnership(
        UserNotification $notification,
        int $userId
    ): void {
        abort_unless(
            (int) $notification->user_id ===
                (int) $userId,
            403,
            'You are not authorized to access this notification.'
        );
    }

    private function formatNotification(
        UserNotification $notification
    ): array {
        return [
            'id' =>
                $notification->id,

            'type' =>
                $notification->type,

            'title' =>
                $notification->title,

            'message' =>
                $notification->message,

            'data' =>
                $notification->data ?? [],

            'is_read' =>
                (bool) $notification->is_read,

            'read_at' =>
                $notification->read_at
                    ?->toISOString(),

            'created_at' =>
                $notification->created_at
                    ?->toISOString(),

            'time_ago' =>
                $notification->created_at
                    ?->diffForHumans(),
        ];
    }
}