<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_abouts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string(
                'professional_headline',
                255
            )->nullable();

            $table->text('about');

            $table->timestamps();

            /*
             * One authenticated user can have
             * only one About record.
             */
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'portfolio_abouts'
        );
    }
};