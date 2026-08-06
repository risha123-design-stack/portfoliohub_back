<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioAbout extends Model
{
    use HasFactory;

    protected $table = 'portfolio_abouts';

    protected $fillable = [
        'user_id',
        'professional_headline',
        'about',
    ];

    /**
     * Get the user who owns
     * this About section.
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}