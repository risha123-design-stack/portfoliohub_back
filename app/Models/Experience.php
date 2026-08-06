<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $table = 'experiences';

    protected $fillable = [
        'user_id',
        'organization_name',
        'position_title',
        'employment_type',
        'industry',
        'location',
        'location_type',
        'start_date',
        'end_date',
        'currently_working',
        'description',
        'achievements',
        'skills',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'currently_working' => 'boolean',
        'achievements' => 'array',
        'skills' => 'array',
        'display_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}