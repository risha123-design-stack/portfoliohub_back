<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicProject;
use App\Models\CameraEquipment;
use App\Models\CaseStudy;
use App\Models\ClientProject;
use App\Models\ClientTestimonial;
use App\Models\ClinicalExperience;
use App\Models\CodingProfile;
use App\Models\ContentPortfolio;
use App\Models\DesignTool;
use App\Models\DeveloperProject;
use App\Models\MedicalCertification;
use App\Models\PhotographyPortfolio;
use App\Models\SocialMediaProfile;
use App\Models\StudentCertification;
use App\Models\TeachingSubject;
use App\Models\TechStack;
use App\Models\Workshop;
use App\Services\PackageAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProfessionModuleOverviewController extends Controller
{
    public function __construct(
        private readonly PackageAccessService $packageAccessService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $profession = trim(
            (string) $user->profession
        );

        $professionModulesUnlocked =
            $this->packageAccessService
                ->canAccessProfessionModules($user);

        /*
         * Gold and Platinum can load the real module statistics.
         * Silver receives the module definitions as locked data
         * without querying profession-module tables.
         */
        $modules = $this->modulesForProfession(
            $profession,
            $user->id,
            $professionModulesUnlocked
        );

        return response()->json([
            'success' => true,
            'profession' => $profession,

            'package_access' => [
                'locked' =>
                    !$professionModulesUnlocked,

                'required_package' =>
                    $professionModulesUnlocked
                        ? null
                        : 'Gold',
            ],

            'modules' => $modules,
        ]);
    }

    private function modulesForProfession(
        string $profession,
        int $userId,
        bool $unlocked
    ): array {
        return match ($profession) {
            'Software Developer' => [
                'tech-stack' =>
                    $this->moduleData(
                        TechStack::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'coding-profiles' =>
                    $this->moduleData(
                        CodingProfile::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'developer-projects' =>
                    $this->moduleData(
                        DeveloperProject::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'Teacher' => [
                'teaching-subjects' =>
                    $this->moduleData(
                        TeachingSubject::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'workshops' =>
                    $this->moduleData(
                        Workshop::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'UI/UX Designer' => [
                'design-tools' =>
                    $this->moduleData(
                        DesignTool::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'case-studies' =>
                    $this->moduleData(
                        CaseStudy::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'Photographer' => [
                'camera-equipment' =>
                    $this->moduleData(
                        CameraEquipment::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'photography-portfolio' =>
                    $this->moduleData(
                        PhotographyPortfolio::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'Healthcare Professional' => [
                'medical-certifications' =>
                    $this->moduleData(
                        MedicalCertification::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'clinical-experience' =>
                    $this->moduleData(
                        ClinicalExperience::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'Freelancer' => [
                'client-projects' =>
                    $this->moduleData(
                        ClientProject::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'client-testimonials' =>
                    $this->moduleData(
                        ClientTestimonial::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'Student' => [
                'academic-projects' =>
                    $this->moduleData(
                        AcademicProject::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'student-certifications' =>
                    $this->moduleData(
                        StudentCertification::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            'Content Creator' => [
                'social-media-profiles' =>
                    $this->moduleData(
                        SocialMediaProfile::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),

                'content-portfolio' =>
                    $this->moduleData(
                        ContentPortfolio::query()
                            ->where('user_id', $userId),
                        $unlocked
                    ),
            ],

            default => [],
        };
    }

    private function moduleData(
        Builder $query,
        bool $unlocked
    ): array {
        if (!$unlocked) {
            return [
                'itemCount' => 0,
                'status' => 'Locked',
                'locked' => true,
                'required_package' => 'Gold',
            ];
        }

        $count = (clone $query)->count();

        if ($count === 0) {
            return [
                'itemCount' => 0,
                'status' => 'Not Started',
                'locked' => false,
                'required_package' => null,
            ];
        }

        $status = $this->resolveStatus(
            $query,
            $count
        );

        return [
            'itemCount' => $count,
            'status' => $status,
            'locked' => false,
            'required_package' => null,
        ];
    }

    private function resolveStatus(
        Builder $query,
        int $count
    ): string {
        $table = $query
            ->getModel()
            ->getTable();

        /*
         * Not every profession-module table contains a status
         * column. Only query it when the column actually exists.
         */
        if (!Schema::hasColumn($table, 'status')) {
            return $count > 0
                ? 'Completed'
                : 'Not Started';
        }

        $completed = (clone $query)
            ->whereIn('status', [
                'Completed',
                'completed',
            ])
            ->count();

        if ($completed === $count) {
            return 'Completed';
        }

        $inProgress = (clone $query)
            ->whereIn('status', [
                'In Progress',
                'in_progress',
                'In progress',
                'Draft',
                'draft',
            ])
            ->count();

        if ($inProgress > 0 || $completed > 0) {
            return 'In Progress';
        }

        return 'Draft';
    }
}