<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'profession',
        'bio',
        'email',
        'phone',
        'address',
        'profile_image',
        'github_url',
        'linkedin_url',
        'website_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}