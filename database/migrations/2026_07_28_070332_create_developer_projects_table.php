<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_projects', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('category');

            $table->text('tech_stack');

            $table->string('github_url')->nullable();

            $table->string('live_demo_url')->nullable();

            $table->longText('description')->nullable();

            $table->enum('status', [
                'Completed',
                'In Progress',
                'Planned'
            ])->default('Completed');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_projects');
    }
};