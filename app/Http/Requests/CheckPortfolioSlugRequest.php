<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckPortfolioSlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' =>
                'The portfolio address may contain only lowercase letters, numbers and single hyphens.',
        ];
    }
}