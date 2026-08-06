<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'portfolio_views',
        'resume_downloads',
        'project_clicks',
        'completion_reminders',
        'package_payment_updates',
        'security_alerts',
        'product_updates',
        'weekly_report',
    ];

    protected function casts(): array
    {
        return [
            'portfolio_views' => 'boolean',
            'resume_downloads' => 'boolean',
            'project_clicks' => 'boolean',
            'completion_reminders' => 'boolean',
            'package_payment_updates' => 'boolean',
            'security_alerts' => 'boolean',
            'product_updates' => 'boolean',
            'weekly_report' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }
}