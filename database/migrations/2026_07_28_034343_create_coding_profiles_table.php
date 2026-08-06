<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coding_profiles', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('platform', 50);
            $table->string('username', 100);

            $table->string('rating', 50)->nullable();
            $table->unsignedInteger('solved')->nullable();
            $table->string('rank', 100)->nullable();
            $table->unsignedInteger('stars')->nullable();

            $table->string('profile_url', 2048);

            $table->timestamps();

            $table->unique(
                ['user_id', 'platform', 'username'],
                'coding_profiles_user_platform_username_unique'
            );

            $table->index(['user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_profiles');
    }
};