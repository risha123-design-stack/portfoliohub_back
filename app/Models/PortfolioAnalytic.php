<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_publication_id',
        'user_id',

        'event_type',
        'event_target',

        'visitor_id',
        'ip_address',
        'user_agent',

        'device_type',
        'browser',
        'operating_system',

        'country',
        'referrer',

        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(
            PortfolioPublication::class,
            'portfolio_publication_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}