<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'title',

        'description',

        'resume_file',

        'original_file_name',

        'resume_version',

        'visibility',

        'is_primary',

        'downloads',

    ];

    protected $casts = [

        'is_primary'=>'boolean',

        'downloads'=>'integer',

    ];

    protected $appends=[
        'resume_url'
    ];

    public function getResumeUrlAttribute()
    {
        return asset(
            'storage/'.$this->resume_file
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}