<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Education information
            $table->string('institution_name');
            $table->string('degree');
            $table->string('field_of_study')->nullable();

            // Timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('currently_studying')->default(false);

            // Additional information
            $table->string('grade')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();

            // Display ordering
            $table->integer('display_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};