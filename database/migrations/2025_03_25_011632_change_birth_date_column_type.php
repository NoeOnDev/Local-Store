<?php
// filepath: /home/noeon/Dev-Projects/Back-end/clients-api/database/migrations/2025_03_22_000000_change_birth_date_column_type.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Cambiar de datetime a date
            $table->date('birth_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->datetime('birth_date')->nullable()->change();
        });
    }
};
