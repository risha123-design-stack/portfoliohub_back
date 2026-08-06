<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_experiences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('hospital');
            $table->string('department');
            $table->string('role');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', [
                'Current',
                'Completed',
            ])->default('Current');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_experiences');
    }
};