<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'issuer',
        'category',
        'issue_date',
        'expiry_date',
        'status',
        'description',
    ];

    protected $casts = [
        'issue_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}