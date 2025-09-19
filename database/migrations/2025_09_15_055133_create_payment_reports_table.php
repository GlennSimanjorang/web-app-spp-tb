<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_reports', function (Blueprint $table) {
            $table->Id('id')->primary();
            $table->string('report_period', 20);
            $table->date('report_date');
            $table->decimal('total_amount', 15, 2);
            $table->integer('total_transactions');
            $table->text('notes')->nullable();
            $table->json('report_data');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('payment_category_id')->constrained('payment_categories')->cascadeOnDelete();


        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_reports');
    }
};
