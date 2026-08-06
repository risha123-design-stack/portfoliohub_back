<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Basic Information
            $table->string('title');
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();

            // Image
            $table->string('image')->nullable();

            // Links
            $table->string('github_url')->nullable();
            $table->string('live_url')->nullable();

            // Portfolio Information
            $table->string('category')->nullable();
            $table->string('role')->nullable();
            $table->string('team_size')->nullable();

            // Technologies
            $table->json('technologies')->nullable();

            // Timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Status
            $table->enum('status', [
                'Planned',
                'In Progress',
                'Completed'
            ])->default('Completed');

            // Featured Project
            $table->boolean('featured')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};