<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCertification extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'title',

        'provider',

        'category',

        'issue_date',

        'expiry_date',

        'credential_id',

        'status',

        'skills',

        'description',

    ];

    protected $casts = [

        'issue_date' => 'date:Y-m-d',

        'expiry_date' => 'date:Y-m-d',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}