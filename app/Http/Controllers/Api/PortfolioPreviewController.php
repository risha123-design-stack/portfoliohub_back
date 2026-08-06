<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioPreviewResource;
use Illuminate\Http\Request;

class PortfolioPreviewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Load Common Modules
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
            'socialLinks'
        ]);

        // Load Profession Specific Modules
        switch ($user->profession) {

            case 'Software Developer':
                $user->load([
                    'techStacks',
                    'codingProfiles',
                    'developerProjects'
                ]);
                break;

            case 'Teacher':
                $user->load([
                    'teachingSubjects',
                    'workshops'
                ]);
                break;

            case 'UI/UX Designer':
                $user->load([
                    'designTools',
                    'caseStudies'
                ]);
                break;

            case 'Photographer':
                $user->load([
                    'cameraEquipment',
                    'photographyPortfolios'
                ]);
                break;

            case 'Healthcare Professional':
                $user->load([
                    'medicalCertifications',
                    'clinicalExperiences'
                ]);
                break;

            case 'Freelancer':
                $user->load([
                    'clientProjects',
                    'clientTestimonials'
                ]);
                break;

            case 'Student':
                $user->load([
                    'academicProjects',
                    'studentCertifications'
                ]);
                break;

            case 'Content Creator':
                $user->load([
                    'contentPortfolios',
                    'socialMediaProfiles'
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => new PortfolioPreviewResource($user)
        ]);
    }
}