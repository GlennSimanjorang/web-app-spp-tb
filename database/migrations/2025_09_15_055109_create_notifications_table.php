<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->string('title', 150);
            $table->text('message');
            $table->enum('type', [
                'payment_reminder',
                'payment_success',
                'payment_failed',
                'va_created',
                'invoice_created'
            ]);
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained('bills')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
