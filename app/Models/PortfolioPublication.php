<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'is_published',
        'visibility',
        'access_password',

        'template_id',
        'template_name',
        'template_category',
        'template_style',
        'template_package',
        'selected_template',
        'enabled_modules',

        'seo_title',
        'seo_description',
        'seo_keywords',
        'allow_search_engines',

        'published_at',
        'unpublished_at',
        'completion_percentage',
    ];

    protected $hidden = [
        'access_password',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'completion_percentage' => 'integer',
            'allow_search_engines' => 'boolean',
            'selected_template' => 'array',
            'enabled_modules' => 'array',
            'published_at' => 'datetime',
            'unpublished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function analytics(): HasMany
{
    return $this->hasMany(
        PortfolioAnalytic::class,
        'portfolio_publication_id'
    );
}
}