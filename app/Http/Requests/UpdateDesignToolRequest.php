<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDesignToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tool' => trim(
                (string) $this->tool
            ),

            'experience' =>
                $this->experience === ''
                    ? null
                    : trim(
                        (string) $this->experience
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
            'tool' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'category' => [
                'required',

                Rule::in([
                    'UI Design',
                    'UX Design',
                    'Graphics',
                    'Prototyping',
                    'Animation',
                ]),
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
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tool.required' =>
                'Tool name is required.',

            'tool.min' =>
                'Tool name must contain at least 2 characters.',

            'category.in' =>
                'Select a valid tool category.',

            'level.in' =>
                'Select a valid skill level.',
        ];
    }
}
