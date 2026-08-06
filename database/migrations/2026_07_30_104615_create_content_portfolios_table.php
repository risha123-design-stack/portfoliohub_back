<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_portfolios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('platform');
            $table->string('content_type');

            $table->date('publish_date')->nullable();

            $table->enum('status', [
                'Published',
                'Draft',
                'Scheduled',
                'Archived',
            ])->default('Published');

            $table->text('content_url')->nullable();
            $table->text('thumbnail_url')->nullable();

            $table->string('views')->nullable();
            $table->string('likes')->nullable();

            $table->string('category')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_portfolios');
    }
};