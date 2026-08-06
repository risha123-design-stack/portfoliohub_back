<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientTestimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_name',
        'company',
        'project',
        'rating',
        'testimonial_date',
        'status',
        'testimonial',
    ];

    protected $casts = [
        'rating' => 'integer',
        'testimonial_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}