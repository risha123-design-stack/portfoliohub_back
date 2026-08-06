<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('display_name', 150)->nullable();

            $table->date('date_of_birth')->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('nationality', 100)->nullable();

            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Professional Information
            |--------------------------------------------------------------------------
            */

            $table->string('professional_title', 200)->nullable();
            $table->string('profession', 150)->nullable();
            $table->string('specialization', 200)->nullable();

            $table->string('current_position', 200)->nullable();
            $table->string('company', 200)->nullable();

            $table->unsignedSmallInteger('experience_years')->nullable();

            $table->string('tagline', 300)->nullable();
            $table->text('career_objective')->nullable();

            /*
            |--------------------------------------------------------------------------
            | About Information
            |--------------------------------------------------------------------------
            */

            $table->text('short_introduction')->nullable();
            $table->longText('about_me')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Contact Information
            |--------------------------------------------------------------------------
            */

            $table->string('public_email', 190)->nullable();
            $table->string('primary_phone', 30)->nullable();
            $table->string('secondary_phone', 30)->nullable();
            $table->string('website', 500)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Address Information
            |--------------------------------------------------------------------------
            */

            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();

            $table->string('city', 120)->nullable();
            $table->string('district', 120)->nullable();
            $table->string('province', 120)->nullable();

            $table->string('postal_code', 30)->nullable();
            $table->string('country', 120)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Languages
            |--------------------------------------------------------------------------
            */

            $table->json('languages')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */

            $table->string('resume')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Social Links
            |--------------------------------------------------------------------------
            */

            $table->string('github', 500)->nullable();
            $table->string('linkedin', 500)->nullable();
            $table->string('facebook', 500)->nullable();
            $table->string('instagram', 500)->nullable();
            $table->string('twitter', 500)->nullable();
            $table->string('youtube', 500)->nullable();
            $table->string('behance', 500)->nullable();
            $table->string('dribbble', 500)->nullable();
            $table->string('portfolio_link', 500)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Visibility Settings
            |--------------------------------------------------------------------------
            */

            $table->boolean('show_email')->default(true);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_date_of_birth')->default(false);
            $table->boolean('show_address')->default(false);
            $table->boolean('show_resume')->default(true);
            $table->boolean('show_social_links')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_profiles');
    }
};