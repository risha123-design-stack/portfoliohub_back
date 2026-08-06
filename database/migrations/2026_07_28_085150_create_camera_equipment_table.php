<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camera_equipment', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->enum('type', [
                'Camera',
                'Lens',
                'Lighting',
                'Tripod',
                'Drone',
                'Accessory',
            ])->default('Camera');

            $table->string('brand');

            $table->string('model');

            $table->enum('condition', [
                'Excellent',
                'Good',
                'Fair',
                'Needs Repair',
            ])->default('Excellent');

            $table->unsignedSmallInteger('purchase_year')
                ->nullable();

            $table->enum('status', [
                'Available',
                'In Use',
                'Under Maintenance',
                'Unavailable',
            ])->default('Available');

            $table->text('description')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_equipment');
    }
};