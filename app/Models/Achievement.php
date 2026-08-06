<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'achievements';

    protected $fillable = [
        'user_id',
        'title',
        'achievement_type',
        'organization',
        'description',
        'position',
        'level',
        'achievement_date',
        'achievement_url',
        'evidence_file',
        'original_file_name',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'achievement_date' => 'date',
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    protected $appends = [
        'evidence_file_url',
    ];

    public function getEvidenceFileUrlAttribute(): ?string
    {
        if (!$this->evidence_file) {
            return null;
        }

        return url(Storage::url($this->evidence_file));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}