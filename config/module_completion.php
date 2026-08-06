<?php

use App\Enums\ModuleId;

return [

    /*
    |--------------------------------------------------------------------------
    | Common Modules
    |--------------------------------------------------------------------------
    */

    ModuleId::PROFILE->value => [
        'table' => 'portfolio_profiles',
        'minimum' => 1,
    ],

    ModuleId::ABOUT->value => [
        'table' => 'portfolio_profiles',
        'minimum' => 1,
    ],

    ModuleId::CONTACT->value => [
        'table' => 'portfolio_profiles',
        'minimum' => 1,
    ],

    ModuleId::PROJECTS->value => [
        'table' => 'projects',
        'minimum' => 3,
    ],

    ModuleId::EDUCATION->value => [
        'table' => 'educations',
        'minimum' => 1,
    ],

    ModuleId::EXPERIENCE->value => [
        'table' => 'experiences',
        'minimum' => 1,
    ],

    ModuleId::SKILLS->value => [
        'table' => 'skills',
        'minimum' => 5,
    ],

    ModuleId::CERTIFICATES->value => [
        'table' => 'certificates',
        'minimum' => 3,
    ],

    ModuleId::ACHIEVEMENTS->value => [
        'table' => 'achievements',
        'minimum' => 3,
    ],

    ModuleId::LANGUAGES->value => [
        'table' => 'languages',
        'minimum' => 1,
    ],

    ModuleId::RESUME->value => [
        'table' => 'resumes',
        'minimum' => 1,
    ],

    ModuleId::SOCIAL_LINKS->value => [
        'table' => 'social_links',
        'minimum' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Software Developer
    |--------------------------------------------------------------------------
    */

    ModuleId::TECH_STACK->value => [
        'table' => 'tech_stacks',
        'minimum' => 3,
    ],

    ModuleId::CODING_PROFILES->value => [
        'table' => 'coding_profiles',
        'minimum' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Teacher
    |--------------------------------------------------------------------------
    */

    ModuleId::TEACHING_SUBJECTS->value => [
        'table' => 'teaching_subjects',
        'minimum' => 3,
    ],

    ModuleId::WORKSHOPS->value => [
        'table' => 'workshops',
        'minimum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI/UX Designer
    |--------------------------------------------------------------------------
    */

    ModuleId::DESIGN_TOOLS->value => [
        'table' => 'design_tools',
        'minimum' => 3,
    ],

    ModuleId::CASE_STUDIES->value => [
        'table' => 'case_studies',
        'minimum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Photographer
    |--------------------------------------------------------------------------
    */

    ModuleId::CAMERA_EQUIPMENT->value => [
        'table' => 'camera_equipments',
        'minimum' => 3,
    ],

    ModuleId::PHOTOGRAPHY_PORTFOLIO->value => [
        'table' => 'photography_portfolios',
        'minimum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Freelancer
    |--------------------------------------------------------------------------
    */

    ModuleId::CLIENT_PROJECTS->value => [
        'table' => 'client_projects',
        'minimum' => 3,
    ],

    ModuleId::CLIENT_TESTIMONIALS->value => [
        'table' => 'client_testimonials',
        'minimum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Creator
    |--------------------------------------------------------------------------
    */

    ModuleId::SOCIAL_MEDIA_PROFILES->value => [
        'table' => 'social_media_profiles',
        'minimum' => 1,
    ],

    ModuleId::CONTENT_PORTFOLIO->value => [
        'table' => 'content_portfolios',
        'minimum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Healthcare Professional
    |--------------------------------------------------------------------------
    */

    ModuleId::CLINICAL_EXPERIENCE->value => [
        'table' => 'clinical_experiences',
        'minimum' => 3,
    ],

    ModuleId::MEDICAL_CERTIFICATIONS->value => [
        'table' => 'medical_certifications',
        'minimum' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Student
    |--------------------------------------------------------------------------
    */

    ModuleId::ACADEMIC_PROJECTS->value => [
        'table' => 'academic_projects',
        'minimum' => 3,
    ],

    ModuleId::STUDENT_CERTIFICATIONS->value => [
        'table' => 'student_certifications',
        'minimum' => 3,
    ],
];