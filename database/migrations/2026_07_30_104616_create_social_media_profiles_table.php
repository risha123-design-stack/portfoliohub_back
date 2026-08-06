<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('platform');
            $table->string('username');
            $table->text('profile_url');

            $table->string('followers')->nullable();
            $table->string('content_type')->nullable();

            $table->enum('status', [
                'Active',
                'Inactive',
                'Archived',
            ])->default('Active');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_profiles');
    }
};