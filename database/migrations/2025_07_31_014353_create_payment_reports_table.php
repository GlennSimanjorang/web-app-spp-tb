<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('report_period', 20);
            $table->date('report_date');
            $table->decimal('total_amount', 15, 2);
            $table->integer('total_transactions');
            $table->text('notes')->nullable();
            $table->json('report_data');
            $table->uuid('academic_year_id')->nullable();
            $table->uuid('payment_category_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('academic_year_id')->references('id')->on('academic_years');
            $table->foreign('payment_category_id')->references('id')->on('payment_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_reports');
    }
};
