<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PortfolioProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $fullName = $user->fullName
            ?? $user->full_name
            ?? $user->name
            ?? '';

        $nameParts = preg_split('/\s+/', trim($fullName), 2);

        $profile = PortfolioProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'display_name' => $fullName,
                'professional_title' => $user->profession ?? '',
                'profession' => $user->profession ?? '',
                'career_objective' => $user->careerGoal
                    ?? $user->career_goal
                    ?? '',
                'public_email' => $user->email ?? '',
                'primary_phone' => $user->phone ?? '',
                'country' => 'Sri Lanka',
            ]
        );

        return response()->json([
            'success' => true,
            'profile' => $this->formatProfile($profile),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'display_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'date_of_birth' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'gender' => [
                'nullable',
                'string',
                'max:50',
            ],

            'nationality' => [
                'nullable',
                'string',
                'max:100',
            ],

            'languages' => [
                'nullable',
                'array',
            ],

            'languages.*' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Professional Information
            |--------------------------------------------------------------------------
            */

            'professional_title' => [
                'nullable',
                'string',
                'max:200',
            ],

            'profession' => [
                'nullable',
                'string',
                'max:150',
            ],

            'specialization' => [
                'nullable',
                'string',
                'max:200',
            ],

            'current_position' => [
                'nullable',
                'string',
                'max:200',
            ],

            'company' => [
                'nullable',
                'string',
                'max:200',
            ],

            'experience_years' => [
                'nullable',
                'integer',
                'min:0',
                'max:80',
            ],

            'tagline' => [
                'nullable',
                'string',
                'max:300',
            ],

            'career_objective' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
            |--------------------------------------------------------------------------
            | About
            |--------------------------------------------------------------------------
            */

            'short_introduction' => [
                'nullable',
                'string',
                'max:500',
            ],

            'about_me' => [
                'nullable',
                'string',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Contact Information
            |--------------------------------------------------------------------------
            */

            'public_email' => [
                'nullable',
                'email',
                'max:190',
            ],

            'primary_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'secondary_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'website' => [
                'nullable',
                'url',
                'max:500',
            ],

            /*
            |--------------------------------------------------------------------------
            | Address Information
            |--------------------------------------------------------------------------
            */

            'address_line_1' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address_line_2' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:120',
            ],

            'district' => [
                'nullable',
                'string',
                'max:120',
            ],

            'province' => [
                'nullable',
                'string',
                'max:120',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'country' => [
                'nullable',
                'string',
                'max:120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Social Links
            |--------------------------------------------------------------------------
            */

            'github' => [
                'nullable',
                'url',
                'max:500',
            ],

            'linkedin' => [
                'nullable',
                'url',
                'max:500',
            ],

            'facebook' => [
                'nullable',
                'url',
                'max:500',
            ],

            'instagram' => [
                'nullable',
                'url',
                'max:500',
            ],

            'twitter' => [
                'nullable',
                'url',
                'max:500',
            ],

            'youtube' => [
                'nullable',
                'url',
                'max:500',
            ],

            'behance' => [
                'nullable',
                'url',
                'max:500',
            ],

            'dribbble' => [
                'nullable',
                'url',
                'max:500',
            ],

            'portfolio_link' => [
                'nullable',
                'url',
                'max:500',
            ],

            /*
            |--------------------------------------------------------------------------
            | File Uploads
            |--------------------------------------------------------------------------
            */

            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                Rule::dimensions()
                    ->maxWidth(5000)
                    ->maxHeight(5000),
                'max:4096',
            ],

            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                Rule::dimensions()
                    ->maxWidth(8000)
                    ->maxHeight(5000),
                'max:6144',
            ],

            'resume' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Visibility Settings
            |--------------------------------------------------------------------------
            */

            'show_email' => [
                'nullable',
                'boolean',
            ],

            'show_phone' => [
                'nullable',
                'boolean',
            ],

            'show_date_of_birth' => [
                'nullable',
                'boolean',
            ],

            'show_address' => [
                'nullable',
                'boolean',
            ],

            'show_resume' => [
                'nullable',
                'boolean',
            ],

            'show_social_links' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Files
            |--------------------------------------------------------------------------
            */

            'remove_profile_image' => [
                'nullable',
                'boolean',
            ],

            'remove_cover_image' => [
                'nullable',
                'boolean',
            ],

            'remove_resume' => [
                'nullable',
                'boolean',
            ],
        ]);

        $profile = PortfolioProfile::firstOrCreate([
            'user_id' => $user->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove Existing Files
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_profile_image')) {
            $this->deletePublicFile($profile->profile_image);
            $profile->profile_image = null;
        }

        if ($request->boolean('remove_cover_image')) {
            $this->deletePublicFile($profile->cover_image);
            $profile->cover_image = null;
        }

        if ($request->boolean('remove_resume')) {
            $this->deletePublicFile($profile->resume);
            $profile->resume = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Upload New Files
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('profile_image')) {
            $this->deletePublicFile($profile->profile_image);

            $profile->profile_image = $request
                ->file('profile_image')
                ->store(
                    'portfolio/profile-images/' . $user->id,
                    'public'
                );
        }

        if ($request->hasFile('cover_image')) {
            $this->deletePublicFile($profile->cover_image);

            $profile->cover_image = $request
                ->file('cover_image')
                ->store(
                    'portfolio/cover-images/' . $user->id,
                    'public'
                );
        }

        if ($request->hasFile('resume')) {
            $this->deletePublicFile($profile->resume);

            $profile->resume = $request
                ->file('resume')
                ->store(
                    'portfolio/resumes/' . $user->id,
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Profile Information
        |--------------------------------------------------------------------------
        */

        $profile->fill([
            'first_name' => $this->nullableString(
                $validated['first_name'] ?? null
            ),

            'last_name' => $this->nullableString(
                $validated['last_name'] ?? null
            ),

            'display_name' => $this->nullableString(
                $validated['display_name'] ?? null
            ),

            'date_of_birth' => $validated['date_of_birth'] ?? null,

            'gender' => $this->nullableString(
                $validated['gender'] ?? null
            ),

            'nationality' => $this->nullableString(
                $validated['nationality'] ?? null
            ),

            'languages' => $this->cleanLanguages(
                $validated['languages'] ?? []
            ),

            'professional_title' => $this->nullableString(
                $validated['professional_title'] ?? null
            ),

            'profession' => $this->nullableString(
                $validated['profession'] ?? null
            ),

            'specialization' => $this->nullableString(
                $validated['specialization'] ?? null
            ),

            'current_position' => $this->nullableString(
                $validated['current_position'] ?? null
            ),

            'company' => $this->nullableString(
                $validated['company'] ?? null
            ),

            'experience_years' => $validated['experience_years']
                ?? null,

            'tagline' => $this->nullableString(
                $validated['tagline'] ?? null
            ),

            'career_objective' => $this->nullableString(
                $validated['career_objective'] ?? null
            ),

            'short_introduction' => $this->nullableString(
                $validated['short_introduction'] ?? null
            ),

            'about_me' => $this->nullableString(
                $validated['about_me'] ?? null
            ),

            'public_email' => $this->nullableString(
                $validated['public_email'] ?? null
            ),

            'primary_phone' => $this->nullableString(
                $validated['primary_phone'] ?? null
            ),

            'secondary_phone' => $this->nullableString(
                $validated['secondary_phone'] ?? null
            ),

            'website' => $this->nullableString(
                $validated['website'] ?? null
            ),

            'address_line_1' => $this->nullableString(
                $validated['address_line_1'] ?? null
            ),

            'address_line_2' => $this->nullableString(
                $validated['address_line_2'] ?? null
            ),

            'city' => $this->nullableString(
                $validated['city'] ?? null
            ),

            'district' => $this->nullableString(
                $validated['district'] ?? null
            ),

            'province' => $this->nullableString(
                $validated['province'] ?? null
            ),

            'postal_code' => $this->nullableString(
                $validated['postal_code'] ?? null
            ),

            'country' => $this->nullableString(
                $validated['country'] ?? null
            ),

            'github' => $this->nullableString(
                $validated['github'] ?? null
            ),

            'linkedin' => $this->nullableString(
                $validated['linkedin'] ?? null
            ),

            'facebook' => $this->nullableString(
                $validated['facebook'] ?? null
            ),

            'instagram' => $this->nullableString(
                $validated['instagram'] ?? null
            ),

            'twitter' => $this->nullableString(
                $validated['twitter'] ?? null
            ),

            'youtube' => $this->nullableString(
                $validated['youtube'] ?? null
            ),

            'behance' => $this->nullableString(
                $validated['behance'] ?? null
            ),

            'dribbble' => $this->nullableString(
                $validated['dribbble'] ?? null
            ),

            'portfolio_link' => $this->nullableString(
                $validated['portfolio_link'] ?? null
            ),

            'show_email' => $request->boolean('show_email'),
            'show_phone' => $request->boolean('show_phone'),

            'show_date_of_birth' => $request->boolean(
                'show_date_of_birth'
            ),

            'show_address' => $request->boolean('show_address'),
            'show_resume' => $request->boolean('show_resume'),

            'show_social_links' => $request->boolean(
                'show_social_links'
            ),
        ]);

        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio profile updated successfully.',
            'profile' => $this->formatProfile($profile->fresh()),
        ]);
    }

    private function formatProfile(
        PortfolioProfile $profile
    ): array {
        return [
            'id' => $profile->id,

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'first_name' => $profile->first_name ?? '',
            'last_name' => $profile->last_name ?? '',
            'full_name' => $profile->full_name ?? '',
            'display_name' => $profile->display_name ?? '',

            'date_of_birth' => $profile->date_of_birth
                ? $profile->date_of_birth->format('Y-m-d')
                : '',

            'gender' => $profile->gender ?? '',
            'nationality' => $profile->nationality ?? '',
            'languages' => $profile->languages ?? [],

            /*
            |--------------------------------------------------------------------------
            | Professional Information
            |--------------------------------------------------------------------------
            */

            'professional_title' => $profile->professional_title ?? '',
            'profession' => $profile->profession ?? '',
            'specialization' => $profile->specialization ?? '',
            'current_position' => $profile->current_position ?? '',
            'company' => $profile->company ?? '',

            'experience_years' => $profile->experience_years
                ?? '',

            'tagline' => $profile->tagline ?? '',
            'career_objective' => $profile->career_objective ?? '',

            /*
            |--------------------------------------------------------------------------
            | About
            |--------------------------------------------------------------------------
            */

            'short_introduction' => $profile->short_introduction ?? '',
            'about_me' => $profile->about_me ?? '',

            /*
            |--------------------------------------------------------------------------
            | Contact Information
            |--------------------------------------------------------------------------
            */

            'public_email' => $profile->public_email ?? '',
            'primary_phone' => $profile->primary_phone ?? '',
            'secondary_phone' => $profile->secondary_phone ?? '',
            'website' => $profile->website ?? '',

            /*
            |--------------------------------------------------------------------------
            | Address Information
            |--------------------------------------------------------------------------
            */

            'address_line_1' => $profile->address_line_1 ?? '',
            'address_line_2' => $profile->address_line_2 ?? '',
            'city' => $profile->city ?? '',
            'district' => $profile->district ?? '',
            'province' => $profile->province ?? '',
            'postal_code' => $profile->postal_code ?? '',
            'country' => $profile->country ?? '',

            /*
            |--------------------------------------------------------------------------
            | Uploaded Files
            |--------------------------------------------------------------------------
            */

            'profile_image' => $profile->profile_image ?? '',

            'profile_image_url' => $this->publicFileUrl(
                $profile->profile_image
            ),

            'cover_image' => $profile->cover_image ?? '',

            'cover_image_url' => $this->publicFileUrl(
                $profile->cover_image
            ),

            'resume' => $profile->resume ?? '',

            'resume_url' => $this->publicFileUrl(
                $profile->resume
            ),

            /*
            |--------------------------------------------------------------------------
            | Social Links
            |--------------------------------------------------------------------------
            */

            'github' => $profile->github ?? '',
            'linkedin' => $profile->linkedin ?? '',
            'facebook' => $profile->facebook ?? '',
            'instagram' => $profile->instagram ?? '',
            'twitter' => $profile->twitter ?? '',
            'youtube' => $profile->youtube ?? '',
            'behance' => $profile->behance ?? '',
            'dribbble' => $profile->dribbble ?? '',
            'portfolio_link' => $profile->portfolio_link ?? '',

            /*
            |--------------------------------------------------------------------------
            | Visibility Settings
            |--------------------------------------------------------------------------
            */

            'show_email' => (bool) $profile->show_email,
            'show_phone' => (bool) $profile->show_phone,

            'show_date_of_birth' => (bool)
                $profile->show_date_of_birth,

            'show_address' => (bool) $profile->show_address,
            'show_resume' => (bool) $profile->show_resume,

            'show_social_links' => (bool)
                $profile->show_social_links,
        ];
    }

    private function deletePublicFile(?string $path): void
    {
        if (
            $path &&
            Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }

    private function publicFileUrl(?string $path): string
    {
        return $path
            ? asset('storage/' . $path)
            : '';
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function cleanLanguages(array $languages): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        fn ($language) => trim((string) $language),
                        $languages
                    ),
                    fn ($language) => $language !== ''
                )
            )
        );
    }
}