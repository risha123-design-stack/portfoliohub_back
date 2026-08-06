<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCameraEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(
                (string) $this->name
            ),

            'brand' => trim(
                (string) $this->brand
            ),

            'model' => trim(
                (string) $this->model
            ),

            'description' =>
                $this->description === ''
                    ? null
                    : trim(
                        (string) $this->description
                    ),

            'purchase_year' =>
                $this->purchase_year === ''
                    ? null
                    : $this->purchase_year,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'type' => [
                'required',

                Rule::in([
                    'Camera',
                    'Lens',
                    'Lighting',
                    'Tripod',
                    'Drone',
                    'Accessory',
                ]),
            ],

            'brand' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'model' => [
                'required',
                'string',
                'max:255',
            ],

            'condition' => [
                'required',

                Rule::in([
                    'Excellent',
                    'Good',
                    'Fair',
                    'Needs Repair',
                ]),
            ],

            'purchase_year' => [
                'nullable',
                'integer',
                'min:1900',
                'max:' . now()->year,
            ],

            'status' => [
                'required',

                Rule::in([
                    'Available',
                    'In Use',
                    'Under Maintenance',
                    'Unavailable',
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
            'name.required' =>
                'Equipment name is required.',

            'name.min' =>
                'Equipment name must contain at least 2 characters.',

            'brand.required' =>
                'Brand is required.',

            'brand.min' =>
                'Brand must contain at least 2 characters.',

            'model.required' =>
                'Model is required.',

            'purchase_year.max' =>
                'Purchase year cannot be in the future.',
        ];
    }
}
