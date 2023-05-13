<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDegreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('degrees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->decimal('fourty')->nullable();
            $table->decimal('sixty1')->nullable();
            $table->decimal('sixty2')->nullable();
            $table->decimal('sixty3')->nullable();
            $table->decimal('final1')->nullable();
            $table->decimal('final2')->nullable();
            $table->decimal('final3')->nullable();
            $table->enum('approx',['A','B','C','D','E','F'])->nullable();
            $table->boolean("isOld")->nullable();
            $table->enum('sts',['pass','fail','carry','other'])->nullable();
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
        Schema::dropIfExists('degrees');
    }
}
