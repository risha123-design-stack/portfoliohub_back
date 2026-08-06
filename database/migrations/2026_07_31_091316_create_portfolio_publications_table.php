<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_publications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Portfolio address
            |--------------------------------------------------------------------------
            */

            $table->string('slug', 50)->unique();

            /*
            |--------------------------------------------------------------------------
            | Publishing status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_published')->default(false);

            $table->enum('visibility', [
                'public',
                'private',
                'password',
            ])->default('public');

            $table->string('access_password')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Template and modules
            |--------------------------------------------------------------------------
            */

            $table->string('template_id')->nullable();
            $table->string('template_name')->nullable();
            $table->string('template_category')->nullable();
            $table->string('template_style')->nullable();
            $table->string('template_package')->nullable();

            $table->json('selected_template')->nullable();
            $table->json('enabled_modules')->nullable();

            /*
            |--------------------------------------------------------------------------
            | SEO settings
            |--------------------------------------------------------------------------
            */

            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->text('seo_keywords')->nullable();

            $table->boolean('allow_search_engines')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Publishing dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('published_at')->nullable();
            $table->timestamp('unpublished_at')->nullable();

            $table->timestamps();

            $table->index([
                'is_published',
                'visibility',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_publications');
    }
};