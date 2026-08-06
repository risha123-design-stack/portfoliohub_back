<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Basic information
            $table->string('organization_name');
            $table->string('position_title');

            // Employment details
            $table->string('employment_type')->nullable();
            $table->string('industry')->nullable();

            // Location
            $table->string('location')->nullable();
            $table->string('location_type')->nullable();

            // Timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('currently_working')
                ->default(false);

            // Work description
            $table->longText('description')->nullable();

            // Common professional details
            $table->json('achievements')->nullable();
            $table->json('skills')->nullable();

            // Ordering
            $table->integer('display_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};