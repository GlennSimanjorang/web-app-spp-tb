<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('xendit_virtual_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->unique();
            $table->string('account_number', 30);
            $table->enum('bank_code', ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA']);
            $table->string('name', 100);
            $table->boolean('is_closed')->default(false);
            $table->timestamp('expiration_date');
            $table->decimal('expected_amount', 15, 2);
            $table->foreignUuid('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xendit_virtual_accounts');
    }
};
