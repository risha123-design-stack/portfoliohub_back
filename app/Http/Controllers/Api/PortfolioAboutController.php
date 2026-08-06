<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioAbout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioAboutController extends Controller
{
    /**
     * Return the About section belonging
     * to the authenticated user.
     */
    public function show()
    {
        $about = PortfolioAbout::where(
            'user_id',
            Auth::id()
        )->first();

        return response()->json([
            'success' => true,
            'about' => $about,
        ]);
    }

    /**
     * Create the authenticated user's
     * About section.
     *
     * A user can have only one About record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->validationRules()
        );

        $validated = $this->prepareData(
            $validated
        );

        $existingAbout =
            PortfolioAbout::where(
                'user_id',
                Auth::id()
            )->first();

        if ($existingAbout) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Your About section already exists. Please update the existing About section.',
            ], 409);
        }

        $validated['user_id'] =
            Auth::id();

        $about = PortfolioAbout::create(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'About section created successfully.',
            'about' => $about,
        ], 201);
    }

    /**
     * Update the authenticated user's
     * existing About section.
     */
    public function update(Request $request)
    {
        $about = PortfolioAbout::where(
            'user_id',
            Auth::id()
        )->first();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' =>
                    'About section not found.',
            ], 404);
        }

        $validated = $request->validate(
            $this->validationRules()
        );

        $validated = $this->prepareData(
            $validated
        );

        $about->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'About section updated successfully.',
            'about' => $about->fresh(),
        ]);
    }

    /**
     * Delete the authenticated user's
     * About section.
     */
    public function destroy()
    {
        $about = PortfolioAbout::where(
            'user_id',
            Auth::id()
        )->first();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' =>
                    'About section not found.',
            ], 404);
        }

        $about->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'About section deleted successfully.',
        ]);
    }

    /**
     * Validation rules shared by
     * store and update methods.
     */
    private function validationRules(): array
    {
        return [
            'professional_headline' => [
                'nullable',
                'string',
                'max:255',
            ],

            'about' => [
                'required',
                'string',
                'min:20',
                'max:5000',
            ],
        ];
    }

    /**
     * Clean and normalize incoming values.
     */
    private function prepareData(
        array $validated
    ): array {
        $validated[
            'professional_headline'
        ] = isset(
            $validated[
                'professional_headline'
            ]
        )
            ? trim(
                $validated[
                    'professional_headline'
                ]
            )
            : null;

        $validated['about'] = trim(
            $validated['about']
        );

        if (
            $validated[
                'professional_headline'
            ] === ''
        ) {
            $validated[
                'professional_headline'
            ] = null;
        }

        return $validated;
    }
}