<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending',
        'completed',
        'failed',
        'refunded',
    ];

    public const METHODS = [
        'cash',
        'bank_transfer',
        'card',
        'online',
        'other',
    ];

    protected $fillable = [
        'user_id',
        'package_name',
        'amount',
        'currency',
        'payment_method',
        'status',
        'transaction_reference',
        'notes',
        'paid_at',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'processed_by'
        );
    }
}