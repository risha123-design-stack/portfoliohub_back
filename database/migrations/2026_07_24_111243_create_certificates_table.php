<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('certificate_name');
            $table->string('issuing_organization');

            $table->string('category')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('credential_url')->nullable();

            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->boolean('never_expires')
                ->default(false);

            $table->string('certificate_file')->nullable();
            $table->string('original_file_name')->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->unsignedInteger('display_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_featured',
                'display_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};