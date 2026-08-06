<?php

use App\Enums\ModuleId;

return [

    'goals' => [

        'Get a Job' => [
            ModuleId::PROFILE,
            ModuleId::ABOUT,
            ModuleId::CONTACT,
            ModuleId::SKILLS,
            ModuleId::PROJECTS,
            ModuleId::EXPERIENCE,
            ModuleId::EDUCATION,
            ModuleId::RESUME,
        ],

        'Find Freelance Clients' => [
            ModuleId::PROFILE,
            ModuleId::ABOUT,
            ModuleId::CONTACT,
            ModuleId::SKILLS,
            ModuleId::PROJECTS,
            ModuleId::EXPERIENCE,
            ModuleId::SOCIAL_LINKS,
        ],

        'Build Personal Brand' => [
            ModuleId::PROFILE,
            ModuleId::ABOUT,
            ModuleId::SKILLS,
            ModuleId::PROJECTS,
            ModuleId::ACHIEVEMENTS,
            ModuleId::SOCIAL_LINKS,
            ModuleId::CONTACT,
        ],

        'Higher Studies' => [
            ModuleId::PROFILE,
            ModuleId::ABOUT,
            ModuleId::EDUCATION,
            ModuleId::PROJECTS,
            ModuleId::SKILLS,
            ModuleId::CERTIFICATES,
            ModuleId::ACHIEVEMENTS,
            ModuleId::RESUME,
        ],

        'Grow Business' => [
            ModuleId::PROFILE,
            ModuleId::ABOUT,
            ModuleId::PROJECTS,
            ModuleId::EXPERIENCE,
            ModuleId::ACHIEVEMENTS,
            ModuleId::SOCIAL_LINKS,
            ModuleId::CONTACT,
        ],

        'Start Career' => [
            ModuleId::PROFILE,
            ModuleId::ABOUT,
            ModuleId::EDUCATION,
            ModuleId::SKILLS,
            ModuleId::PROJECTS,
            ModuleId::CERTIFICATES,
            ModuleId::RESUME,
            ModuleId::CONTACT,
        ],

    ],

    'professions' => [

        'Software Developer' => [
            [
                'placement' => 'after',
                'target' => ModuleId::SKILLS,
                'modules' => [
                    ModuleId::TECH_STACK,
                    ModuleId::CODING_PROFILES,
                ],
            ],
        ],

        'Teacher' => [
            [
                'placement' => 'after',
                'target' => ModuleId::SKILLS,
                'modules' => [
                    ModuleId::TEACHING_SUBJECTS,
                    ModuleId::WORKSHOPS,
                ],
            ],
        ],

        'UI/UX Designer' => [
            [
                'placement' => 'after',
                'target' => ModuleId::PROJECTS,
                'modules' => [
                    ModuleId::DESIGN_TOOLS,
                    ModuleId::CASE_STUDIES,
                ],
            ],
        ],

        'Photographer' => [
            [
                'placement' => 'after',
                'target' => ModuleId::PROJECTS,
                'modules' => [
                    ModuleId::CAMERA_EQUIPMENT,
                    ModuleId::PHOTOGRAPHY_PORTFOLIO,
                ],
            ],
        ],

        'Freelancer' => [
            [
                'placement' => 'after',
                'target' => ModuleId::PROJECTS,
                'modules' => [
                    ModuleId::CLIENT_PROJECTS,
                    ModuleId::CLIENT_TESTIMONIALS,
                ],
            ],
        ],

        'Content Creator' => [
            [
                'placement' => 'after',
                'target' => ModuleId::PROJECTS,
                'modules' => [
                    ModuleId::SOCIAL_MEDIA_PROFILES,
                    ModuleId::CONTENT_PORTFOLIO,
                ],
            ],
        ],

        'Healthcare Professional' => [
            [
                'placement' => 'after',
                'target' => ModuleId::EXPERIENCE,
                'modules' => [
                    ModuleId::CLINICAL_EXPERIENCE,
                    ModuleId::MEDICAL_CERTIFICATIONS,
                ],
            ],
        ],

        'Student' => [
            [
                'placement' => 'after',
                'target' => ModuleId::EDUCATION,
                'modules' => [
                    ModuleId::ACADEMIC_PROJECTS,
                    ModuleId::STUDENT_CERTIFICATIONS,
                ],
            ],
        ],

    ],

];