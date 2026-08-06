<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\PortfolioProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\ResumeController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\CodingProfileController;
use App\Http\Controllers\Api\TechStackController;
use App\Http\Controllers\Api\DeveloperProjectController;
use App\Http\Controllers\Api\TeachingSubjectController;
use App\Http\Controllers\Api\WorkshopController;
use App\Http\Controllers\Api\CameraEquipmentController;
use App\Http\Controllers\Api\PhotographyPortfolioController;
use App\Http\Controllers\Api\AcademicProjectController;
use App\Http\Controllers\Api\StudentCertificationController;
use App\Http\Controllers\Api\ClientProjectController;
use App\Http\Controllers\Api\ClientTestimonialController;
use App\Http\Controllers\Api\CaseStudyController;
use App\Http\Controllers\Api\DesignToolController;
use App\Http\Controllers\Api\ContentPortfolioController;
use App\Http\Controllers\Api\SocialMediaProfileController;
use App\Http\Controllers\Api\Healthcare\ClinicalExperienceController;
use App\Http\Controllers\Api\Healthcare\MedicalCertificationController;
use App\Http\Controllers\Api\PortfolioAboutController;
use App\Http\Controllers\Api\ContactInformationController;
use App\Http\Controllers\Api\PortfolioPreviewController;
use App\Http\Controllers\Api\ProfessionModuleOverviewController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\PortfolioPublishController;
use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\PackageAccessController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminPackageController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\AdminUserController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Platinum Portfolio API is working',
    ]);
});
Route::get(
    '/public/portfolio/{slug}',
    [PublicPortfolioController::class, 'show']
);
Route::post(
    '/public/portfolio/{slug}/verify-password',
    [PublicPortfolioController::class, 'verifyPassword']
);
Route::middleware([
    'auth:api',
    'admin',
])
    ->prefix('admin')
    ->group(function () {
        Route::get('/test', function () {
            return response()->json([
                'success' => true,
                'message' =>
                    'Admin authentication is working.',
            ]);
        });
    });
Route::middleware([
    'auth:api',
    'admin',
])
    ->prefix('admin')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            [
                AdminDashboardController::class,
                'index',
            ]
        );

        Route::get(
            '/users',
            [
                AdminUserController::class,
                'index',
            ]
        );

        Route::get(
            '/users/{user}',
            [
                AdminUserController::class,
                'show',
            ]
        );

        Route::patch(
            '/users/{user}/package',
            [
                AdminUserController::class,
                'updatePackage',
            ]
        );

        Route::patch(
            '/users/{user}/status',
            [
                AdminUserController::class,
                'updateStatus',
            ]
        );

        Route::delete(
            '/users/{user}',
            [
                AdminUserController::class,
                'destroy',
            ]
        );

        Route::get(
            '/packages',
            [
                AdminPackageController::class,
                'index',
            ]
        );

        Route::apiResource(
            'payments',
            AdminPaymentController::class
        );

        Route::patch(
            '/payments/{payment}/status',
            [
                AdminPaymentController::class,
                'updateStatus',
            ]
        );

        Route::get(
            '/profile',
            [
                AdminProfileController::class,
                'show',
            ]
        );

        Route::put(
            '/profile',
            [
                AdminProfileController::class,
                'update',
            ]
        );

        Route::put(
            '/profile/password',
            [
                AdminProfileController::class,
                'changePassword',
            ]
        );
    });
