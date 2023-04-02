<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('instructor_id');
            $table->boolean('isCounts')->default(true);
            $table->enum('level',['bachaelor','master','pHD']);
            $table->enum('year',['first','second','third','fourth']);
            $table->string('name_ar')->nullable();
            $table->string('name_en');
            $table->string('code');
            $table->double('success')->default(50);
            $table->double('unit');
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
        Schema::dropIfExists('courses');
    }
}
