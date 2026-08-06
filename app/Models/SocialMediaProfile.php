<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMediaProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'username',
        'profile_url',
        'followers',
        'content_type',
        'status',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}