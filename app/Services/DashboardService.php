<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
   public function __construct(
    private readonly PortfolioPriorityService $priorityService,
    private readonly ModuleCompletionService $completionService,
    private readonly PackageAccessService $packageAccessService
) {
}

    public function getDashboardData(User $user): array
    {
        $recommendationsUnlocked =
    $this->packageAccessService
        ->canUseDashboardRecommendations(
            $user
        );
        $requiredModules = $this->priorityService->generate(
            $user->profession,
            $user->career_goal
        );

        $allModules = $this->completionService
            ->getModulesWithStatus(
                $user,
                $requiredModules
            );

        $modules = collect($allModules);

        $nextSteps = $modules
            ->whereNotIn('status', ['completed'])
            ->values()
            ->all();

        $completedModules = $modules
            ->where('status', 'completed')
            ->values()
            ->all();

        $pendingCount = $modules
            ->where('status', 'pending')
            ->count();

        $draftCount = $modules
            ->where('status', 'draft')
            ->count();

        $completedCount =
            count($completedModules);

        $totalCount =
            count($allModules);

        $completionPercentage =
            $totalCount > 0
                ? (int) round(
                    ($completedCount / $totalCount)
                    * 100
                )
                : 0;

        return [
            'package_access' => [
    'recommendations_unlocked' =>
        $recommendationsUnlocked,

    'required_package' =>
        $recommendationsUnlocked
            ? null
            : 'Gold',
],
            'profession' =>
                $user->profession,

            'career_goal' =>
                $user->career_goal,

            'summary' => [
                'total' =>
                    $totalCount,

                'pending' =>
                    $pendingCount,

                'draft' =>
                    $draftCount,

                'completed' =>
                    $completedCount,

                'completion_percentage' =>
                    $completionPercentage,
            ],

            'statistics' =>
                $this->buildStatistics($user),

            'activities' =>
                $this->buildRecentActivities($user),

            'required_modules' =>
                $allModules,

            'completed_modules' =>
                $completedModules,

            'next_steps' =>
                $nextSteps,
        ];
    }

    private function buildStatistics(
        User $user
    ): array {
        $publication =
            $user->portfolioPublication;

        return [
            'projects' =>
                $user->projects()->count(),

            'certificates' =>
                $user->certificates()->count(),

            'portfolio_views' =>
                $publication
                    ? $publication
                        ->analytics()
                        ->where(
                            'event_type',
                            'portfolio_view'
                        )
                        ->count()
                    : 0,

            'resume_downloads' =>
                $publication
                    ? $publication
                        ->analytics()
                        ->where(
                            'event_type',
                            'resume_download'
                        )
                        ->count()
                    : 0,

            'project_clicks' =>
                $publication
                    ? $publication
                        ->analytics()
                        ->where(
                            'event_type',
                            'project_click'
                        )
                        ->count()
                    : 0,
        ];
    }

    private function buildRecentActivities(
        User $user
    ): array {
        $activities = collect();

        $this->addProjectActivities(
            $activities,
            $user
        );

        $this->addCertificateActivities(
            $activities,
            $user
        );

        $this->addResumeActivities(
            $activities,
            $user
        );

        $this->addSkillActivities(
            $activities,
            $user
        );

        $this->addEducationActivities(
            $activities,
            $user
        );

        $this->addExperienceActivities(
            $activities,
            $user
        );

        $this->addProfileActivity(
            $activities,
            $user
        );

        return $activities
            ->filter(
                fn (array $activity) =>
                    !empty(
                        $activity['created_at']
                    )
            )
            ->sortByDesc('created_at')
            ->take(8)
            ->values()
            ->all();
    }

    private function addProjectActivities(
        Collection $activities,
        User $user
    ): void {
        $user->projects()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->each(function ($project) use (
                $activities
            ) {
                $activities->push([
                    'id' =>
                        'project-'
                        . $project->id,

                    'type' =>
                        'project',

                    'module' =>
                        'projects',

                    'title' =>
                        'Project updated',

                    'description' =>
                        $project->title
                        ?: 'Portfolio project',

                    'reference_id' =>
                        $project->id,

                    'created_at' =>
                        optional(
                            $project->updated_at
                        )->toISOString(),
                ]);
            });
    }

    private function addCertificateActivities(
        Collection $activities,
        User $user
    ): void {
        $user->certificates()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->each(function ($certificate) use (
                $activities
            ) {
                $activities->push([
                    'id' =>
                        'certificate-'
                        . $certificate->id,

                    'type' =>
                        'certificate',

                    'module' =>
                        'certificates',

                    'title' =>
                        'Certificate updated',

                    'description' =>
                        $certificate
                            ->certificate_name
                        ?: 'Professional certificate',

                    'reference_id' =>
                        $certificate->id,

                    'created_at' =>
                        optional(
                            $certificate->updated_at
                        )->toISOString(),
                ]);
            });
    }

    private function addResumeActivities(
        Collection $activities,
        User $user
    ): void {
        $user->resumes()
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->each(function ($resume) use (
                $activities
            ) {
                $activities->push([
                    'id' =>
                        'resume-'
                        . $resume->id,

                    'type' =>
                        'resume',

                    'module' =>
                        'resume',

                    'title' =>
                        'Resume updated',

                    'description' =>
                        $resume->title
                        ?: $resume
                            ->original_file_name
                        ?: 'Professional resume',

                    'reference_id' =>
                        $resume->id,

                    'created_at' =>
                        optional(
                            $resume->updated_at
                        )->toISOString(),
                ]);
            });
    }

    private function addSkillActivities(
        Collection $activities,
        User $user
    ): void {
        $user->skills()
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->each(function ($skill) use (
                $activities
            ) {
                $activities->push([
                    'id' =>
                        'skill-'
                        . $skill->id,

                    'type' =>
                        'skill',

                    'module' =>
                        'skills',

                    'title' =>
                        'Skill updated',

                    'description' =>
                        $skill->name
                        ?: 'Portfolio skill',

                    'reference_id' =>
                        $skill->id,

                    'created_at' =>
                        optional(
                            $skill->updated_at
                        )->toISOString(),
                ]);
            });
    }

    private function addEducationActivities(
        Collection $activities,
        User $user
    ): void {
        $user->educations()
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->each(function ($education) use (
                $activities
            ) {
                $activities->push([
                    'id' =>
                        'education-'
                        . $education->id,

                    'type' =>
                        'education',

                    'module' =>
                        'education',

                    'title' =>
                        'Education updated',

                    'description' =>
                        $education->degree
                        ?: $education
                            ->institution_name
                        ?: 'Education record',

                    'reference_id' =>
                        $education->id,

                    'created_at' =>
                        optional(
                            $education->updated_at
                        )->toISOString(),
                ]);
            });
    }

    private function addExperienceActivities(
        Collection $activities,
        User $user
    ): void {
        $user->experiences()
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->each(function ($experience) use (
                $activities
            ) {
                $activities->push([
                    'id' =>
                        'experience-'
                        . $experience->id,

                    'type' =>
                        'experience',

                    'module' =>
                        'experience',

                    'title' =>
                        'Experience updated',

                    'description' =>
                        $experience
                            ->position_title
                        ?: $experience
                            ->organization_name
                        ?: 'Experience record',

                    'reference_id' =>
                        $experience->id,

                    'created_at' =>
                        optional(
                            $experience->updated_at
                        )->toISOString(),
                ]);
            });
    }

    private function addProfileActivity(
        Collection $activities,
        User $user
    ): void {
        $profile =
            $user->portfolioProfile;

        if (!$profile) {
            return;
        }

        $activities->push([
            'id' =>
                'profile-'
                . $profile->id,

            'type' =>
                'profile',

            'module' =>
                'profile',

            'title' =>
                'Profile updated',

            'description' =>
                $profile->display_name
                ?: $user->name
                ?: 'Portfolio profile',

            'reference_id' =>
                $profile->id,

            'created_at' =>
                optional(
                    $profile->updated_at
                )->toISOString(),
        ]);
    }
}