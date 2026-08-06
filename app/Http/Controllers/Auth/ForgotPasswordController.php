<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(
        Request $request
    ): JsonResponse {
        $validated = $request->validate(
            [
                'email' => [
                    'required',
                    'email',
                    'exists:users,email',
                ],
            ],
            [
                'email.required' =>
                    'Email address is required.',

                'email.email' =>
                    'Enter a valid email address.',

                'email.exists' =>
                    'No account was found with this email address.',
            ]
        );

        try {
            $status = Password::sendResetLink([
                'email' => strtolower(
                    trim($validated['email'])
                ),
            ]);

            if (
                $status ===
                Password::RESET_LINK_SENT
            ) {
                return response()->json([
                    'success' => true,
                    'message' =>
                        'Password reset link has been sent to your email.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        } catch (
            ValidationException $exception
        ) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to send the password reset email. Please try again.',
            ], 500);
        }
    }
}