<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Semester;


class HomeController extends Controller
{
    public function counts (Request $request){
        $students=Student::SELECT('*')->get();
        $students = $students->where('isEnded', false)->where('isGrad', false)->count();
        $instuctors=Instructor::SELECT('*')->get();
        $instuctors=$instuctors->count();
        $semester=Semester::SELECT('*')->where("isEnded","=",false)->first();
        $courses=Course::SELECT('*')->where('semester_id','=',"$semester->id")->get();
        $courses=$courses->count();
        
        $year=$semester->year;
        $number=$semester->number;
        return[
            'students'=>$students,
            'instuctors'=>$instuctors,
            'courses'=>$courses,
            'year'=>$year,
            'number'=>$number,
        ];
    }
}
