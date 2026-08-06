<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_testimonials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('client_name');

            $table->string('company');

            $table->string('project');

            $table->unsignedTinyInteger('rating')
                ->default(5);

            $table->date('testimonial_date')->nullable();

            $table->enum('status', [
                'Published',
                'Draft',
                'Hidden',
            ])->default('Published');

            $table->text('testimonial');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_testimonials');
    }
};