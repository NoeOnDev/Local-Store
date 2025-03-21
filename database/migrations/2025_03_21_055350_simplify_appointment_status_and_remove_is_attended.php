<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Cambiar los valores posibles del enum
            $table->enum('status', ['pending', 'attended', 'cancelled'])->default('pending')->change();
            // Eliminar el campo redundante
            $table->dropColumn('is_attended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'attended'])->default('pending')->change();
            $table->boolean('is_attended')->default(false)->after('status');
        });
    }
};
