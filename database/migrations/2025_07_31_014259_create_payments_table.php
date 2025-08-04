<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('payment_date');
            $table->decimal('amount_paid', 15, 2);
            $table->enum('payment_method', [
                'virtual_account',
                'qris',
                'ewallet',
                'retail_outlet',
                'credit_card',
                'bank_transfer',
                'invoice'
            ]);
            $table->string('reference_number', 100)->nullable();
            $table->string('xendit_payment_id', 100)->nullable();
            $table->string('xendit_external_id', 100)->nullable();
            $table->enum('status', ['pending', 'settled', 'failed', 'cancelled'])->default('pending');
            $table->json('callback_data')->nullable();
            $table->uuid('processed_by')->nullable();
            $table->uuid('bill_id');
            $table->uuid('xendit_virtual_account_id')->nullable();
            $table->uuid('xendit_invoice_id')->nullable();
            $table->timestamps();

            $table->foreign('processed_by')->references('id')->on('users');
            $table->foreign('bill_id')->references('id')->on('bills');
            $table->foreign('xendit_virtual_account_id')->references('id')->on('xendit_virtual_accounts');
            $table->foreign('xendit_invoice_id')->references('id')->on('xendit_invoices');
        });

        DB::statement("
            ALTER TABLE payments ADD CONSTRAINT chk_payment_source CHECK (
                (xendit_virtual_account_id IS NOT NULL AND xendit_invoice_id IS NULL) OR
                (xendit_virtual_account_id IS NULL AND xendit_invoice_id IS NOT NULL)
            )
        ");
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
