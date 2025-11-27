<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->string('name');
            $table->string('nisn', 20)->unique();
            $table->string('kelas', 10);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();;
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};
