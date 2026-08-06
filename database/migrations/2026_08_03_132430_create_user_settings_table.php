<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (
            Blueprint $table
        ) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->boolean(
                    'portfolio_views_notification'
                )
                ->default(true);

            $table
                ->boolean(
                    'resume_downloads_notification'
                )
                ->default(true);

            $table
                ->boolean(
                    'project_clicks_notification'
                )
                ->default(true);

            $table
                ->boolean('security_alerts')
                ->default(true);

            $table
                ->boolean('product_updates')
                ->default(false);

            $table
                ->boolean('weekly_report')
                ->default(true);

            $table
                ->enum('theme', [
                    'light',
                    'dark',
                    'system',
                ])
                ->default('light');

            $table
                ->boolean('compact_mode')
                ->default(false);

            $table
                ->boolean('animations')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};