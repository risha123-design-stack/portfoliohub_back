<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicProject extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'title',

        'course',

        'subject',

        'project_type',

        'start_date',

        'end_date',

        'status',

        'grade',

        'technologies',

        'description',

    ];

    protected $casts=[

        'start_date'=>'date:Y-m-d',

        'end_date'=>'date:Y-m-d',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}