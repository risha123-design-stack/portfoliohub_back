<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicProjectRequest extends FormRequest
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

            'course' => trim(
                (string) $this->course
            ),

            'subject' => trim(
                (string) $this->subject
            ),

            'grade' =>
                $this->grade === ''
                    ? null
                    : trim(
                        (string) $this->grade
                    ),

            'technologies' => trim(
                (string) $this->technologies
            ),

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),

            'start_date' =>
                $this->start_date === ''
                    ? null
                    : $this->start_date,

            'end_date' =>
                $status === 'Completed' &&
                $this->end_date !== ''
                    ? $this->end_date
                    : null,

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

            'course' => [
                'required',
                'string',
                'max:255',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'project_type' => [
                'required',
                'in:Individual Project,Group Project,Research Project,Capstone Project',
            ],

            'start_date' => [
                'required_if:status,In Progress,Completed,On Hold',
                'nullable',
                'date',
            ],

            'end_date' => [
                'required_if:status,Completed',
                'prohibited_unless:status,Completed',
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'required',
                'in:Planned,In Progress,Completed,On Hold',
            ],

            'grade' => [
                'nullable',
                'string',
                'max:20',
            ],

            'technologies' => [
                'required',
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
            'start_date.required_if' =>
                'Start date is required for this academic project status.',

            'end_date.required_if' =>
                'End date is required for a completed academic project.',

            'end_date.prohibited_unless' =>
                'End date can only be entered for a completed academic project.',

            'end_date.after_or_equal' =>
                'End date cannot be earlier than the start date.',
        ];
    }
}
