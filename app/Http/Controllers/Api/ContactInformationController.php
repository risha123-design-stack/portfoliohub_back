<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContactInformationController extends Controller
{
    /**
     * Return all contact information records
     * belonging to the authenticated user.
     */
    public function index()
    {
        $contacts = ContactInformation::where(
            'user_id',
            Auth::id()
        )
            ->orderBy('display_order')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Store new contact information.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->validationRules()
        );

        $validated = $this->prepareData(
            $validated
        );

        $validated['user_id'] = Auth::id();

        $contact = ContactInformation::create(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Contact information created successfully.',
            'contact' => $contact,
        ], 201);
    }

    /**
     * Update an existing contact information record.
     */
    public function update(
        Request $request,
        ContactInformation $contactInformation
    ) {
        if (
            (int) $contactInformation->user_id !==
            (int) Auth::id()
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'You are not authorized to update this contact information.',
            ], 403);
        }

        $validated = $request->validate(
            $this->validationRules()
        );

        $validated = $this->prepareData(
            $validated
        );

        $contactInformation->update(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Contact information updated successfully.',
            'contact' =>
                $contactInformation->fresh(),
        ]);
    }

    /**
     * Delete a contact information record.
     */
    public function destroy(
        ContactInformation $contactInformation
    ) {
        if (
            (int) $contactInformation->user_id !==
            (int) Auth::id()
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'You are not authorized to delete this contact information.',
            ], 403);
        }

        $contactInformation->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Contact information deleted successfully.',
        ]);
    }

    /**
     * Validation rules shared by
     * store and update.
     */
    private function validationRules(): array
    {
        return [
            'contact_type' => [
                'required',
                'string',
                Rule::in([
                    'Email',
                    'Phone',
                    'WhatsApp',
                    'Website',
                    'LinkedIn',
                    'GitHub',
                    'Facebook',
                    'Instagram',
                    'Twitter',
                    'YouTube',
                    'Address',
                    'Location',
                    'Other',
                ]),
            ],

            'label' => [
                'nullable',
                'string',
                'max:150',
            ],

            'value' => [
                'required',
                'string',
                'max:1000',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Clean and normalize request values.
     */
    private function prepareData(
        array $validated
    ): array {
        $validated['contact_type'] = trim(
            $validated['contact_type']
        );

        $validated['label'] =
            isset($validated['label'])
                ? trim($validated['label'])
                : null;

        $validated['value'] = trim(
            $validated['value']
        );

        $validated['display_order'] =
            $validated['display_order'] ?? 0;

        if ($validated['label'] === '') {
            $validated['label'] = null;
        }

        return $validated;
    }
}