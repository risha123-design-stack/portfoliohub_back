<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'username',
        'rating',
        'solved',
        'rank',
        'stars',
        'profile_url',
    ];

    protected $casts = [
        'solved' => 'integer',
        'stars' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}