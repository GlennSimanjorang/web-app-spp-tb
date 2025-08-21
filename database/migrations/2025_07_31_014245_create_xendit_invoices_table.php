<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('xendit_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->unique();
            $table->string('xendit_invoice_id', 100);
            $table->string('invoice_url', 500);
            $table->enum('status', ['pending', 'settled', 'expired'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('customer_name', 100);
            $table->string('customer_email', 100);
            $table->string('customer_phone', 15);
            $table->json('payment_methods')->nullable();
            $table->timestamps();
            $table->foreignUuid('bill_id')->nullable()->constrained('bills')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xendit_invoices');
    }
};
