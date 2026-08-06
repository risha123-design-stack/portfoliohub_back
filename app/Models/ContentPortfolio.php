<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'platform',
        'content_type',
        'publish_date',
        'status',
        'content_url',
        'thumbnail_url',
        'views',
        'likes',
        'category',
        'description',
    ];

    protected $casts = [
        'publish_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}