<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bill_number', 50)->unique();
            $table->string('month_year', 7)->nullable(); // YYYY-MM
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->uuid('payment_categories_id');
            $table->uuid('student_id');
            $table->uuid('academic_years_id');
            $table->timestamps();

            $table->foreign('payment_categories_id')->references('id')->on('payment_categories');
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('academic_years_id')->references('id')->on('academic_years');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bills');
    }
};
