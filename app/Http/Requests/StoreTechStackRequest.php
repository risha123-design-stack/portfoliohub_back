<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTechStackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => trim(
                (string) $this->category
            ),

            'technology' => trim(
                (string) $this->technology
            ),

            'level' => trim(
                (string) $this->level
            ),

            'experience' => trim(
                (string) $this->experience
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => [
                'required',

                Rule::in([
                    'Programming Language',
                    'Framework',
                    'Database',
                    'Tool',
                    'Cloud / DevOps',
                    'Other',
                ]),
            ],

            'technology' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'level' => [
                'required',

                Rule::in([
                    'Beginner',
                    'Intermediate',
                    'Advanced',
                    'Expert',
                ]),
            ],

            'experience' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' =>
                'Select a valid technology category.',

            'technology.required' =>
                'Technology name is required.',

            'technology.min' =>
                'Technology name must contain at least 2 characters.',

            'level.in' =>
                'Select a valid skill level.',

            'experience.required' =>
                'Experience is required.',
        ];
    }
}
