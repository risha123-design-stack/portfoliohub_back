<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('platform', 100);
            $table->string('label')->nullable();
            $table->string('username')->nullable();
            $table->string('url', 2048);

            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->unsignedInteger('display_order')->default(0);
            $table->unsignedInteger('clicks')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'display_order']);
            $table->index(['user_id', 'is_visible']);
            $table->index(['user_id', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};