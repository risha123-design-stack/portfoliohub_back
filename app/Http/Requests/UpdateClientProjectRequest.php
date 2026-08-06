<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $budget = $this->budget;

        if (is_string($budget)) {
            $budget = preg_replace(
                '/[^0-9.]/',
                '',
                $budget
            );
        }

        $this->merge([
            'title' => trim(
                (string) $this->title
            ),

            'client' => trim(
                (string) $this->client
            ),

            'category' => trim(
                (string) $this->category
            ),

            'budget' =>
                $budget === '' ||
                $budget === null
                    ? null
                    : $budget,

            'deadline' =>
                $this->deadline === ''
                    ? null
                    : $this->deadline,

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

            'client' => [
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

            'budget' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'deadline' => [
                'nullable',
                'date',

                Rule::when(
                    $this->input(
                        'status'
                    ) === 'In Progress',
                    [
                        'after_or_equal:today',
                    ]
                ),
            ],

            'status' => [
                'required',

                Rule::in([
                    'In Progress',
                    'Completed',
                    'Cancelled',
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
                'Project title is required.',

            'title.min' =>
                'Project title must contain at least 2 characters.',

            'client.required' =>
                'Client name is required.',

            'client.min' =>
                'Client name must contain at least 2 characters.',

            'category.required' =>
                'Project category is required.',

            'budget.numeric' =>
                'Budget must be a valid amount.',

            'deadline.after_or_equal' =>
                'An in-progress project cannot have a past deadline.',

            'status.in' =>
                'Please select a valid project status.',
        ];
    }
}