/*
|--------------------------------------------------------------------------
| Public authentication routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post(
        '/register',
        [AuthController::class, 'register']
    )->middleware('throttle:5,1');

    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->middleware('throttle:5,1');

    Route::post(
        '/verify-otp',
        [AuthController::class, 'verifyOtp']
    )->middleware('throttle:10,1');
});


/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/auth/me',
        [AuthController::class, 'me']
    );

    Route::post(
        '/auth/logout',
        [AuthController::class, 'logout']
    );

    Route::post(
        '/auth/refresh',
        [AuthController::class, 'refresh']
    );


    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'show']
    );


 Route::get(
        '/portfolio/profile',
        [PortfolioProfileController::class, 'show']
    );

    Route::post(
        '/portfolio/profile',
        [PortfolioProfileController::class, 'update']
    );
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

     Route::get('/educations', [EducationController::class, 'index']);
    Route::post('/educations', [EducationController::class, 'store']);
    Route::put('/educations/{education}', [EducationController::class, 'update']);
    Route::delete('/educations/{education}', [EducationController::class, 'destroy']);
Route::get('/experiences', [
    ExperienceController::class,
    'index'
]);

Route::post('/experiences', [
    ExperienceController::class,
    'store'
]);

Route::put('/experiences/{experience}', [
    ExperienceController::class,
    'update'
]);

Route::delete('/experiences/{experience}', [
    ExperienceController::class,
    'destroy'
]);
Route::get('/skills', [
        SkillController::class,
        'index',
    ]);

    Route::post('/skills', [
        SkillController::class,
        'store',
    ]);

    Route::put('/skills/{skill}', [
        SkillController::class,
        'update',
    ]);

    Route::delete('/skills/{skill}', [
        SkillController::class,
        'destroy',
    ]);
    Route::get('/certificates', [
        CertificateController::class,
        'index',
    ]);

    Route::post('/certificates', [
        CertificateController::class,
        'store',
    ]);

    Route::put('/certificates/{certificate}', [
        CertificateController::class,
        'update',
    ]);

    /*
     * File upload update-க்கு frontend இந்த POST route-ஐ
     * _method=PUT உடன் பயன்படுத்தும்.
     */
    Route::post('/certificates/{certificate}', [
        CertificateController::class,
        'update',
    ]);

    Route::delete('/certificates/{certificate}', [
        CertificateController::class,
        'destroy',
    ]);
    Route::get('/achievements', [
        AchievementController::class,
        'index',
    ]);

    Route::post('/achievements', [
        AchievementController::class,
        'store',
    ]);

    Route::put('/achievements/{achievement}', [
        AchievementController::class,
        'update',
    ]);

    /*
     * Multipart file update:
     * Frontend sends POST + _method=PUT.
     */
    Route::post('/achievements/{achievement}', [
        AchievementController::class,
        'update',
    ]);

    Route::delete('/achievements/{achievement}', [
        AchievementController::class,
        'destroy',
    ]);
    Route::get(
        '/languages',
        [LanguageController::class,'index']
    );

    Route::post(
        '/languages',
        [LanguageController::class,'store']
    );

    Route::put(
        '/languages/{language}',
        [LanguageController::class,'update']
    );

    Route::delete(
        '/languages/{language}',
        [LanguageController::class,'destroy']
    );
Route::get(
        '/resumes',
        [ResumeController::class,'index']
    );

    Route::post(
        '/resumes',
        [ResumeController::class,'store']
    );

    Route::post(
        '/resumes/{resume}',
        [ResumeController::class,'update']
    );

    Route::delete(
        '/resumes/{resume}',
        [ResumeController::class,'destroy']
    );

    Route::get(
        '/resumes/{resume}/download',
        [ResumeController::class,'download']
    );
    
Route::get('/social-links', [
        SocialLinkController::class,
        'index',
    ]);

    Route::post('/social-links', [
        SocialLinkController::class,
        'store',
    ]);

    Route::get('/social-links/{socialLink}', [
        SocialLinkController::class,
        'show',
    ]);

    Route::put('/social-links/{socialLink}', [
        SocialLinkController::class,
        'update',
    ]);

    Route::delete('/social-links/{socialLink}', [
        SocialLinkController::class,
        'destroy',
    ]);

    Route::patch('/social-links/{socialLink}/visibility', [
        SocialLinkController::class,
        'toggleVisibility',
    ]);
    Route::get('/coding-profiles', [
    CodingProfileController::class,
    'index',
]);

Route::post('/coding-profiles', [
    CodingProfileController::class,
    'store',
]);

Route::get('/coding-profiles/{codingProfile}', [
    CodingProfileController::class,
    'show',
]);

Route::put('/coding-profiles/{codingProfile}', [
    CodingProfileController::class,
    'update',
]);

Route::delete('/coding-profiles/{codingProfile}', [
    CodingProfileController::class,
    'destroy',
]);
Route::get(
    '/tech-stacks',
    [
        TechStackController::class,
        'index',
    ]
);

Route::post(
    '/tech-stacks',
    [
        TechStackController::class,
        'store',
    ]
);

Route::get(
    '/tech-stacks/{techStack}',
    [
        TechStackController::class,
        'show',
    ]
);

Route::put(
    '/tech-stacks/{techStack}',
    [
        TechStackController::class,
        'update',
    ]
);

