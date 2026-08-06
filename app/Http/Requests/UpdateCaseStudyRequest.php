<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseStudyRequest extends FormRequest
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

            'client' => trim(
                (string) $this->client
            ),

            'duration' =>
                $this->duration === ''
                    ? null
                    : trim(
                        (string) $this->duration
                    ),

            'tools' =>
                $this->tools === ''
                    ? null
                    : trim(
                        (string) $this->tools
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
                'max:255',
            ],

            'client' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'duration' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tools' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',

                Rule::in([
                    'Completed',
                    'In Progress',
                    'Planned',
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
            'title.required' =>
                'Case study title is required.',

            'title.min' =>
                'Case study title must contain at least 2 characters.',

            'category.required' =>
                'Case study category is required.',

            'client.required' =>
                'Client name is required.',

            'status.in' =>
                'Select a valid case study status.',
        ];
    }
}
