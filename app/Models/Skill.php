<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $table = 'skills';

    protected $fillable = [
        'user_id',
        'name',
        'skill_type',
        'category',
        'proficiency_level',
        'years_of_experience',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'years_of_experience' => 'decimal:1',
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}