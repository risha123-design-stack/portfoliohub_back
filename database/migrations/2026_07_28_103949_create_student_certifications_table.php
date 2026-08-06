<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_certifications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('provider');

            $table->string('category');

            $table->date('issue_date')->nullable();

            $table->date('expiry_date')->nullable();

            $table->string('credential_id')->nullable();

            $table->enum('status',[
                'Completed',
                'In Progress',
                'Expired'
            ])->default('Completed');

            $table->string('skills')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_certifications');
    }
};