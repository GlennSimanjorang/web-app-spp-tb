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
        Schema::table('due_date_alerts', function (Blueprint $table) {
            // Perbarui enum untuk menyertakan 'due'
            $table->enum('alert_type', ['upcoming', 'due', 'overdue', 'critical'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('due_date_alerts', function (Blueprint $table) {
            // Kembalikan ke enum lama (tanpa 'due')
            $table->enum('alert_type', ['upcoming', 'overdue', 'critical'])->change();
        });
    }
};