Route::delete(
    '/tech-stacks/{techStack}',
    [
        TechStackController::class,
        'destroy',
    ]
);
Route::get(
    '/developer-projects',
    [
        DeveloperProjectController::class,
        'index',
    ]
);

Route::post(
    '/developer-projects',
    [
        DeveloperProjectController::class,
        'store',
    ]
);

Route::get(
    '/developer-projects/{developerProject}',
    [
        DeveloperProjectController::class,
        'show',
    ]
);

Route::put(
    '/developer-projects/{developerProject}',
    [
        DeveloperProjectController::class,
        'update',
    ]
);

Route::delete(
    '/developer-projects/{developerProject}',
    [
        DeveloperProjectController::class,
        'destroy',
    ]
);
/*
|--------------------------------------------------------------------------
| Teaching Subjects
|--------------------------------------------------------------------------
*/

Route::get(
    '/teaching-subjects',
    [TeachingSubjectController::class, 'index']
);

Route::post(
    '/teaching-subjects',
    [TeachingSubjectController::class, 'store']
);

Route::get(
    '/teaching-subjects/{teachingSubject}',
    [TeachingSubjectController::class, 'show']
);

Route::put(
    '/teaching-subjects/{teachingSubject}',
    [TeachingSubjectController::class, 'update']
);

Route::delete(
    '/teaching-subjects/{teachingSubject}',
    [TeachingSubjectController::class, 'destroy']
);
/*
|--------------------------------------------------------------------------
| Workshops
|--------------------------------------------------------------------------
*/

Route::get(
    '/workshops',
    [WorkshopController::class, 'index']
);

Route::post(
    '/workshops',
    [WorkshopController::class, 'store']
);

Route::get(
    '/workshops/{workshop}',
    [WorkshopController::class, 'show']
);

Route::put(
    '/workshops/{workshop}',
    [WorkshopController::class, 'update']
);

Route::delete(
    '/workshops/{workshop}',
    [WorkshopController::class, 'destroy']
);
/*
|--------------------------------------------------------------------------
| Camera Equipment
|--------------------------------------------------------------------------
*/

Route::get(
    '/camera-equipment',
    [CameraEquipmentController::class, 'index']
);

Route::post(
    '/camera-equipment',
    [CameraEquipmentController::class, 'store']
);

Route::get(
    '/camera-equipment/{cameraEquipment}',
    [CameraEquipmentController::class, 'show']
);

Route::put(
    '/camera-equipment/{cameraEquipment}',
    [CameraEquipmentController::class, 'update']
);

Route::delete(
    '/camera-equipment/{cameraEquipment}',
    [CameraEquipmentController::class, 'destroy']
);
/*
|--------------------------------------------------------------------------
| Photography Portfolio
|--------------------------------------------------------------------------
*/

Route::get(
    '/photography-portfolio',
    [PhotographyPortfolioController::class, 'index']
);

Route::post(
    '/photography-portfolio',
    [PhotographyPortfolioController::class, 'store']
);

Route::get(
    '/photography-portfolio/{photographyPortfolio}',
    [PhotographyPortfolioController::class, 'show']
);

Route::put(
    '/photography-portfolio/{photographyPortfolio}',
    [PhotographyPortfolioController::class, 'update']
);

Route::delete(
    '/photography-portfolio/{photographyPortfolio}',
    [PhotographyPortfolioController::class, 'destroy']
);
/*
|--------------------------------------------------------------------------
| Student - Academic Projects
|--------------------------------------------------------------------------
*/

Route::get(
    '/academic-projects',
    [AcademicProjectController::class, 'index']
);

Route::post(
    '/academic-projects',
    [AcademicProjectController::class, 'store']
);

Route::get(
    '/academic-projects/{academicProject}',
    [AcademicProjectController::class, 'show']
);

Route::put(
    '/academic-projects/{academicProject}',
    [AcademicProjectController::class, 'update']
);

Route::delete(
    '/academic-projects/{academicProject}',
    [AcademicProjectController::class, 'destroy']
);
/*
|--------------------------------------------------------------------------
| Student - Certifications
|--------------------------------------------------------------------------
*/

Route::get(
    '/student-certifications',
    [StudentCertificationController::class, 'index']
);

Route::post(
    '/student-certifications',
    [StudentCertificationController::class, 'store']
);

Route::get(
    '/student-certifications/{studentCertification}',
    [StudentCertificationController::class, 'show']
);

Route::put(
    '/student-certifications/{studentCertification}',
    [StudentCertificationController::class, 'update']
);

