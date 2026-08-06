<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkshopRequest extends FormRequest
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

            'organizer' => trim(
                (string) $this->organizer
            ),

            'location' => trim(
                (string) $this->location
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

            'organizer' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'location' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'workshop_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'certificate' => [
                'required',

                Rule::in([
                    'Yes',
                    'No',
                ]),
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
            'title.required' =>
                'Workshop title is required.',

            'title.min' =>
                'Workshop title must contain at least 2 characters.',

            'organizer.required' =>
                'Organizer is required.',

            'organizer.min' =>
                'Organizer name must contain at least 2 characters.',

            'location.required' =>
                'Location is required.',

            'location.min' =>
                'Location must contain at least 2 characters.',

            'workshop_date.required' =>
                'Workshop date is required.',

            'workshop_date.before_or_equal' =>
                'Workshop date cannot be in the future.',

            'certificate.in' =>
                'Certificate must be Yes or No.',
        ];
    }
}
