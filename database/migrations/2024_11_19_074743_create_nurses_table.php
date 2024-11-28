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
    {Schema::create('nurses', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('phone');
        $table->string('gender');
        $table->string('age');
        $table->string('address')->nullable();
        $table->string('the_jop')->default('nothing');
        $table->string('time');
        $table->string('salary');
        $table->string('date');
        $table->string('profile_picture')->nullable();
     // تغيير إلى unsignedBigInteger
     
        $table->string('password');
        $table->integer('doctor_id'); 
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
        Schema::dropIfExists('nurses');
    }
};
