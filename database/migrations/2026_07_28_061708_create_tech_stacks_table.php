<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_stacks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category');

            $table->string('technology');

            $table->string('level');

            $table->string('experience');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_stacks');
    }
};