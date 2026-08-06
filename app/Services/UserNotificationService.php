<?php

namespace App\Services;

use App\Models\PortfolioPublication;
use App\Models\Project;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

class UserNotificationService
{
    public function __construct(
        private readonly PackageAccessService
        $packageAccessService
    ) {
    }

    public function createFromAnalytics(
        PortfolioPublication $publication,
        string $eventType,
        ?string $target = null,
        ?string $visitorId = null,
        array $metadata = []
    ): ?UserNotification {
        $user = $publication->user;

        if (!$user) {
            return null;
        }

        /*
         * Silver users do not have access
         * to activity notifications.
         */
        if (
            !$this->packageAccessService
                ->canAccessNotifications($user)
        ) {
            return null;
        }

        $settings = $user->settings;

        if (
            !$this->notificationEnabled(
                $settings,
                $eventType
            )
        ) {
            return null;
        }

        if (
            $this->isDuplicate(
                $user->id,
                $eventType,
                $target,
                $visitorId
            )
        ) {
            return null;
        }

        $content = $this->buildContent(
            $publication,
            $eventType,
            $target
        );

        if (!$content) {
            return null;
        }

        return UserNotification::create([
            'user_id' =>
                $user->id,

            'type' =>
                $eventType,

            'title' =>
                $content['title'],

            'message' =>
                $content['message'],

            'data' => [
                'portfolio_publication_id' =>
                    $publication->id,

                'portfolio_slug' =>
                    $publication->slug,

                'event_target' =>
                    $target,

                'visitor_id' =>
                    $visitorId,

                ...$metadata,
            ],

            'is_read' =>
                false,

            'read_at' =>
                null,
        ]);
    }

    private function notificationEnabled(
        $settings,
        string $eventType
    ): bool {
        if (!$settings) {
            return true;
        }

        return match ($eventType) {
            'portfolio_view' =>
                (bool) $settings
                    ->portfolio_views_notification,

            'resume_view',
            'resume_download' =>
                (bool) $settings
                    ->resume_downloads_notification,

            'project_click' =>
                (bool) $settings
                    ->project_clicks_notification,

            default =>
                false,
        };
    }

    private function isDuplicate(
        int $userId,
        string $eventType,
        ?string $target,
        ?string $visitorId
    ): bool {
        $minutes = match ($eventType) {
            'portfolio_view' =>
                30,

            'project_click' =>
                10,

            'resume_view' =>
                10,

            'resume_download' =>
                2,

            default =>
                5,
        };

        $query = UserNotification::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'type',
                $eventType
            )
            ->where(
                'created_at',
                '>=',
                Carbon::now()
                    ->subMinutes($minutes)
            );

        if ($target !== null) {
            $query->where(
                'data->event_target',
                (string) $target
            );
        }

        if ($visitorId !== null) {
            $query->where(
                'data->visitor_id',
                $visitorId
            );
        }

        return $query->exists();
    }

    private function buildContent(
        PortfolioPublication $publication,
        string $eventType,
        ?string $target
    ): ?array {
        return match ($eventType) {
            'portfolio_view' => [
                'title' =>
                    'New portfolio view',

                'message' =>
                    'Someone viewed your public portfolio.',
            ],

            'resume_view' => [
                'title' =>
                    'Resume viewed',

                'message' =>
                    'A visitor opened your resume.',
            ],

            'resume_download' => [
                'title' =>
                    'Resume downloaded',

                'message' =>
                    'A visitor downloaded your resume.',
            ],

            'project_click' =>
                $this->projectClickContent(
                    $publication,
                    $target
                ),

            default =>
                null,
        };
    }

    private function projectClickContent(
        PortfolioPublication $publication,
        ?string $target
    ): array {
        $projectTitle =
            'one of your projects';

        if ($target) {
            $project = Project::query()
                ->where(
                    'user_id',
                    $publication->user_id
                )
                ->whereKey(
                    (int) $target
                )
                ->first();

            if ($project?->title) {
                $projectTitle =
                    '"' .
                    $project->title .
                    '"';
            }
        }

        return [
            'title' =>
                'Project interaction',

            'message' =>
                "A visitor opened {$projectTitle}.",
        ];
    }
}