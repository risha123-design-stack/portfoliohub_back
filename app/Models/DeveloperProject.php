<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperProject extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'title',

        'category',

        'tech_stack',

        'github_url',

        'live_demo_url',

        'description',

        'status',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}