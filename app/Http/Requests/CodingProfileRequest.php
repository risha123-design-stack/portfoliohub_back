<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CodingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'platform' => trim(
                (string) $this->platform
            ),

            'username' => trim(
                (string) $this->username
            ),

            'rating' =>
                $this->rating === ''
                    ? null
                    : trim(
                        (string) $this->rating
                    ),

            'rank' =>
                $this->rank === ''
                    ? null
                    : trim(
                        (string) $this->rank
                    ),

            'profileUrl' => trim(
                (string) $this->profileUrl
            ),
        ]);
    }

    public function rules(): array
    {
        $codingProfile =
            $this->route(
                'coding_profile'
            );

        $codingProfileId =
            is_object($codingProfile)
                ? $codingProfile->id
                : $codingProfile;

        return [
            'platform' => [
                'required',
                'string',
                'max:50',

                Rule::in([
                    'LeetCode',
                    'HackerRank',
                    'Codeforces',
                    'CodeChef',
                    'GitHub',
                ]),
            ],

            'username' => [
                'required',
                'string',
                'min:2',
                'max:100',

                Rule::unique(
                    'coding_profiles',
                    'username'
                )
                    ->where(
                        function (
                            $query
                        ) {
                            return $query
                                ->where(
                                    'user_id',
                                    auth('api')->id()
                                )
                                ->where(
                                    'platform',
                                    $this->input(
                                        'platform'
                                    )
                                );
                        }
                    )
                    ->ignore(
                        $codingProfileId
                    ),
            ],

            'rating' => [
                'nullable',
                'string',
                'max:50',
            ],

            'solved' => [
                'nullable',
                'integer',
                'min:0',
                'max:10000000',
            ],

            'rank' => [
                'nullable',
                'string',
                'max:100',
            ],

            'stars' => [
                'nullable',
                'integer',
                'min:0',
                'max:10000000',
            ],

            'profileUrl' => [
                'required',
                'url:http,https',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'platform.required' =>
                'Platform is required.',

            'platform.in' =>
                'Please select a valid coding platform.',

            'username.required' =>
                'Username is required.',

            'username.min' =>
                'Username must contain at least 2 characters.',

            'username.unique' =>
                'This coding profile has already been added.',

            'solved.integer' =>
                'Problems solved must be a whole number.',

            'solved.min' =>
                'Problems solved cannot be negative.',

            'stars.integer' =>
                'Stars must be a whole number.',

            'stars.min' =>
                'Stars cannot be negative.',

            'profileUrl.required' =>
                'Profile URL is required.',

            'profileUrl.url' =>
                'Profile URL must be a valid HTTP or HTTPS URL.',
        ];
    }
}
