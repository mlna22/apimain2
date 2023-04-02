<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\course_instructor;
use App\Models\Degree;
use App\Models\Instructor;
use App\Models\Semester;
use Illuminate\Http\Request;

class InstructorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')
            ->only(['destroy', 'create', 'update']);
    }
    public function showAll()
    {
        $instructor = Instructor::select('*')->get();
        return $instructor;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name_ar' => 'required',
            'name_en' => 'required',
        ]);
        Instructor::create([
            'name_ar' => $request->name_ar,
            'name_en' =>  $request->name_en,
        ]);
    }
    public function update(Request $request){
        $request->validate([
            'id' => 'required',
            'name_ar' => 'required',
            'name_en' => 'required',
        ]);
        $instructor = Instructor::select('*')->where('id','=',"$request->id")->first();
        $instructor->name_ar = $request->name_ar;
        $instructor->name_en = $request->name_en;
        $instructor-> save();
    
    }
    public function destroy($id)
    {
        $semester = Semester::select('*')->get()->last();
        $id = $semester->id;
        $inscourse=Course::where("instructor_id","=","$id")->where("semester_id",'=',"$id")->get();
        if($inscourse==null){
        Instructor::destroy($id);}
        else return response(409,"لا يمكن حذف تدريسي لديه مواد حالية");
        return response('تم حذف التدريسي بنجاح', 200);
    }
}







