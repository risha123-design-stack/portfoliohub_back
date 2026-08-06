<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishPortfolioRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

                Rule::unique(
                    'portfolio_publications',
                    'slug'
                )->ignore($userId, 'user_id'),
            ],

            'visibility' => [
                'required',
                Rule::in([
                    'public',
                    'private',
                    'password',
                ]),
            ],

            'portfolio_password' => [
                Rule::requiredIf(
                    fn () =>
                        $this->input('visibility') === 'password'
                ),
                'nullable',
                'string',
                'min:6',
                'max:100',
            ],

            'seo.title' => [
                'nullable',
                'string',
                'max:60',
            ],

            'seo.description' => [
                'nullable',
                'string',
                'max:160',
            ],

            'seo.keywords' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'seo.allow_search_engines' => [
                'required',
                'boolean',
            ],

            'selected_template' => [
                'nullable',
                'array',
            ],

            'selected_template.id' => [
                'nullable',
            ],

            'selected_template.name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'selected_template.category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'selected_template.packageName' => [
                'nullable',
                'string',
                'max:50',
            ],

            'selected_template.style' => [
                'nullable',
                'string',
                'max:100',
            ],

            'enabled_modules' => [
                'nullable',
                'array',
            ],

            'enabled_modules.*' => [
                'boolean',
            ],
            'completion_percentage' => [
    'required',
    'integer',
    'min:0',
    'max:100',
],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' =>
                'This portfolio address is already being used.',

            'slug.regex' =>
                'The portfolio address may contain only lowercase letters, numbers and hyphens.',

            'portfolio_password.required' =>
                'A password is required for a password-protected portfolio.',
        ];
    }
}