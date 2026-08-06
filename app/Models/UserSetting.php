<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

   protected $fillable = [
    'user_id',

    'portfolio_views_notification',
    'resume_downloads_notification',
    'project_clicks_notification',

    'completion_reminders',
    'package_payment_updates',

    'security_alerts',
    'product_updates',
    'weekly_report',

    'theme',
    'compact_mode',
    'animations',
];

    protected function casts(): array
{
    return [
        'portfolio_views_notification' =>
            'boolean',

        'resume_downloads_notification' =>
            'boolean',

        'project_clicks_notification' =>
            'boolean',

        'completion_reminders' =>
            'boolean',

        'package_payment_updates' =>
            'boolean',

        'security_alerts' =>
            'boolean',

        'product_updates' =>
            'boolean',

        'weekly_report' =>
            'boolean',

        'compact_mode' =>
            'boolean',

        'animations' =>
            'boolean',
    ];
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}