Route::delete(
    '/student-certifications/{studentCertification}',
    [StudentCertificationController::class, 'destroy']
);
/*
|--------------------------------------------------------------------------
| Freelancer - Client Projects
|--------------------------------------------------------------------------
*/

Route::get(
    '/client-projects',
    [ClientProjectController::class,'index']
);

Route::post(
    '/client-projects',
    [ClientProjectController::class,'store']
);

Route::get(
    '/client-projects/{clientProject}',
    [ClientProjectController::class,'show']
);

Route::put(
    '/client-projects/{clientProject}',
    [ClientProjectController::class,'update']
);

Route::delete(
    '/client-projects/{clientProject}',
    [ClientProjectController::class,'destroy']
);

/*
|--------------------------------------------------------------------------
| Freelancer - Client Testimonials
|--------------------------------------------------------------------------
*/

Route::get(
    '/client-testimonials',
    [ClientTestimonialController::class,'index']
);

Route::post(
    '/client-testimonials',
    [ClientTestimonialController::class,'store']
);

Route::get(
    '/client-testimonials/{clientTestimonial}',
    [ClientTestimonialController::class,'show']
);

Route::put(
    '/client-testimonials/{clientTestimonial}',
    [ClientTestimonialController::class,'update']
);

Route::delete(
    '/client-testimonials/{clientTestimonial}',
    [ClientTestimonialController::class,'destroy']
);
Route::get(
        '/uiux/case-studies',
        [CaseStudyController::class, 'index']
    );

    Route::post(
        '/uiux/case-studies',
        [CaseStudyController::class, 'store']
    );

    Route::get(
        '/uiux/case-studies/{caseStudy}',
        [CaseStudyController::class, 'show']
    );

    Route::put(
        '/uiux/case-studies/{caseStudy}',
        [CaseStudyController::class, 'update']
    );

    Route::delete(
        '/uiux/case-studies/{caseStudy}',
        [CaseStudyController::class, 'destroy']
    );
    Route::get(
        '/uiux/design-tools',
        [DesignToolController::class, 'index']
    );

    Route::post(
        '/uiux/design-tools',
        [DesignToolController::class, 'store']
    );

    Route::get(
        '/uiux/design-tools/{designTool}',
        [DesignToolController::class, 'show']
    );

    Route::put(
        '/uiux/design-tools/{designTool}',
        [DesignToolController::class, 'update']
    );

    Route::delete(
        '/uiux/design-tools/{designTool}',
        [DesignToolController::class, 'destroy']
    );
    Route::get(
        '/healthcare/clinical-experiences',
        [ClinicalExperienceController::class, 'index']
    );

    Route::post(
        '/healthcare/clinical-experiences',
        [ClinicalExperienceController::class, 'store']
    );

    Route::get(
        '/healthcare/clinical-experiences/{id}',
        [ClinicalExperienceController::class, 'show']
    )->whereNumber('id');

    Route::put(
        '/healthcare/clinical-experiences/{id}',
        [ClinicalExperienceController::class, 'update']
    )->whereNumber('id');

    Route::delete(
        '/healthcare/clinical-experiences/{id}',
        [ClinicalExperienceController::class, 'destroy']
    )->whereNumber('id');


    /*
    |--------------------------------------------------------------------------
    | Healthcare Professional - Medical Certifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/healthcare/medical-certifications',
        [MedicalCertificationController::class, 'index']
    );

    Route::post(
        '/healthcare/medical-certifications',
        [MedicalCertificationController::class, 'store']
    );

    Route::get(
        '/healthcare/medical-certifications/{id}',
        [MedicalCertificationController::class, 'show']
    )->whereNumber('id');

    Route::put(
        '/healthcare/medical-certifications/{id}',
        [MedicalCertificationController::class, 'update']
    )->whereNumber('id');

    Route::delete(
        '/healthcare/medical-certifications/{id}',
        [MedicalCertificationController::class, 'destroy']
    )->whereNumber('id');
    /*
|--------------------------------------------------------------------------
| Content Creator - Content Portfolio
|--------------------------------------------------------------------------
*/

Route::get(
    '/content-portfolios',
    [ContentPortfolioController::class, 'index']
);

Route::post(
    '/content-portfolios',
    [ContentPortfolioController::class, 'store']
);

Route::get(
    '/content-portfolios/{id}',
    [ContentPortfolioController::class, 'show']
)->whereNumber('id');

