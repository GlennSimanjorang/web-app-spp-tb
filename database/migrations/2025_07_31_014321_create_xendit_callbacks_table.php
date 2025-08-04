<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('xendit_callbacks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('callback_type', ['invoice', 'virtual_account', 'ewallet', 'qris']);
            $table->string('xendit_id', 100);
            $table->string('event_type', 50);
            $table->json('raw_data');
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->uuid('payment_id')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('xendit_callbacks');
    }
};
