<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('achievement_type')->nullable();
            $table->string('organization')->nullable();

            $table->text('description')->nullable();

            $table->string('position')->nullable();
            $table->string('level')->nullable();

            $table->date('achievement_date')->nullable();

            $table->string('achievement_url', 1000)->nullable();

            $table->string('evidence_file')->nullable();
            $table->string('original_file_name')->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedInteger('display_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_featured',
                'display_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};