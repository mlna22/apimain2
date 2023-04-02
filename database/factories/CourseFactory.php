<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "semester_id" => 1,
            "instructor_id"=>1,
            "level" => "master",
            "year" => "first",
            "name_ar" => "fatma",
            "name_en" => "fatma",
            "code" => "fatma",
            "success" => 50,
            "unit" => 5.0,
        ];
    }
}