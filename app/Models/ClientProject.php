<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'client',
        'category',
        'budget',
        'deadline',
        'status',
        'description',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}