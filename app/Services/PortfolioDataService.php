<?php

namespace App\Services;

use App\Http\Resources\PortfolioPreviewResource;
use App\Models\User;

class PortfolioDataService
{
    public function buildForUser(
        User $user,
        $publication = null
    ): array {
        $user->load([
            'portfolioProfile',
            'portfolioAbout',
            'contactInformation',
            'projects',
            'educations',
            'experiences',
            'skills',
            'certificates',
            'achievements',
            'languages',
            'resumes',
            'socialLinks',
        ]);

        $this->loadProfessionModules($user);

        $data = (
            new PortfolioPreviewResource($user)
        )->resolve(request());

        $data['slug'] =
            $publication?->slug;

        $data['publishedCompletion'] =
            (int) (
                $publication?->completion_percentage
                ?? 0
            );

        $data['seo'] = [
            'title' =>
                $this->cleanString(
                    $publication?->seo_title
                ),

            'description' =>
                $this->cleanString(
                    $publication?->seo_description
                ),

            'keywords' =>
                $this->cleanString(
                    $publication?->seo_keywords
                ),

            'allow_search_engines' =>
                (bool) (
                    $publication?->allow_search_engines
                    ?? true
                ),
        ];

        if (
            $publication &&
            is_array($publication->enabled_modules) &&
            count($publication->enabled_modules) > 0
        ) {
            $data['enabledModules'] = array_merge(
                $data['enabledModules'] ?? [],
                $publication->enabled_modules
            );
        }

        if (
            $publication &&
            is_array($publication->selected_template) &&
            count($publication->selected_template) > 0
        ) {
            $data['template'] =
                $publication->selected_template;
        }

        return $data;
    }

    private function loadProfessionModules(
        User $user
    ): void {
        $relations = match ($user->profession) {
            'Software Developer' => [
                'techStacks',
                'codingProfiles',
                'developerProjects',
            ],

            'Teacher' => [
                'teachingSubjects',
                'workshops',
            ],

            'UI/UX Designer' => [
                'designTools',
                'caseStudies',
            ],

            'Photographer' => [
                'cameraEquipment',
                'photographyPortfolios',
            ],

            'Healthcare Professional' => [
                'medicalCertifications',
                'clinicalExperiences',
            ],

            'Freelancer' => [
                'clientProjects',
                'clientTestimonials',
            ],

            'Student' => [
                'academicProjects',
                'studentCertifications',
            ],

            'Content Creator' => [
                'contentPortfolios',
                'socialMediaProfiles',
            ],

            default => [],
        };

        if (count($relations) > 0) {
            $user->load($relations);
        }
    }

    private function cleanString(
        ?string $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}