Route::put(
    '/content-portfolios/{id}',
    [ContentPortfolioController::class, 'update']
)->whereNumber('id');

Route::delete(
    '/content-portfolios/{id}',
    [ContentPortfolioController::class, 'destroy']
)->whereNumber('id');


/*
|--------------------------------------------------------------------------
| Content Creator - Social Media Profiles
|--------------------------------------------------------------------------
*/

Route::get(
    '/social-media-profiles',
    [SocialMediaProfileController::class, 'index']
);

Route::post(
    '/social-media-profiles',
    [SocialMediaProfileController::class, 'store']
);

Route::get(
    '/social-media-profiles/{id}',
    [SocialMediaProfileController::class, 'show']
)->whereNumber('id');

Route::put(
    '/social-media-profiles/{id}',
    [SocialMediaProfileController::class, 'update']
)->whereNumber('id');

Route::delete(
    '/social-media-profiles/{id}',
    [SocialMediaProfileController::class, 'destroy']
)->whereNumber('id');
Route::get(
    '/portfolio-about',
    [
        PortfolioAboutController::class,
        'show',
    ]
);

Route::post(
    '/portfolio-about',
    [
        PortfolioAboutController::class,
        'store',
    ]
);

Route::put(
    '/portfolio-about',
    [
        PortfolioAboutController::class,
        'update',
    ]
);

Route::delete(
    '/portfolio-about',
    [
        PortfolioAboutController::class,
        'destroy',
    ]
);
Route::get(
    '/contact-information',
    [ContactInformationController::class, 'index']
);

Route::post(
    '/contact-information',
    [ContactInformationController::class, 'store']
);

Route::put(
    '/contact-information/{contactInformation}',
    [ContactInformationController::class, 'update']
);

Route::delete(
    '/contact-information/{contactInformation}',
    [ContactInformationController::class, 'destroy']
);
Route::middleware('auth')->get(
    '/portfolio/preview',
    [PortfolioPreviewController::class, 'index']
);
Route::get(
    '/profession-modules/overview',
    [ProfessionModuleOverviewController::class, 'index']
);
Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::prefix('portfolio')->group(function () {
    Route::get(
        '/publish-settings',
        [PortfolioPublishController::class, 'show']
    );

    Route::post(
        '/check-slug',
        [PortfolioPublishController::class, 'checkSlug']
    );

    Route::post(
        '/publish',
        [PortfolioPublishController::class, 'publish']
    );

    Route::post(
        '/unpublish',
        [PortfolioPublishController::class, 'unpublish']
    );
});
Route::middleware('auth:api')->group(function () {

    Route::get(
        '/analytics/summary',
        [AnalyticsController::class, 'summary']
    );

});
Route::post(
    '/analytics/track',
    [AnalyticsController::class, 'track']
);
Route::get(
    '/analytics/details',
    [AnalyticsController::class,'details']
);
Route::post(
    '/analytics/resume-download',
    [AnalyticsController::class, 'trackResumeDownload']
);
Route::prefix('settings')->group(function () {
    Route::get(
        '/',
        [SettingsController::class, 'show']
    );

    Route::put(
        '/profile',
        [SettingsController::class, 'updateProfile']
    );

    Route::put(
        '/account',
        [SettingsController::class, 'updateAccount']
    );

    Route::put(
        '/password',
        [SettingsController::class, 'updatePassword']
    );

    Route::put(
        '/notifications',
        [
            SettingsController::class,
            'updateNotifications',
        ]
    );

    Route::put(
        '/appearance',
        [
            SettingsController::class,
            'updateAppearance',
        ]
    );

    Route::delete(
        '/account',
        [
            SettingsController::class,
            'destroyAccount',
        ]
    );
    });
    Route::get(
    '/package-access',
    [
        PackageAccessController::class,
        'show',
    ]
);
Route::get(
    '/resumes/{resume}/preview',
    [
        ResumeController::class,
        'preview',
    ]
);

Route::get(
    '/resumes/{resume}/download',
    [
        ResumeController::class,
        'download',
    ]
);

Route::get(
    '/certificates/{certificate}/preview',
    [
        CertificateController::class,
        'preview',
    ]
);

Route::get(
    '/certificates/{certificate}/download',
    [
        CertificateController::class,
        'download',
    ]
);

});

Route::post('/public/social-links/{socialLink}/click', [
    SocialLinkController::class,
    'trackClick',
]);