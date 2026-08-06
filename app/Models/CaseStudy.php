<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [

        'user_id',

        'title',

        'category',

        'client',

        'duration',

        'tools',

        'status',

        'description',
    ];
}