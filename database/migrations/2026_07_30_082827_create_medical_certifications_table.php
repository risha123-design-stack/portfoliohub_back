<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_certifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('issuer');
            $table->string('category');

            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->enum('status', [
                'Active',
                'Expired',
                'Expiring Soon',
            ])->default('Active');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_certifications');
    }
};