<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->string('name');
            $table->enum('role', ['admin', 'parents']);
            $table->string('email')->unique();
            $table->string('number')->nullable(); // <= Tambahkan
            $table->text('password');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
