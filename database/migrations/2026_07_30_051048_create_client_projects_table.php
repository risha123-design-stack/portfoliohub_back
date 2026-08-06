<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('client');

            $table->string('category');

            $table->decimal('budget', 12, 2)->nullable();

            $table->date('deadline')->nullable();

            $table->enum('status', [
                'In Progress',
                'Completed',
                'Cancelled',
            ])->default('In Progress');

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_projects');
    }
};