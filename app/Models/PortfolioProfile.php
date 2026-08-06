<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioProfile extends Model
{
    use HasFactory;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        'user_id',

        /*
        |--------------------------------------------------------------------------
        | Personal Information
        |--------------------------------------------------------------------------
        */

        'first_name',
        'last_name',
        'display_name',

        'date_of_birth',
        'gender',
        'nationality',

        'profile_image',
        'cover_image',

        /*
        |--------------------------------------------------------------------------
        | Professional Information
        |--------------------------------------------------------------------------
        */

        'professional_title',
        'profession',
        'specialization',

        'current_position',
        'company',

        'experience_years',

        'tagline',
        'career_objective',

        /*
        |--------------------------------------------------------------------------
        | About
        |--------------------------------------------------------------------------
        */

        'short_introduction',
        'about_me',

        /*
        |--------------------------------------------------------------------------
        | Contact
        |--------------------------------------------------------------------------
        */

        'public_email',
        'primary_phone',
        'secondary_phone',
        'website',

        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        'address_line_1',
        'address_line_2',

        'city',
        'district',
        'province',

        'postal_code',
        'country',

        /*
        |--------------------------------------------------------------------------
        | Languages
        |--------------------------------------------------------------------------
        */

        'languages',

        /*
        |--------------------------------------------------------------------------
        | Documents
        |--------------------------------------------------------------------------
        */

        'resume',

        /*
        |--------------------------------------------------------------------------
        | Social Links
        |--------------------------------------------------------------------------
        */

        'github',
        'linkedin',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'behance',
        'dribbble',
        'portfolio_link',

        /*
        |--------------------------------------------------------------------------
        | Visibility
        |--------------------------------------------------------------------------
        */

        'show_email',
        'show_phone',
        'show_date_of_birth',
        'show_address',
        'show_resume',
        'show_social_links',
    ];

    protected $casts = [

        'date_of_birth' => 'date',

        'languages' => 'array',

        'show_email' => 'boolean',
        'show_phone' => 'boolean',
        'show_date_of_birth' => 'boolean',
        'show_address' => 'boolean',
        'show_resume' => 'boolean',
        'show_social_links' => 'boolean',

    ];

    protected $attributes = [

        'show_email' => true,
        'show_phone' => false,
        'show_date_of_birth' => false,
        'show_address' => false,
        'show_resume' => true,
        'show_social_links' => true,

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return trim(
            ($this->first_name ?? '') .
            ' ' .
            ($this->last_name ?? '')
        );
    }
}