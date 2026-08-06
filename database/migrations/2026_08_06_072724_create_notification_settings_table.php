<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'notification_settings',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->boolean('portfolio_views')
                    ->default(true);

                $table
                    ->boolean('resume_downloads')
                    ->default(true);

                $table
                    ->boolean('project_clicks')
                    ->default(true);

                $table
                    ->boolean('completion_reminders')
                    ->default(true);

                $table
                    ->boolean('package_payment_updates')
                    ->default(true);

                /*
                 * Important account security alerts.
                 * Backend always keeps this enabled.
                 */
                $table
                    ->boolean('security_alerts')
                    ->default(true);

                $table
                    ->boolean('product_updates')
                    ->default(false);

                $table
                    ->boolean('weekly_report')
                    ->default(true);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'notification_settings'
        );
    }
};