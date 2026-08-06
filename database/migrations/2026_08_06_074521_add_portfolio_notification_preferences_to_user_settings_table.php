<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'user_settings',
            function (Blueprint $table): void {
                if (
                    !Schema::hasColumn(
                        'user_settings',
                        'completion_reminders'
                    )
                ) {
                    $table
                        ->boolean(
                            'completion_reminders'
                        )
                        ->default(true)
                        ->after(
                            'project_clicks_notification'
                        );
                }

                if (
                    !Schema::hasColumn(
                        'user_settings',
                        'package_payment_updates'
                    )
                ) {
                    $table
                        ->boolean(
                            'package_payment_updates'
                        )
                        ->default(true)
                        ->after(
                            'completion_reminders'
                        );
                }
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'user_settings',
            function (Blueprint $table): void {
                if (
                    Schema::hasColumn(
                        'user_settings',
                        'package_payment_updates'
                    )
                ) {
                    $table->dropColumn(
                        'package_payment_updates'
                    );
                }

                if (
                    Schema::hasColumn(
                        'user_settings',
                        'completion_reminders'
                    )
                ) {
                    $table->dropColumn(
                        'completion_reminders'
                    );
                }
            }
        );
    }
};