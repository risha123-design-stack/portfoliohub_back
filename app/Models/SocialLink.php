<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'label',
        'username',
        'url',
        'is_visible',
        'is_featured',
        'display_order',
        'clicks',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'display_order' => 'integer',
        'clicks' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}