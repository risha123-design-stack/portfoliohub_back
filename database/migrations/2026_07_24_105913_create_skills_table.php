<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('skill_type')->nullable();
            $table->string('category')->nullable();
            $table->string('proficiency_level')->nullable();

            $table->decimal(
                'years_of_experience',
                4,
                1
            )->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedInteger('display_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'user_id',
                'display_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};