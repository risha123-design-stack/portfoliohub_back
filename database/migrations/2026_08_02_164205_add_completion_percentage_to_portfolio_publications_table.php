<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'portfolio_publications',
            function (Blueprint $table) {
                $table->unsignedTinyInteger(
                    'completion_percentage'
                )->default(0)->after('is_published');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'portfolio_publications',
            function (Blueprint $table) {
                $table->dropColumn(
                    'completion_percentage'
                );
            }
        );
    }
};