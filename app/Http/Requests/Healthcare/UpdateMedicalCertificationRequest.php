<?php

namespace App\Http\Requests\Healthcare;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim(
                (string) $this->title
            ),

            'issuer' => trim(
                (string) $this->issuer
            ),

            'category' => trim(
                (string) $this->category
            ),

            'certificateNumber' =>
                $this->certificateNumber === ''
                    ? null
                    : trim(
                        (string) $this->certificateNumber
                    ),

            'issueDate' =>
                $this->issueDate === ''
                    ? null
                    : $this->issueDate,

            'expiryDate' =>
                $this->expiryDate === ''
                    ? null
                    : $this->expiryDate,

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

            'issuer' => [
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

            'certificateNumber' => [
                'nullable',
                'string',
                'max:255',
            ],

            'issueDate' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'expiryDate' => [
                'required_if:status,Expired,Expiring Soon',
                'nullable',
                'date',
                'after_or_equal:issueDate',

                Rule::when(
                    $this->input(
                        'status'
                    ) === 'Expired',
                    [
                        'before_or_equal:today',
                    ]
                ),

                Rule::when(
                    $this->input(
                        'status'
                    ) === 'Active',
                    [
                        'after_or_equal:today',
                    ]
                ),
            ],

            'status' => [
                'required',

                Rule::in([
                    'Active',
                    'Expired',
                    'Expiring Soon',
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
                'Certification title is required.',

            'issuer.required' =>
                'Certification issuer is required.',

            'category.required' =>
                'Certification category is required.',

            'issueDate.required' =>
                'Issue date is required.',

            'issueDate.before_or_equal' =>
                'Issue date cannot be in the future.',

            'expiryDate.required_if' =>
                'Expiry date is required for this certification status.',

            'expiryDate.after_or_equal' =>
                'Expiry date cannot be earlier than issue date.',

            'expiryDate.before_or_equal' =>
                'An expired certification cannot have a future expiry date.',

            'status.in' =>
                'Select a valid certification status.',
        ];
    }
}
