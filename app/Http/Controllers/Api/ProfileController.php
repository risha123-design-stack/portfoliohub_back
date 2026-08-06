<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
{
    $user = auth('api')->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'user' => [
            'id' => $user->id,
            'fullName' => $user->name,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,

            'profession' => $user->profession,
            'careerGoal' => $user->career_goal,
            'career_goal' => $user->career_goal,
            'packageName' => $user->package_name,
            'package_name' => $user->package_name,

            'location' => $user->location,
            'website' => $user->website,
            'current_position' => $user->current_position,
            'experience_years' => $user->experience_years,
            'bio' => $user->bio,

            'github' => $user->github,
            'linkedin' => $user->linkedin,
            'facebook' => $user->facebook,
            'instagram' => $user->instagram,
            'twitter' => $user->twitter,
            'profile_photo' => $user->profile_photo,
        ],
    ]);
}
    public function update(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'fullName' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'current_position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'experience_years' => [
                'nullable',
                'integer',
                'min:0',
                'max:80',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'github' => [
                'nullable',
                'url',
                'max:255',
            ],

            'linkedin' => [
                'nullable',
                'url',
                'max:255',
            ],

            'facebook' => [
                'nullable',
                'url',
                'max:255',
            ],

            'instagram' => [
                'nullable',
                'url',
                'max:255',
            ],

            'twitter' => [
                'nullable',
                'url',
                'max:255',
            ],
        ]);

        $user->name = $validated['fullName'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->location = $validated['location'] ?? null;
        $user->website = $validated['website'] ?? null;
        $user->current_position =
            $validated['current_position'] ?? null;
        $user->experience_years =
            $validated['experience_years'] ?? null;
        $user->bio = $validated['bio'] ?? null;
        $user->github = $validated['github'] ?? null;
        $user->linkedin = $validated['linkedin'] ?? null;
        $user->facebook = $validated['facebook'] ?? null;
        $user->instagram = $validated['instagram'] ?? null;
        $user->twitter = $validated['twitter'] ?? null;

        $user->save();

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'fullName' => $user->name,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,

                'profession' => $user->profession,
                'careerGoal' => $user->career_goal,
                'career_goal' => $user->career_goal,
                'packageName' => $user->package_name,
                'package_name' => $user->package_name,

                'location' => $user->location,
                'website' => $user->website,
                'current_position' => $user->current_position,
                'experience_years' => $user->experience_years,
                'bio' => $user->bio,

                'github' => $user->github,
                'linkedin' => $user->linkedin,
                'facebook' => $user->facebook,
                'instagram' => $user->instagram,
                'twitter' => $user->twitter,
                'profile_photo' => $user->profile_photo,
            ],
        ]);
    }
}