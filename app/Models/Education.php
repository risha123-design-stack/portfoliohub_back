<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    /*
     * இந்த line முக்கியம்.
     * Database table name "educations".
     */
    protected $table = 'educations';

    protected $fillable = [
        'user_id',
        'institution_name',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
        'currently_studying',
        'grade',
        'location',
        'description',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'currently_studying' => 'boolean',
        'display_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}