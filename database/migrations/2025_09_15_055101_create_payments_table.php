<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // cukup, otomatis auto-increment & primary key
            $table->date('payment_date'); // karena hanya butuh tanggal (bukan waktu)
            $table->decimal('amount_paid', 15, 2);

            // Pastikan 'cash' dan 'transfer' termasuk!
            $table->enum('payment_method', [
                'cash',
                'transfer',
                'virtual_account',
                'qris',
                'ewallet',
                'retail_outlet',
                'credit_card',
                'bank_transfer',
                'invoice'
            ]);

            // Kolom dari seeder
            $table->string('reference_number')->nullable(); // no. transfer, dll
            $table->string('receipt_of_payment')->nullable(); // path file bukti bayar
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');

            // Midtrans fields
            $table->string('midtrans_order_id')->unique()->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->string('midtrans_va_number')->nullable();
            $table->string('midtrans_fraud_status')->nullable();
            $table->json('midtrans_raw_response')->nullable();

            // Relasi
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();

            // created_at & updated_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
