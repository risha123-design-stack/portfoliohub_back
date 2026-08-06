<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_informations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('contact_type',100);

            $table->string('label',150)
                ->nullable();

            $table->string('value',1000);

            $table->unsignedInteger('display_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'user_id',
                'display_order'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'contact_informations'
        );
    }
};