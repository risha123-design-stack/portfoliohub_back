<?php

namespace App\Http\Requests\Healthcare;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicalExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    protected function prepareForValidation(): void
    {
        $status = trim(
            (string) $this->input(
                'status'
            )
        );

        $endDate = $this->input(
            'endDate'
        );

        $this->merge([
            'hospital' =>
                is_string($this->hospital)
                    ? trim($this->hospital)
                    : $this->hospital,

            'department' =>
                is_string(
                    $this->department
                )
                    ? trim(
                        $this->department
                    )
                    : $this->department,

            'role' =>
                is_string($this->role)
                    ? trim($this->role)
                    : $this->role,

            'description' =>
                is_string(
                    $this->description
                )
                    ? trim(
                        $this->description
                    )
                    : $this->description,

            'status' => $status,

            'endDate' =>
                $status === 'Completed' &&
                is_string($endDate) &&
                trim($endDate) !== ''
                    ? trim($endDate)
                    : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'hospital' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'department' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'role' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'startDate' => [
                'required',
                'date',
            ],

            'endDate' => [
                'required_if:status,Completed',
                'prohibited_if:status,Current',
                'nullable',
                'date',
                'after_or_equal:startDate',
            ],

            'status' => [
                'required',
                Rule::in([
                    'Current',
                    'Completed',
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
            'hospital.required' =>
                'Hospital name is required.',

            'department.required' =>
                'Department is required.',

            'role.required' =>
                'Clinical role is required.',

            'startDate.required' =>
                'Start date is required.',

            'endDate.required_if' =>
                'End date is required for a completed clinical experience.',

            'endDate.prohibited_if' =>
                'Current clinical experiences cannot have an end date.',

            'endDate.after_or_equal' =>
                'End date must be the same as or after the start date.',

            'status.in' =>
                'Clinical experience status must be Current or Completed.',
        ];
    }
}
