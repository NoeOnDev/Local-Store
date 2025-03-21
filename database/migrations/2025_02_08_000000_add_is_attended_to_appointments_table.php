_appointment_status_column.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'attended'])->default('pending')->change();
            $table->boolean('is_attended')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending')->change();
            $table->dropColumn('is_attended');
        });
    }
};
