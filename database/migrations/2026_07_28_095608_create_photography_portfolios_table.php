<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photography_portfolios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('category');

            $table->string('location');

            $table->date('project_date')
                ->nullable();

            $table->string('camera')
                ->nullable();

            $table->enum('status', [
                'Published',
                'Draft',
            ])->default('Published');

            $table->text('description')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photography_portfolios');
    }
};