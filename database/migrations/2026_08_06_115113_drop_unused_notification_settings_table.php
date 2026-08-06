<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists(
            'notification_settings'
        );
    }

    public function down(): void
    {
        // பழைய duplicate table-ஐ மீண்டும்
        // உருவாக்க வேண்டிய அவசியம் இல்லை.
    }
};