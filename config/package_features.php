<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package hierarchy
    |--------------------------------------------------------------------------
    */

    'hierarchy' => [
        'Silver' => 1,
        'Gold' => 2,
        'Platinum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silver
    |--------------------------------------------------------------------------
    */

    'Silver' => [
        'common_modules' => [
            'profile',
            'about',
            'contact_information',
            'projects',
            'skills',
            'certificates',
            'education',
            'experience',
        ],

        'profession_modules' => false,

        'limits' => [
            'projects' => 3,
            'skills' => 15,
            'certificates' => 3,
            'education' => 3,
            'experience' => 3,
        ],

        'dashboard_recommendations' => false,

        'analytics' => [
            'enabled' => false,
            'history_days' => 0,
        ],

        'publish' => [
            'seo' => false,
            'public' => true,
            'password' => false,
            'private' => false,
        ],

        'appearance' => [
            'themes' => [
                'light',
            ],

            'compact_mode' => false,
            'animations' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gold
    |--------------------------------------------------------------------------
    */

    'Gold' => [
        'common_modules' => [
            'profile',
            'about',
            'contact_information',
            'projects',
            'skills',
            'certificates',
            'education',
            'experience',
            'achievements',
            'languages',
            'resume',
            'social_links',
        ],

        'profession_modules' => true,

        'limits' => [
            'projects' => 15,
            'skills' => 50,
            'certificates' => 15,
            'education' => 15,
            'experience' => 15,
            'achievements' => 15,
            'languages' => 5,
            'resume' => 3,
            'social_links' => 10,

            /*
             * Profession module records.
             */
            'profession_modules' => 15,
        ],

        'dashboard_recommendations' => true,

        'analytics' => [
            'enabled' => true,
            'history_days' => 30,
        ],

        'publish' => [
            'seo' => true,
            'public' => true,
            'password' => true,
            'private' => false,
        ],

        'appearance' => [
            'themes' => [
                'light',
                'dark',
                'system',
            ],

            'compact_mode' => false,
            'animations' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Platinum
    |--------------------------------------------------------------------------
    */

    'Platinum' => [
        'common_modules' => [
            'profile',
            'about',
            'contact_information',
            'projects',
            'skills',
            'certificates',
            'education',
            'experience',
            'achievements',
            'languages',
            'resume',
            'social_links',
        ],

        'profession_modules' => true,

        'limits' => [
            'projects' => null,
            'skills' => null,
            'certificates' => null,
            'education' => null,
            'experience' => null,
            'achievements' => null,
            'languages' => null,
            'resume' => null,
            'social_links' => null,
            'profession_modules' => null,
        ],

        'dashboard_recommendations' => true,

        'analytics' => [
            'enabled' => true,
            'history_days' => 90,
        ],

        'publish' => [
            'seo' => true,
            'public' => true,
            'password' => true,
            'private' => true,
        ],

        'appearance' => [
            'themes' => [
                'light',
                'dark',
                'system',
            ],

            'compact_mode' => true,
            'animations' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module package requirements
    |--------------------------------------------------------------------------
    */

    'module_requirements' => [
        'profile' => 'Silver',
        'about' => 'Silver',
        'contact_information' => 'Silver',
        'projects' => 'Silver',
        'skills' => 'Silver',
        'certificates' => 'Silver',

        'education' => 'Silver',
        'experience' => 'Silver',
        'achievements' => 'Gold',
        'languages' => 'Gold',
        'resume' => 'Gold',
        'social_links' => 'Gold',
    ],

    /*
    |--------------------------------------------------------------------------
    | Profession module IDs
    |--------------------------------------------------------------------------
    */

    'profession_module_ids' => [
        'coding_profiles',
        'tech_stacks',
        'developer_projects',

        'teaching_subjects',
        'workshops',

        'camera_equipment',
        'photography_portfolio',

        'academic_projects',
        'student_certifications',

        'client_projects',
        'client_testimonials',

        'case_studies',
        'design_tools',

        'clinical_experiences',
        'medical_certifications',

        'content_portfolios',
        'social_media_profiles',
    ],
];