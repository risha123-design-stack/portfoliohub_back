<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('language');

            $table->string('proficiency');

            $table->string('reading_level')->nullable();

            $table->string('writing_level')->nullable();

            $table->string('speaking_level')->nullable();

            $table->string('certificate_name')->nullable();

            $table->string('certificate_url')->nullable();

            $table->boolean('is_native')
                  ->default(false);

            $table->boolean('is_featured')
                  ->default(false);

            $table->unsignedInteger('display_order')
                  ->default(0);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_featured',
                'display_order'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};