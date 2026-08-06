<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $status = trim(
            (string) $this->status
        );

        $this->merge([
            'title' => trim(
                (string) $this->title
            ),

            'provider' => trim(
                (string) $this->provider
            ),

            'category' => trim(
                (string) $this->category
            ),

            'credential_id' =>
                $this->credential_id === ''
                    ? null
                    : trim(
                        (string) $this->credential_id
                    ),

            'skills' =>
                $this->skills === ''
                    ? null
                    : trim(
                        (string) $this->skills
                    ),

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),

            'issue_date' =>
                $status === 'In Progress' ||
                $this->issue_date === ''
                    ? null
                    : $this->issue_date,

            'expiry_date' =>
                $status === 'In Progress' ||
                $this->expiry_date === ''
                    ? null
                    : $this->expiry_date,

            'status' => $status,
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

            'provider' => [
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

            'issue_date' => [
                'required_if:status,Completed,Expired',
                'prohibited_if:status,In Progress',
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'expiry_date' => [
                'required_if:status,Expired',
                'prohibited_if:status,In Progress',
                'nullable',
                'date',
                'after_or_equal:issue_date',
                Rule::when(
                    $this->input('status') ===
                        'Expired',
                    [
                        'before_or_equal:today',
                    ]
                ),
            ],

            'credential_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',

                Rule::in([
                    'Completed',
                    'In Progress',
                    'Expired',
                ]),
            ],

            'skills' => [
                'nullable',
                'string',
                'max:500',
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
                'Certification title is required.',

            'provider.required' =>
                'Certification provider is required.',

            'category.required' =>
                'Certification category is required.',

            'issue_date.required_if' =>
                'Issue date is required for a completed or expired certification.',

            'issue_date.prohibited_if' =>
                'In-progress certifications cannot have an issue date.',

            'issue_date.before_or_equal' =>
                'Issue date cannot be in the future.',

            'expiry_date.required_if' =>
                'Expiry date is required for an expired certification.',

            'expiry_date.prohibited_if' =>
                'In-progress certifications cannot have an expiry date.',

            'expiry_date.after_or_equal' =>
                'Expiry date cannot be earlier than the issue date.',

            'expiry_date.before_or_equal' =>
                'An expired certification cannot have a future expiry date.',
        ];
    }
}
