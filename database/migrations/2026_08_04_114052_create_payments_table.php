<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'payments',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'package_name',
                    30
                );

                $table->decimal(
                    'amount',
                    12,
                    2
                );

                $table->string(
                    'currency',
                    10
                )->default('LKR');

                $table->string(
                    'payment_method',
                    40
                )->default('other');

                $table->string(
                    'status',
                    30
                )->default('pending');

                $table->string(
                    'transaction_reference',
                    150
                )->nullable();

                $table->text('notes')
                    ->nullable();

                $table->timestamp('paid_at')
                    ->nullable();

                $table->foreignId(
                    'processed_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index([
                    'status',
                    'created_at',
                ]);

                $table->index([
                    'package_name',
                    'status',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'payments'
        );
    }
};