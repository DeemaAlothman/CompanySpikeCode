<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->integer('doctor_id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('age');
            $table->string('the_jop')->nullable();
            $table->string('photo')->nullable();
            $table->string('time');
            $table->string('date');
            $table->string('payments')->nullable();
            $table->string('state')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
