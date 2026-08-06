<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignTool extends Model
{
    protected $fillable = [

        'user_id',

        'tool',

        'category',

        'level',

        'experience',

        'description',

    ];
}