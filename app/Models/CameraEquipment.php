<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CameraEquipment extends Model
{
    use HasFactory;

    protected $table = 'camera_equipment';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'brand',
        'model',
        'condition',
        'purchase_year',
        'status',
        'description',
    ];

    protected $casts = [
        'purchase_year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}