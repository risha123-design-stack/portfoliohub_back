<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPaymentController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                Rule::in(
                    Payment::STATUSES
                ),
            ],

            'package' => [
                'nullable',
                Rule::in([
                    'Silver',
                    'Gold',
                    'Platinum',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:5',
                'max:100',
            ],
        ]);

        $query = Payment::query()
            ->with([
                'user:id,name,email',
                'processor:id,name,email',
            ]);

        if (!empty($validated['search'])) {
            $search = trim(
                $validated['search']
            );

            $query->where(
                function ($builder) use (
                    $search
                ): void {
                    $builder
                        ->where(
                            'transaction_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'user',
                            function (
                                $userQuery
                            ) use ($search): void {
                                $userQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        if (!empty($validated['status'])) {
            $query->where(
                'status',
                $validated['status']
            );
        }

        if (!empty($validated['package'])) {
            $query->where(
                'package_name',
                $validated['package']
            );
        }

        $payments = $query
            ->latest()
            ->paginate(
                $validated['per_page'] ?? 10
            );

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    public function store(
        Request $request
    ): JsonResponse {
        $validated = $request->validate(
            $this->rules()
        );

        $user = User::query()
            ->where('role', '!=', 'admin')
            ->findOrFail(
                $validated['user_id']
            );

        $validated['processed_by'] =
            $request->user()->id;

        if (
            $validated['status'] ===
                'completed' &&
            empty($validated['paid_at'])
        ) {
            $validated['paid_at'] = now();
        }

        $payment = Payment::create(
            $validated
        );

        if (
            $payment->status ===
            'completed'
        ) {
            $user->update([
                'package_name' =>
                    $payment->package_name,
                'package_status' =>
                    'active',
                'package_activated_at' =>
                    $payment->paid_at ?? now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Payment record created successfully.',
            'data' => $payment->load([
                'user:id,name,email',
                'processor:id,name,email',
            ]),
        ], 201);
    }

    public function show(
        Payment $payment
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $payment->load([
                'user:id,name,email',
                'processor:id,name,email',
            ]),
        ]);
    }

    public function update(
        Request $request,
        Payment $payment
    ): JsonResponse {
        $validated = $request->validate(
            $this->rules(
                isUpdate: true
            )
        );

        $validated['processed_by'] =
            $request->user()->id;

        if (
            ($validated['status'] ??
                $payment->status) ===
                'completed' &&
            empty(
                $validated['paid_at'] ??
                $payment->paid_at
            )
        ) {
            $validated['paid_at'] = now();
        }

        $payment->update($validated);

        if (
            $payment->status ===
            'completed'
        ) {
            $payment->user->update([
                'package_name' =>
                    $payment->package_name,
                'package_status' =>
                    'active',
                'package_activated_at' =>
                    $payment->paid_at ?? now(),
            ]);
        } else {
            $hasCompletedPayment =
                $payment->user
                    ->payments()
                    ->where(
                        'package_name',
                        $payment->package_name
                    )
                    ->where(
                        'status',
                        'completed'
                    )
                    ->exists();

            if (!$hasCompletedPayment) {
                $payment->user->update([
                    'package_name' =>
                        $payment->package_name,
                    'package_status' =>
                        'payment_pending',
                    'package_activated_at' =>
                        null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Payment updated successfully.',
            'data' => $payment
                ->fresh()
                ->load([
                    'user:id,name,email',
                    'processor:id,name,email',
                ]),
        ]);
    }

    public function updateStatus(
        Request $request,
        Payment $payment
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(
                    Payment::STATUSES
                ),
            ],
        ]);

        $changes = [
            'status' =>
                $validated['status'],

            'processed_by' =>
                $request->user()->id,
        ];

        if (
            $validated['status'] ===
            'completed'
        ) {
            $changes['paid_at'] =
                $payment->paid_at ?? now();
        }

        $payment->update($changes);

        if (
            $payment->status ===
            'completed'
        ) {
            $payment->user->update([
                'package_name' =>
                    $payment->package_name,
                'package_status' =>
                    'active',
                'package_activated_at' =>
                    $payment->paid_at ?? now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Payment status updated.',
            'data' => $payment
                ->fresh()
                ->load([
                    'user:id,name,email',
                    'processor:id,name,email',
                ]),
        ]);
    }

    public function destroy(
        Payment $payment
    ): JsonResponse {
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Payment deleted successfully.',
        ]);
    }

    private function rules(
        bool $isUpdate = false
    ): array {
        $required =
            $isUpdate
                ? 'sometimes'
                : 'required';

        return [
            'user_id' => [
                $required,
                'integer',
                'exists:users,id',
            ],

            'package_name' => [
                $required,
                Rule::in([
                    'Silver',
                    'Gold',
                    'Platinum',
                ]),
            ],

            'amount' => [
                $required,
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'currency' => [
                'sometimes',
                'string',
                'max:10',
            ],

            'payment_method' => [
                $required,
                Rule::in(
                    Payment::METHODS
                ),
            ],

            'status' => [
                $required,
                Rule::in(
                    Payment::STATUSES
                ),
            ],

            'transaction_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'paid_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
