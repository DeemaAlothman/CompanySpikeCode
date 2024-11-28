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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('gender');
            $table->string('age');
            $table->string('address');
            $table->string('profile_picture')->nullable();
            $table->string('password');
            $table->string('facebookacount')->nullable();
            $table->string('instaaccount')->nullable();
            $table->string('years_experiense')->nullable();;
            $table->string('about_doctor')->nullable();
            $table->string('doctors_time')->default('nothing');
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
        Schema::dropIfExists('doctors');
    }
};
