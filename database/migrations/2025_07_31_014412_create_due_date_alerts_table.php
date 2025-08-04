<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('due_date_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('alert_type', ['upcoming', 'overdue', 'critical']);
            $table->date('alert_date');
            $table->boolean('is_processed')->default(false);
            $table->uuid('bill_id');
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('due_date_alerts');
    }
};
