<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'short_description',
        'description',
        'image',
        'github_url',
        'live_url',
        'category',
        'role',
        'team_size',
        'technologies',
        'start_date',
        'end_date',
        'status',
        'featured',
    ];

    protected $casts = [
        'technologies' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'featured' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}