<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('course');

            $table->string('subject');

            $table->enum('project_type', [
                'Individual Project',
                'Group Project',
                'Research Project',
                'Capstone Project',
            ])->default('Individual Project');

            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

            $table->enum('status', [
                'Planned',
                'In Progress',
                'Completed',
                'On Hold',
            ])->default('Planned');

            $table->string('grade')->nullable();

            $table->string('technologies');

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_projects');
    }
};