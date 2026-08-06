<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeveloperProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim(
                (string) $this->title
            ),

            'category' => trim(
                (string) $this->category
            ),

            'tech_stack' => trim(
                (string) $this->tech_stack
            ),

            'github_url' =>
                $this->github_url === ''
                    ? null
                    : trim(
                        (string) $this->github_url
                    ),

            'live_demo_url' =>
                $this->live_demo_url === ''
                    ? null
                    : trim(
                        (string) $this->live_demo_url
                    ),

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),

            'status' => trim(
                (string) $this->status
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'category' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'tech_stack' => [
                'required',
                'string',
                'min:2',
                'max:1000',
            ],

            'github_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'live_demo_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'required',

                Rule::in([
                    'Completed',
                    'In Progress',
                    'Planned',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' =>
                'Project title is required.',

            'title.min' =>
                'Project title must contain at least 2 characters.',

            'category.required' =>
                'Project category is required.',

            'tech_stack.required' =>
                'Technology stack is required.',

            'github_url.url' =>
                'GitHub URL must be a valid HTTP or HTTPS URL.',

            'live_demo_url.url' =>
                'Live demo URL must be a valid HTTP or HTTPS URL.',

            'status.in' =>
                'Select a valid project status.',
        ];
    }
}
