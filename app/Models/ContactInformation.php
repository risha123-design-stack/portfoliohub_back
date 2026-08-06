<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInformation extends Model
{
    use HasFactory;

    protected $table = 'contact_informations';

    protected $fillable = [
        'user_id',
        'contact_type',
        'label',
        'value',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Get the user who owns
     * this contact information.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}