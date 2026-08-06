<?php

namespace App\Enums;

enum ModuleId: string
{
    /*
    |--------------------------------------------------------------------------
    | Common Portfolio Modules
    |--------------------------------------------------------------------------
    */

    case PROFILE = 'profile';
    case ABOUT = 'about';
    case CONTACT = 'contact';
    case PROJECTS = 'projects';
    case EDUCATION = 'education';
    case EXPERIENCE = 'experience';
    case SKILLS = 'skills';
    case CERTIFICATES = 'certificates';
    case ACHIEVEMENTS = 'achievements';
    case LANGUAGES = 'languages';
    case RESUME = 'resume';
    case SOCIAL_LINKS = 'social-links';

    /*
    |--------------------------------------------------------------------------
    | Software Developer Modules
    |--------------------------------------------------------------------------
    */

    case TECH_STACK = 'tech-stack';
    case CODING_PROFILES = 'coding-profiles';

    /*
    |--------------------------------------------------------------------------
    | Teacher Modules
    |--------------------------------------------------------------------------
    */

    case TEACHING_SUBJECTS = 'teaching-subjects';
    case WORKSHOPS = 'workshops';

    /*
    |--------------------------------------------------------------------------
    | UI/UX Designer Modules
    |--------------------------------------------------------------------------
    */

    case DESIGN_TOOLS = 'design-tools';
    case CASE_STUDIES = 'case-studies';

    /*
    |--------------------------------------------------------------------------
    | Photographer Modules
    |--------------------------------------------------------------------------
    */

    case CAMERA_EQUIPMENT = 'camera-equipment';
    case PHOTOGRAPHY_PORTFOLIO = 'photography-portfolio';

    /*
    |--------------------------------------------------------------------------
    | Freelancer Modules
    |--------------------------------------------------------------------------
    */

    case CLIENT_PROJECTS = 'client-projects';
    case CLIENT_TESTIMONIALS = 'client-testimonials';

    /*
    |--------------------------------------------------------------------------
    | Content Creator Modules
    |--------------------------------------------------------------------------
    */

    case SOCIAL_MEDIA_PROFILES = 'social-media-profiles';
    case CONTENT_PORTFOLIO = 'content-portfolio';

    /*
    |--------------------------------------------------------------------------
    | Healthcare Professional Modules
    |--------------------------------------------------------------------------
    */

    case CLINICAL_EXPERIENCE = 'clinical-experience';
    case MEDICAL_CERTIFICATIONS = 'medical-certifications';

    /*
    |--------------------------------------------------------------------------
    | Student Modules
    |--------------------------------------------------------------------------
    */

    case ACADEMIC_PROJECTS = 'academic-projects';
    case STUDENT_CERTIFICATIONS = 'student-certifications';

    /**
     * Return the display name shown in the frontend.
     */
    public function label(): string
    {
        return match ($this) {
            self::PROFILE => 'Profile',
            self::ABOUT => 'About',
            self::CONTACT => 'Contact Information',
            self::PROJECTS => 'Projects',
            self::EDUCATION => 'Education',
            self::EXPERIENCE => 'Experience',
            self::SKILLS => 'Skills',
            self::CERTIFICATES => 'Certificates',
            self::ACHIEVEMENTS => 'Achievements',
            self::LANGUAGES => 'Languages',
            self::RESUME => 'Resume',
            self::SOCIAL_LINKS => 'Social Links',

            self::TECH_STACK => 'Tech Stack',
            self::CODING_PROFILES => 'Coding Profiles',

            self::TEACHING_SUBJECTS => 'Teaching Subjects',
            self::WORKSHOPS => 'Workshops',

            self::DESIGN_TOOLS => 'Design Tools',
            self::CASE_STUDIES => 'Case Studies',

            self::CAMERA_EQUIPMENT => 'Camera Equipment',
            self::PHOTOGRAPHY_PORTFOLIO => 'Photography Portfolio',

            self::CLIENT_PROJECTS => 'Client Projects',
            self::CLIENT_TESTIMONIALS => 'Client Testimonials',

            self::SOCIAL_MEDIA_PROFILES => 'Social Media Profiles',
            self::CONTENT_PORTFOLIO => 'Content Portfolio',

            self::CLINICAL_EXPERIENCE => 'Clinical Experience',
            self::MEDICAL_CERTIFICATIONS => 'Medical Certifications',

            self::ACADEMIC_PROJECTS => 'Academic Projects',
            self::STUDENT_CERTIFICATIONS => 'Student Certifications',
        };
    }

    /**
     * Check whether the module is a common portfolio module.
     */
    public function isCommon(): bool
    {
        return in_array($this, [
            self::PROFILE,
            self::ABOUT,
            self::CONTACT,
            self::PROJECTS,
            self::EDUCATION,
            self::EXPERIENCE,
            self::SKILLS,
            self::CERTIFICATES,
            self::ACHIEVEMENTS,
            self::LANGUAGES,
            self::RESUME,
            self::SOCIAL_LINKS,
        ], true);
    }
}