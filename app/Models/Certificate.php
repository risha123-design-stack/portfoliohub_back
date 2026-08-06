<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'user_id',
        'certificate_name',
        'issuing_organization',
        'category',
        'credential_id',
        'credential_url',
        'issue_date',
        'expiry_date',
        'never_expires',
        'certificate_file',
        'original_file_name',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'never_expires' => 'boolean',
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    protected $appends = [
        'certificate_file_url',
    ];

    public function getCertificateFileUrlAttribute(): ?string
    {
        if (!$this->certificate_file) {
            return null;
        }

        return url(Storage::url($this->certificate_file));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}