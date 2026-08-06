<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->string('phone', 30)
                ->nullable()
                ->after('email');

            $table
                ->string('profession', 100)
                ->nullable()
                ->after('password');

            $table
                ->string('career_goal', 100)
                ->nullable()
                ->after('profession');

            $table
                ->string('package_name', 30)
                ->default('Silver')
                ->after('career_goal');

            $table
                ->boolean('is_active')
                ->default(true)
                ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'profession',
                'career_goal',
                'package_name',
                'is_active',
            ]);
        });
    }
};