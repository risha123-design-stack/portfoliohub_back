<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
    use HasFactory;

    protected $table = 'languages';

    protected $fillable = [

        'user_id',

        'language',

        'proficiency',

        'reading_level',

        'writing_level',

        'speaking_level',

        'certificate_name',

        'certificate_url',

        'is_native',

        'is_featured',

        'display_order',

    ];

    protected $casts = [

        'is_native' => 'boolean',

        'is_featured' => 'boolean',

        'display_order' => 'integer',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}