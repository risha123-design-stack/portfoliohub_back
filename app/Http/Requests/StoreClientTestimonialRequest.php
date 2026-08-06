<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'client_name' => trim(
                (string) $this->client_name
            ),

            'company' => trim(
                (string) $this->company
            ),

            'project' => trim(
                (string) $this->project
            ),

            'rating' =>
                $this->rating === '' ||
                $this->rating === null
                    ? 5
                    : (int) $this->rating,

            'testimonial_date' =>
                $this->testimonial_date === ''
                    ? null
                    : $this->testimonial_date,

            'testimonial' => trim(
                (string) $this->testimonial
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'client_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'company' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'project' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'testimonial_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'status' => [
                'required',

                Rule::in([
                    'Published',
                    'Draft',
                    'Hidden',
                ]),
            ],

            'testimonial' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.required' =>
                'Client name is required.',

            'company.required' =>
                'Company name is required.',

            'project.required' =>
                'Project name is required.',

            'rating.between' =>
                'Rating must be between 1 and 5.',

            'testimonial_date.before_or_equal' =>
                'Testimonial date cannot be in the future.',

            'testimonial.required' =>
                'Client testimonial is required.',

            'testimonial.min' =>
                'Testimonial must contain at least 10 characters.',

            'status.in' =>
                'Please select a valid testimonial status.',
        ];
    }
}
