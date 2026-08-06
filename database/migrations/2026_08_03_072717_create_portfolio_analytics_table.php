<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'portfolio_analytics',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('portfolio_publication_id')
                    ->constrained('portfolio_publications')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('event_type', 50);

                $table->string('event_target')
                    ->nullable();

                $table->string('visitor_id')
                    ->nullable()
                    ->index();

                $table->string('ip_address', 45)
                    ->nullable();

                $table->text('user_agent')
                    ->nullable();

                $table->string('device_type', 50)
                    ->nullable();

                $table->string('browser', 100)
                    ->nullable();

                $table->string('operating_system', 100)
                    ->nullable();

                $table->string('country', 100)
                    ->nullable();

                $table->string('referrer')
                    ->nullable();

                $table->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'portfolio_publication_id',
                    'event_type',
                ]);

                $table->index('created_at');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_analytics');
    }
};