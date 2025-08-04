<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
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
            $table->uuid('user_id');
            $table->uuid('bill_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
