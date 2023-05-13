<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\Carry;
use App\Models\Degree;
use Illuminate\Support\Facades\Gate;

class StudentController extends Controller
{

            /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')
            ->only(['destroy', /*'create', */'update']);
    }
    public function showAll(Request $request,$year){
        $query = Student::select('*')->where("year","=",$year)->where('isGrad','=',false); 
        if ($request->has('search')) {
            $query->where('name_ar', 'like', '%' . $request->input('search') . '%');
        }
        $data = $query->paginate(10);
        return $data;
    }
    public function getCurAvg(Request $request){
        $id = $request->id; 
        $degrees = Degree::select("*")->whereHas('courses', function($q) {
            $q->where("isCounts","=",false)->whereHas('semester', function($q){
                $q->where("isEnded","=",false);
            });
        })->where("student_id","=","$id")->get();
        $sum = 0 ;
        $final = 0; 
        $unit = 0; 
        foreach($degrees as $degrees){
            $id = $degrees->course_id; 
            $units = Course::select("unit")->where("id","=","$id")->first();
            $unit = $unit + $units->unit;
            if($degrees->final3!=null){
                $final = $degrees->final3;
            } else if($degrees->final2!=null){
                $final = $degrees->final2;
            } else if($degrees->final1!=null){
                $final = $degrees->final1;
            }
            $final = $final * $units->unit; 
            $sum = $final + $sum;   
        }
        $avg = $sum / $unit;
        return round($avg,2);
    }
    public function create(Request $request){
        $request->validate([
            'name_ar' => 'required',
            'name_en' => 'required',
           // 'level'=>'required',
            'year' => 'required',
        ]);
        
            Student::create([
                'name_ar' => $request->name_ar,
                'name_en' =>  $request->name_en,
              //  'level'=> $request->level,
                'year' =>  $request->year,
            ]); 
    }
    public function destroy($id)
    {
        $deg=DEGREE::where("student_id","=",$id)->get();

       Student::destroy($id);
       foreach ($deg as $d ) {
            Degree::destroy($deg);
       }
        return response(200);
    }

    public function remove($id)
    {

     $student =Student::where('id','=',"$id")->first();
     $student->isEnded = true; 
     $student->save();
        return response(200);
    }

public function update(Request $request){
    $request->validate([
        'id' => 'required',
        'name_ar' => 'required',
        'name_en' => 'required',
      //  'level'=>'required',
        'year' => 'required',
        'note'=> 'nullable',
    ]);
    $students = Student::select('*')->where('id','=',"$request->id")->first();
    $students->name_ar = $request->name_ar;
    $students->name_en = $request->name_en;
   // $students->level = $request->level;
    $students->year = $request->year;
    $students->note = $request->note;
    $students-> save();

}
public function attendency(Request $request){
    $request->validate([
        'student_id'=>'required',
        'name_en'=>'required',
    ]);
    $course = Course::select('*')->where('name_en','=',"$request->name_en")->first();
    $student = Student::select('*')->where('id','=',"$request->student_id")->first();
    if($course==null){
        return response(410);
    }
    if($student->level != $course->level){
        return response(409);
    }
    if($student->year == $course->year){
        return response(411);
    }
    $carry =Carry::where("course_id","=","$course->id")->where("student_id","=","$student->id")->first();
    if($carry==null){
        Carry::create([
            'course_id'=>$course->id,
            'student_id'=>$student->id,
            'attend_carry'=>'attend'
        ]);
        return response('done',200);
    } else  return response(411);

}

}
