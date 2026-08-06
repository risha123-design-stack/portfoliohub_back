<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocialMediaProfileRequest extends FormRequest
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

            'profile_url' => trim(
                (string) $this->profile_url
            ),

            'followers' =>
                $this->followers === ''
                    ? null
                    : $this->followers,

            'content_type' =>
                $this->content_type === ''
                    ? null
                    : trim(
                        (string) $this->content_type
                    ),

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),
        ]);
    }

    public function rules(): array
    {
        return [
            'platform' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'username' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],

            'profile_url' => [
                'required',
                'url:http,https',
                'max:2048',
            ],

            'followers' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'content_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',

                Rule::in([
                    'Active',
                    'Inactive',
                    'Archived',
                ]),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'platform.required' =>
                'Platform is required.',

            'platform.min' =>
                'Platform must contain at least 2 characters.',

            'username.required' =>
                'Username is required.',

            'username.min' =>
                'Username must contain at least 2 characters.',

            'profile_url.required' =>
                'Profile URL is required.',

            'profile_url.url' =>
                'Profile URL must be a valid HTTP or HTTPS URL.',

            'followers.integer' =>
                'Followers must be a whole number.',

            'followers.min' =>
                'Followers cannot be negative.',

            'status.in' =>
                'Select a valid social media profile status.',
        ];
    }
}
