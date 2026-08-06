<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhotographyPortfolioRequest extends FormRequest
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

            'location' => trim(
                (string) $this->location
            ),

            'camera' =>
                $this->camera === ''
                    ? null
                    : trim(
                        (string) $this->camera
                    ),

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),

            'project_date' =>
                $this->project_date === ''
                    ? null
                    : $this->project_date,
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
                'max:150',
            ],

            'location' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'project_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'camera' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',

                Rule::in([
                    'Published',
                    'Draft',
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
                'Portfolio project title is required.',

            'title.min' =>
                'Portfolio project title must contain at least 2 characters.',

            'category.required' =>
                'Photography category is required.',

            'location.required' =>
                'Project location is required.',

            'project_date.before_or_equal' =>
                'Project date cannot be in the future.',
        ];
    }
}
