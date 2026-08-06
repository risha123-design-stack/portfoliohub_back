<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'user_notifications',
            function (Blueprint $table): void {

                $table->id();

                $table
                    ->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                /*
                 * Notification category
                 *
                 * portfolio_view
                 * resume_download
                 * project_click
                 * security
                 * package
                 * system
                 */

                $table
                    ->string('type', 50);

                $table
                    ->string('title', 150);

                $table
                    ->text('message');

                /*
                 * Extra information
                 *
                 * project_id
                 * visitor_id
                 * analytics_id
                 * etc.
                 */

                $table
                    ->json('data')
                    ->nullable();

                $table
                    ->boolean('is_read')
                    ->default(false);

                $table
                    ->timestamp('read_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'is_read'
                ]);

                $table->index([
                    'type'
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'user_notifications'
        );
    }
};