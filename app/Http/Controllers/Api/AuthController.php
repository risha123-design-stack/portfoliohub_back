<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use App\Models\LoginOtp;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'fullName' => [
                'required',
                'string',
                'min:2',
                'max:150',
                "regex:/^[\pL\pM][\pL\pM'’.\-]*(?:\s+[\pL\pM][\pL\pM'’.\-]*)*$/u",
            ],

            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:150',
                Rule::unique(
                    'users',
                    'email'
                ),
            ],

            'phone' => [
                'required',
                'string',
                'min:9',
                'max:16',
                'regex:/^\+?[0-9][0-9\s-]{7,14}[0-9]$/',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',

                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'profession' => [
                'required',
                Rule::in([
                    'Software Developer',
                    'Teacher',
                    'UI/UX Designer',
                    'Photographer',
                    'Freelancer',
                    'Content Creator',
                    'Healthcare Professional',
                    'Student',
                ]),
            ],

            'careerGoal' => [
                'required',
                Rule::in([
                    'Get a Job',
                    'Find Freelance Clients',
                    'Build Personal Brand',
                    'Higher Studies',
                    'Grow Business',
                    'Start Career',
                ]),
            ],

            'packageName' => [
                'required',
                Rule::in([
                    'Silver',
                    'Gold',
                    'Platinum',
                ]),
            ],
        ], [
            'fullName.regex' =>
                'The full name may contain only letters, spaces, apostrophes, periods and hyphens.',

            'email.email' =>
                'Enter a valid email address.',

            'email.unique' =>
                'An account already exists with this email address.',

            'phone.regex' =>
                'Enter 9 to 15 digits. A leading +, spaces and hyphens are allowed.',

            'password.mixed' =>
                'The password must contain uppercase and lowercase letters.',

            'password.numbers' =>
                'The password must contain at least one number.',

            'password.symbols' =>
                'The password must contain at least one special character.',

            'password.confirmed' =>
                'The password confirmation does not match.',

            'profession.in' =>
                'Select a valid profession.',

            'careerGoal.in' =>
                'Select a valid career goal.',

            'packageName.in' =>
                'Select a valid package.',
        ]);

        $user = User::create([
            'name' => preg_replace(
                '/\s+/',
                ' ',
                trim($data['fullName'])
            ),

            'email' => Str::lower(
                trim($data['email'])
            ),

            'phone' => trim(
                $data['phone']
            ),

            'password' =>
                $data['password'],

            'profession' =>
                $data['profession'],

            'career_goal' =>
                $data['careerGoal'],

            'package_name' =>
                $data['packageName'],

            'package_status' =>
                $data['packageName'] === 'Silver'
                    ? 'active'
                    : 'payment_pending',

            'package_activated_at' =>
                $data['packageName'] === 'Silver'
                    ? now()
                    : null,

            'role' => 'user',
            'is_active' => true,
        ]);

        $requiresPayment =
            $user->package_name !== 'Silver';

        $payment = null;

        if ($requiresPayment) {
            $amount = match (
                $user->package_name
            ) {
                'Gold' => 2500.00,
                'Platinum' => 5000.00,
                default => 0.00,
            };

            $payment = Payment::create([
                'user_id' => $user->id,
                'package_name' =>
                    $user->package_name,
                'amount' => $amount,
                'currency' => 'LKR',
                'payment_method' => 'other',
                'status' => 'pending',
                'transaction_reference' =>
                    'REG-' .
                    strtoupper(substr(
                        $user->package_name,
                        0,
                        3
                    )) .
                    '-' .
                    $user->id .
                    '-' .
                    now()->format('YmdHis'),
                'notes' =>
                    'Created automatically during registration.',
            ]);
        }

        return response()->json([
            'success' => true,
            'requires_payment' =>
                $requiresPayment,
            'message' =>
                $requiresPayment
                    ? 'Account created. Complete your package payment before logging in.'
                    : 'Account created successfully. Please login.',
            'user' =>
                $this->formatUser($user),
            'payment' =>
                $payment
                    ? [
                        'id' => $payment->id,
                        'reference' =>
                            $payment->transaction_reference,
                        'package_name' =>
                            $payment->package_name,
                        'amount' =>
                            $payment->amount,
                        'currency' =>
                            $payment->currency,
                        'status' =>
                            $payment->status,
                    ]
                    : null,
        ], 201);
    }

    public function login(
        Request $request
    ): JsonResponse {
        $credentials =
            $request->validate([
                'email' => [
                    'required',
                    'email',
                ],

                'password' => [
                    'required',
                    'string',
                ],
            ]);

        $email = Str::lower(
            trim($credentials['email'])
        );

        $rateKey =
            'login:' .
            $email .
            '|' .
            $request->ip();

        if (
            RateLimiter::tooManyAttempts(
                $rateKey,
                5
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Too many login attempts. Try again in ' .
                    RateLimiter::availableIn(
                        $rateKey
                    ) .
                    ' seconds.',
            ], 429);
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (
            !$user ||
            !Hash::check(
                $credentials['password'],
                $user->password
            )
        ) {
            RateLimiter::increment($rateKey);

            throw ValidationException::withMessages([
                'email' => [
                    'The email or password is incorrect.',
                ],
            ]);
        }

        RateLimiter::clear($rateKey);

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Your account has been disabled.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Platform administrator
        |--------------------------------------------------------------------------
        | The administrator uses the normal login form and does not go through
        | the package-based Platinum OTP flow.
        */

        if ($user->role === 'admin') {
            $token = auth('api')->login(
                $user
            );

            return $this->directLoginResponse(
                $token,
                $user,
                'Admin login successful.'
            );
        }

        if (
            in_array(
                $user->package_name,
                ['Gold', 'Platinum'],
                true
            ) &&
            $user->package_status !== 'active'
        ) {
            $latestPayment =
                $user->payments()
                    ->latest()
                    ->first();

            return response()->json([
                'success' => false,
                'requires_payment' => true,
                'package_name' =>
                    $user->package_name,
                'package_status' =>
                    $user->package_status,
                'payment_reference' =>
                    $latestPayment?->transaction_reference,
                'message' =>
                    'Please complete your package payment before logging in.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Silver and Gold users
        |--------------------------------------------------------------------------
        */

        if (
            $user->package_name !==
            'Platinum'
        ) {
            $token = auth('api')->login(
                $user
            );

            return $this->directLoginResponse(
                $token,
                $user,
                'Login successful.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platinum users
        |--------------------------------------------------------------------------
        */

        LoginOtp::query()
            ->where(
                'user_id',
                $user->id
            )
            ->whereNull('consumed_at')
            ->delete();

        $plainOtp = (string) random_int(
            100000,
            999999
        );

        $challenge = LoginOtp::create([
            'user_id' => $user->id,

            'otp_hash' => Hash::make(
                $plainOtp
            ),

            'attempts' => 0,

            'expires_at' =>
                now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)
                ->send(
                    new LoginOtpMail(
                        $plainOtp
                    )
                );
        } catch (Throwable $exception) {
            report($exception);

            $challenge->delete();

            return response()->json([
                'success' => false,
                'message' =>
                    'The verification email could not be sent.',
            ], 500);
        }

        return response()->json([
            'success' => true,

            'requires_two_factor' =>
                true,

            'message' =>
                'A verification code was sent to your email.',

            'challenge_id' =>
                $challenge->id,

            'email' =>
                $this->maskEmail(
                    $user->email
                ),

            'expires_in' => 600,
        ]);
    }

    public function verifyOtp(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'challenge_id' => [
                'required',
                'uuid',
            ],

            'otp' => [
                'required',
                'digits:6',
            ],
        ]);

        $rateKey =
            'otp:' .
            $data['challenge_id'] .
            '|' .
            $request->ip();

        if (
            RateLimiter::tooManyAttempts(
                $rateKey,
                5
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Too many OTP attempts. Try again later.',
            ], 429);
        }

        $challenge = LoginOtp::with(
            'user'
        )
            ->whereKey(
                $data['challenge_id']
            )
            ->whereNull('consumed_at')
            ->first();

        if (
            !$challenge ||
            $challenge
                ->expires_at
                ->isPast()
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This verification request has expired.',
            ], 422);
        }

        if ($challenge->attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This verification request is locked.',
            ], 429);
        }

        if (
            !Hash::check(
                $data['otp'],
                $challenge->otp_hash
            )
        ) {
            $challenge->increment(
                'attempts'
            );

            RateLimiter::increment(
                $rateKey
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'The verification code is incorrect.',
            ], 422);
        }

        $challenge->update([
            'consumed_at' => now(),
        ]);

        RateLimiter::clear($rateKey);

        $user = $challenge->user;

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Your account has been disabled.',
            ], 403);
        }

        $token = auth('api')->login(
            $user
        );

        return $this->directLoginResponse(
            $token,
            $user,
            'Verification successful.'
        );
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'user' => $user
                ? $this->formatUser($user)
                : null,
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' =>
                'Logged out successfully.',
        ]);
    }

    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();

        $user = auth('api')->user();

        return $this->directLoginResponse(
            $token,
            $user,
            'Token refreshed successfully.'
        );
    }

    private function directLoginResponse(
        string $token,
        User $user,
        string $message
    ): JsonResponse {
        return response()->json([
            'success' => true,

            'requires_two_factor' =>
                false,

            'message' => $message,

            'access_token' => $token,

            'token_type' => 'Bearer',

            'expires_in' =>
                auth('api')
                    ->factory()
                    ->getTTL() * 60,

            'user' =>
                $this->formatUser($user),
        ]);
    }

    private function formatUser(
        User $user
    ): array {
        return [
            'id' => $user->id,

            'fullName' => $user->name,

            'name' => $user->name,

            'email' => $user->email,

            'phone' => $user->phone,

            'profession' =>
                $user->profession,

            'careerGoal' =>
                $user->career_goal,

            'career_goal' =>
                $user->career_goal,

            'packageName' =>
                $user->package_name,

            'package_name' =>
                $user->package_name,

            'packageStatus' =>
                $user->package_status,

            'package_status' =>
                $user->package_status,

            'package_activated_at' =>
                $user->package_activated_at?->toISOString(),

            'role' =>
                $user->role ?? 'user',

            'is_active' =>
                (bool) $user->is_active,
        ];
    }

    private function maskEmail(
        string $email
    ): string {
        [$name, $domain] = explode(
            '@',
            $email,
            2
        );

        $visible = substr(
            $name,
            0,
            2
        );

        return $visible .
            str_repeat(
                '*',
                max(
                    strlen($name) - 2,
                    1
                )
            ) .
            '@' .
            $domain;
    }
}
