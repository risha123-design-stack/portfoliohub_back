<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')
                  ->nullable();

            $table->string('resume_file');

            $table->string('original_file_name');

            $table->string('resume_version')
                  ->default('1.0');

            $table->enum('visibility',[
                'public',
                'private'
            ])->default('public');

            $table->boolean('is_primary')
                  ->default(false);

            $table->unsignedInteger('downloads')
                  ->default(0);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_primary'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};