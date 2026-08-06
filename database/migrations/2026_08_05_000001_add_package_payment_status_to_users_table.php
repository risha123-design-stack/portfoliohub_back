<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('package_status', 40)
                ->default('active')
                ->after('package_name');

            $table->timestamp('package_activated_at')
                ->nullable()
                ->after('package_status');

            $table->index([
                'package_name',
                'package_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex([
                'package_name',
                'package_status',
            ]);

            $table->dropColumn([
                'package_status',
                'package_activated_at',
            ]);
        });
    }
};
