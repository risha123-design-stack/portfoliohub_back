<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('location')->nullable();
            $table->string('website')->nullable();

            $table->string('current_position')->nullable();
            $table->unsignedInteger('experience_years')->nullable();

            $table->text('bio')->nullable();

            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();

            $table->string('profile_photo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'website',
                'current_position',
                'experience_years',
                'bio',
                'github',
                'linkedin',
                'facebook',
                'instagram',
                'twitter',
                'profile_photo',
            ]);
        });
    }
};