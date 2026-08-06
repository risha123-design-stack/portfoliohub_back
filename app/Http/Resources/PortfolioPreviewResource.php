<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class PortfolioPreviewResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        $profile = $this->portfolioProfile;
        $about = $this->portfolioAbout;
        $contacts = collect(
            $this->contactInformation
        );

        $primaryResume = $this->resumes
            ->firstWhere('is_primary', true)
            ?? $this->resumes->first();

        return [
            'user' => [
                'id' => $this->id,
                'name' =>
                    $this->cleanString($this->name),
                'email' =>
                    $this->cleanString($this->email),
                'profession' =>
                    $this->cleanString(
                        $this->profession
                    ),
                'career_goal' =>
                    $this->cleanString(
                        $this->career_goal
                    ),
                'package_name' =>
                    $this->cleanString(
                        $this->package_name
                    ),
            ],

            'profile' => [
                'fullName' =>
                    $this->getDisplayName($profile),

                'profession' =>
                    $this->cleanString(
                        $profile?->professional_title
                    )
                    ?: $this->cleanString(
                        $profile?->profession
                    )
                    ?: $this->cleanString(
                        $this->profession
                    ),

                'headline' =>
                    $this->cleanString(
                        $about?->professional_headline
                    )
                    ?: $this->cleanString(
                        $profile?->tagline
                    )
                    ?: $this->cleanString(
                        $profile?->short_introduction
                    ),

                'about' =>
                    $this->cleanString(
                        $about?->about
                    )
                    ?: $this->cleanString(
                        $profile?->about_me
                    )
                    ?: $this->cleanString(
                        $profile?->career_objective
                    ),

                'email' =>
                    $this->cleanString(
                        $this->getContactValue(
                            $contacts,
                            [
                                'email',
                                'public_email',
                            ]
                        )
                    )
                    ?: $this->cleanString(
                        $profile?->public_email
                    )
                    ?: $this->cleanString(
                        $this->email
                    ),

                'phone' =>
                    $this->cleanString(
                        $this->getContactValue(
                            $contacts,
                            [
                                'phone',
                                'mobile',
                                'telephone',
                            ]
                        )
                    )
                    ?: $this->cleanString(
                        $profile?->primary_phone
                    )
                    ?: $this->cleanString(
                        $this->phone
                    ),

                'location' =>
                    $this->cleanString(
                        $this->getContactValue(
                            $contacts,
                            [
                                'location',
                                'address',
                            ]
                        )
                    )
                    ?: $this->buildLocation($profile)
                    ?: $this->cleanString(
                        $this->location
                    ),

                'profileImage' =>
                    $this->fileUrl(
                        $profile?->profile_image
                        ?? $this->profile_photo
                    ),

                'resumeUrl' =>
                    $this->fileUrl(
                        $primaryResume?->resume_url
                    ),
            ],

            'about' => $about
                ? [
                    'id' => $about->id,
                    'professionalHeadline' =>
                        $this->cleanString(
                            $about
                                ->professional_headline
                        ),
                    'about' =>
                        $this->cleanString(
                            $about->about
                        ),
                ]
                : null,

            'contact' => $contacts
                ->sortBy('display_order')
                ->values()
                ->map(function ($contact) {
                    return [
                        'id' => $contact->id,
                        'type' =>
                            $this->cleanString(
                                $contact->contact_type
                            ),
                        'label' =>
                            $this->cleanString(
                                $contact->label
                            ),
                        'value' =>
                            $this->cleanString(
                                $contact->value
                            ),
                        'displayOrder' =>
                            $contact->display_order,
                    ];
                })
                ->filter(
                    fn ($contact) =>
                        !empty($contact['value'])
                )
                ->values(),

            'projects' => $this->projects
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'title' =>
                            $this->cleanString(
                                $project->title
                            ),
                        'category' =>
                            $this->cleanString(
                                $project->category
                            ),
                        'description' =>
                            $this->cleanString(
                                $project->description
                                ?: $project
                                    ->short_description
                            ),
                        'technologies' =>
                            collect(
                                $project->technologies
                                ?? []
                            )
                                ->filter()
                                ->values()
                                ->all(),
                        'liveUrl' =>
                            $this->externalUrl(
                                $project->live_url
                            ),
                        'githubUrl' =>
                            $this->externalUrl(
                                $project->github_url
                            ),
                        'image' =>
                            $this->fileUrl(
                                $project->image
                            ),
                        'role' =>
                            $this->cleanString(
                                $project->role
                            ),
                        'status' =>
                            $this->cleanString(
                                $project->status
                            ),
                        'featured' =>
                            (bool) $project->featured,
                    ];
                })
                ->filter(
                    fn ($project) =>
                        !empty($project['title'])
                )
                ->values(),

            'education' => $this->educations
                ->sortBy('display_order')
                ->values()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'qualification' =>
                            $this->cleanString(
                                $item->degree
                            ),
                        'institution' =>
                            $this->cleanString(
                                $item
                                    ->institution_name
                            ),
                        'fieldOfStudy' =>
                            $this->cleanString(
                                $item->field_of_study
                            ),
                        'startDate' =>
                            $this->formatDate(
                                $item->start_date
                            ),
                        'endDate' =>
                            $item->currently_studying
                            ? 'Present'
                            : $this->formatDate(
                                $item->end_date
                            ),
                        'description' =>
                            $this->cleanString(
                                $item->description
                            ),
                        'grade' =>
                            $this->cleanString(
                                $item->grade
                            ),
                        'location' =>
                            $this->cleanString(
                                $item->location
                            ),
                    ];
                }),

            'experience' => $this->experiences
                ->sortBy('display_order')
                ->values()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'role' =>
                            $this->cleanString(
                                $item->position_title
                            ),
                        'company' =>
                            $this->cleanString(
                                $item
                                    ->organization_name
                            ),
                        'employmentType' =>
                            $this->cleanString(
                                $item->employment_type
                            ),
                        'industry' =>
                            $this->cleanString(
                                $item->industry
                            ),
                        'location' =>
                            $this->cleanString(
                                $item->location
                            ),
                        'startDate' =>
                            $this->formatDate(
                                $item->start_date
                            ),
                        'endDate' =>
                            $item->currently_working
                            ? 'Present'
                            : $this->formatDate(
                                $item->end_date
                            ),
                        'description' =>
                            $this->cleanString(
                                $item->description
                            ),
                        'achievements' =>
                            collect(
                                $item->achievements
                                ?? []
                            )
                                ->filter()
                                ->values()
                                ->all(),
                        'skills' =>
                            collect(
                                $item->skills
                                ?? []
                            )
                                ->filter()
                                ->values()
                                ->all(),
                    ];
                }),

            'skills' => $this->skills
                ->sortBy('display_order')
                ->pluck('name')
                ->map(
                    fn ($name) =>
                        $this->cleanString($name)
                )
                ->filter()
                ->values(),

            'certificates' => $this->certificates
                ->sortBy('display_order')
                ->values()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' =>
                            $this->cleanString(
                                $item->certificate_name
                            ),
                        'issuer' =>
                            $this->cleanString(
                                $item
                                    ->issuing_organization
                            ),
                        'issuedDate' =>
                            $this->formatDate(
                                $item->issue_date
                            ),
                        'expiryDate' =>
                            $item->never_expires
                            ? null
                            : $this->formatDate(
                                $item->expiry_date
                            ),
                        'credentialId' =>
                            $this->cleanString(
                                $item->credential_id
                            ),
                        'credentialUrl' =>
                            $this->externalUrl(
                                $item->credential_url
                            ),
                        'certificateFileUrl' =>
                            $this->fileUrl(
                                $item
                                    ->certificate_file_url
                            ),
                        'featured' =>
                            (bool) $item->is_featured,
                    ];
                })
                ->filter(
                    fn ($certificate) =>
                        !empty($certificate['title'])
                )
                ->values(),

            'achievements' => $this->achievements
                ->sortBy('display_order')
                ->values()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' =>
                            $this->cleanString(
                                $item->title
                            ),
                        'description' =>
                            $this->cleanString(
                                $item->description
                            ),
                        'type' =>
                            $this->cleanString(
                                $item->achievement_type
                            ),
                        'organization' =>
                            $this->cleanString(
                                $item->organization
                            ),
                        'date' =>
                            $this->formatDate(
                                $item
                                    ->achievement_date
                            ),
                        'url' =>
                            $this->externalUrl(
                                $item->achievement_url
                            ),
                        'evidenceFileUrl' =>
                            $this->fileUrl(
                                $item
                                    ->evidence_file_url
                            ),
                        'featured' =>
                            (bool) $item->is_featured,
                    ];
                })
                ->filter(
                    fn ($achievement) =>
                        !empty($achievement['title'])
                )
                ->values(),

            'languages' => $this->languages
                ->sortBy('display_order')
                ->values()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'language' =>
                            $this->cleanString(
                                $item->language
                            ),
                        'proficiency' =>
                            $this->cleanString(
                                $item->proficiency
                            ),
                        'readingLevel' =>
                            $this->cleanString(
                                $item->reading_level
                            ),
                        'writingLevel' =>
                            $this->cleanString(
                                $item->writing_level
                            ),
                        'speakingLevel' =>
                            $this->cleanString(
                                $item->speaking_level
                            ),
                        'isNative' =>
                            (bool) $item->is_native,
                        'isFeatured' =>
                            (bool) $item->is_featured,
                    ];
                })
                ->filter(
                    fn ($language) =>
                        !empty($language['language'])
                )
                ->values(),

            'resumes' => $this->resumes
                ->map(function ($resume) {
                    return [
                        'id' => $resume->id,
                        'title' =>
                            $this->cleanString(
                                $resume->title
                            ),
                        'description' =>
                            $this->cleanString(
                                $resume->description
                            ),
                        'resumeUrl' =>
                            $this->fileUrl(
                                $resume->resume_url
                            ),
                        'originalFileName' =>
                            $this->cleanString(
                                $resume
                                    ->original_file_name
                            ),
                        'version' =>
                            $this->cleanString(
                                $resume
                                    ->resume_version
                            ),
                        'visibility' =>
                            $this->cleanString(
                                $resume->visibility
                            ),
                        'isPrimary' =>
                            (bool) $resume->is_primary,
                        'downloads' =>
                            (int) (
                                $resume->downloads
                                ?? 0
                            ),
                    ];
                })
                ->filter(
                    fn ($resume) =>
                        !empty($resume['title'])
                        || !empty(
                            $resume['resumeUrl']
                        )
                )
                ->values(),

            'social_links' => $this->socialLinks
                ->where('is_visible', true)
                ->sortBy('display_order')
                ->values()
                ->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'platform' =>
                            $this->cleanString(
                                $link->platform
                            ),
                        'label' =>
                            $this->cleanString(
                                $link->label
                            ),
                        'username' =>
                            $this->cleanString(
                                $link->username
                            ),
                        'url' =>
                            $this->externalUrl(
                                $link->url
                            ),
                        'isFeatured' =>
                            (bool) $link->is_featured,
                    ];
                })
                ->filter(
                    fn ($link) =>
                        !empty($link['url'])
                )
                ->values(),

            'enabledModules' => [
                'profile' => true,
                'about' => true,
                'skills' => true,
                'projects' => true,
                'experience' => true,
                'education' => true,
                'certificates' => true,
                'achievements' => true,
                'languages' => true,
                'resume' => true,
                'socialLinks' => true,
                'contact' => true,
            ],

            'profession_modules' =>
                $this->getProfessionModules(),

            'template' => [
                'id' => 1,
                'name' => 'Modern Developer',
                'category' =>
                    $this->profession
                    ?: 'Professional',
                'packageName' =>
                    $this->package_name
                    ?: 'Silver',
                'style' => 'Modern',
                'layout' => 'Single Page',
                'accent' => 'Purple',
            ],
        ];
    }

    private function getDisplayName(
        $profile
    ): string {
        if (!$profile) {
            return $this->name ?? '';
        }

        if (
            !empty(
                $this->cleanString(
                    $profile->display_name
                )
            )
        ) {
            return $this->cleanString(
                $profile->display_name
            );
        }

        $fullName = trim(
            ($profile->first_name ?? '')
            . ' '
            . ($profile->last_name ?? '')
        );

        return $fullName !== ''
            ? $fullName
            : ($this->name ?? '');
    }

    private function getContactValue(
        Collection $contacts,
        array $types
    ): ?string {
        foreach ($types as $type) {
            $contact = $contacts->first(
                function ($item) use ($type) {
                    return strtolower(
                        trim(
                            (string) $item
                                ->contact_type
                        )
                    ) === strtolower($type);
                }
            );

            if (
                $contact &&
                !empty(
                    $this->cleanString(
                        $contact->value
                    )
                )
            ) {
                return $this->cleanString(
                    $contact->value
                );
            }
        }

        return null;
    }

    private function buildLocation(
        $profile
    ): ?string {
        if (!$profile) {
            return null;
        }

        $parts = array_filter([
            $this->cleanString(
                $profile->city
            ),
            $this->cleanString(
                $profile->district
            ),
            $this->cleanString(
                $profile->country
            ),
        ]);

        return count($parts) > 0
            ? implode(', ', $parts)
            : null;
    }

    private function formatDate(
        $date
    ): ?string {
        if (!$date) {
            return null;
        }

        if (
            is_object($date) &&
            method_exists($date, 'format')
        ) {
            return $date->format('Y-m-d');
        }

        return (string) $date;
    }

    private function cleanString(
        $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    private function externalUrl(
        ?string $url
    ): ?string {
        $url = $this->cleanString($url);

        if (!$url) {
            return null;
        }

        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            return null;
        }

        $scheme = strtolower(
            parse_url($url, PHP_URL_SCHEME)
            ?? ''
        );

        return in_array(
            $scheme,
            ['http', 'https'],
            true
        )
            ? $url
            : null;
    }

    private function fileUrl(
        ?string $path
    ): ?string {
        $path = $this->cleanString($path);

        if (!$path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://')
        ) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return url($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return url('/' . $path);
        }

        return asset(
            'storage/' . ltrim($path, '/')
        );
    }

    private function getProfessionModules(): array
    {
        return match ($this->profession) {
            'Software Developer' => [
                'tech_stacks' =>
                    $this->techStacks,
                'coding_profiles' =>
                    $this->codingProfiles,
                'developer_projects' =>
                    $this->developerProjects,
            ],

            'Teacher' => [
                'teaching_subjects' =>
                    $this->teachingSubjects,
                'workshops' =>
                    $this->workshops,
            ],

            'UI/UX Designer' => [
                'design_tools' =>
                    $this->designTools,
                'case_studies' =>
                    $this->caseStudies,
            ],

            'Photographer' => [
                'camera_equipment' =>
                    $this->cameraEquipment,
                'photography_portfolios' =>
                    $this
                        ->photographyPortfolios,
            ],

            'Healthcare Professional' => [
                'medical_certifications' =>
                    $this
                        ->medicalCertifications,
                'clinical_experiences' =>
                    $this->clinicalExperiences,
            ],

            'Freelancer' => [
                'client_projects' =>
                    $this->clientProjects,
                'client_testimonials' =>
                    $this
                        ->clientTestimonials,
            ],

            'Student' => [
                'academic_projects' =>
                    $this->academicProjects,
                'student_certifications' =>
                    $this
                        ->studentCertifications,
            ],

            'Content Creator' => [
                'content_portfolios' =>
                    $this->contentPortfolios,
                'social_media_profiles' =>
                    $this
                        ->socialMediaProfiles,
            ],

            default => [],
        };
    }
}