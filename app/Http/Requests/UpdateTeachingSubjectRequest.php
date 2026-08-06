<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeachingSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'subject' => trim(
                (string) $this->subject
            ),

            'grade' => trim(
                (string) $this->grade
            ),

            'medium' => trim(
                (string) $this->medium
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
            'subject' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'grade' => [
                'required',
                'string',
                'max:100',
            ],

            'medium' => [
                'required',

                Rule::in([
                    'English',
                    'Tamil',
                    'Sinhala',
                ]),
            ],

            'experience' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' =>
                'Subject name is required.',

            'subject.min' =>
                'Subject name must contain at least 2 characters.',

            'grade.required' =>
                'Grade is required.',

            'medium.required' =>
                'Teaching medium is required.',

            'medium.in' =>
                'Medium must be English, Tamil or Sinhala.',
        ];
    }
}
