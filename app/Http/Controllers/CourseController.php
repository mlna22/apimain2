<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Degree;
use App\Models\Instructor;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Constraint\Count;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')
            ->only(['destroy', 'create', 'update']);
    }
    // public function showCurrent()
    // {
    //     $semester = Semester::where('isEnded', '=', false)->first();
    //     $id = $semester->id;
    //     $courses = Course::with('instructor')->with('studentsCarry')->select('*')->where('semester_id', "=", $id)->get();
    //     $newList = [];
    //     foreach ($courses as $course) {
    //         $students = Student::select('*')->where('level', '=', $course->level)
    //             ->where('year', '=', $course->year)->where('isEnded',"=",false)
    //             ->get();
    //         $course['students'] = $students;
    //         array_push($newList, $course);
    //     }
    //     return $newList;
    // }

    public function showCurrent(Request $request,$year,$number,$semester)
    {
        $semester = Semester::where("number","=",$number)->where('year','=',$semester)->first();
        $id = $semester->id;
        $query = Course::select('*')->where('semester_id', "=", $id)->where("year","=",$year);
        if ($request->has('search')) {
            $query->where('name_en', 'like', '%' . $request->input('search') . '%');
        }
        $data = $query->paginate(10);
        return $data;
    }
    public function showStudents($rid){
        $semester = Semester::where('isEnded', '=', false)->first();
        $id = $semester->id;
        $coid = $rid; 
        $coursesQuery = Course::with('studentsCarry')->select('*')->where('semester_id', "=", $id)->where('id','=',$coid);
        $courses = $coursesQuery->paginate(10);

        $newList = [];
        foreach ($courses as $course) {
            $students = Student::select('*')->where('level', '=', $course->level)->where("isGrad", '=', false)
                ->where('year', '=', $course->year)->where('isEnded', "=", false)
                ->get();
            $students_carry = $course->studentsCarry()->where('isEnded', '=', false)->get();
            $merged_students = array_merge($students->toArray(), $students_carry->toArray());
            $course['students'] = $merged_students;
            array_push($newList, $course);
        }
        $paginatedList = new LengthAwarePaginator(
            $newList,
            $coursesQuery->count(),
            10,
            $courses->currentPage(),
            ['path' => $courses->getOptions()['path']]
        );

        return $paginatedList;
    

    }
    // public function showAll(Request $request)
    // {
    //     $year = $request->year;
    //     $courses = Course::whereHas("semester", function ($q) use ($year) {
    //         $q->where("year", "=", "$year");
    //     })->with('instructor')->with('studentsCarry')->select('*')->get();
    //     $newList = [];
    //     foreach ($courses as $course) {
    //         $students = Student::select('*')->where('level', '=', $course->level)
    //             ->where('year', '=', $course->year)
    //             ->get();
    //         $course['students'] = $students;
    //         array_push($newList, $course);
    //     }
    //     return $newList;
    // }


    
    public function create(Request $request)
    {
        $request->validate([
            'name_ar' => 'required',
            'name_en' => 'required',
            'level' => 'required',
            'code' => 'required',
            'unit' => 'required',
            'year' => 'required',
            'semester' =>'required',
            'ins_name' => 'required',
            'success' => 'required',
        ]);
        $course = Course::where('name_en', '=', "$request->name_en")->first();
        if ($course !== null && $course->name_en != $request->name_en) {
            return response('المادة موجودة مسبقاً', 409);
        }
        $semester = Semester::where('isEnded', '=', false)->where('number','=',$request->semester)->first();
        $instructor = Instructor::where('name_ar', '=', "$request->ins_name")->first();
        if ($instructor == null) {
            return response("لا يوجد تدريسي بهذا الاسم, الرجاء التأكد", 409);
        }

        Course::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'instructor_id' => $instructor->id,
            'level' => $request->level,
            'code' => $request->code,
            'semester_id' => $semester->id,
            'unit' => $request->unit,
            'year' => $request->year,
            'success' => $request->success,
        ]);
        $crs = Course::all()->last();
        $crs->isCounts = $request->isCounts;
        $crs->save();
    }

    public function update(Request $request)
    {
        $request->validate([
            'name_ar' => 'required',
            'name_en' => 'required',
            'code' => 'required',
            'unit' => 'required',
            'ins_name' => 'required',
            'success' => 'required',
        ]);
        $instructor = Instructor::where('name_ar', '=', "$request->ins_name")->first();
        if ($instructor == null) {
            return response("لا يوجد تدريسي بهذا الاسم, الرجاء التأكد", 409);
        }
        $course = Course::select('*')->where('id', '=', "$request->id")->first();
        $course->name_ar = $request->name_ar;
        $course->name_en = $request->name_en;
        $course->code = $request->code;
        $course->unit = $request->unit;
        $course->instructor_id = $instructor->id;
        $course->success = $request->success;
        $course->save();

    }
    public function destroy($id)
    {
        $deg = Degree::where("course_id", "=", $id)->get();
        Course::destroy($id);
        foreach ($deg as $d) {
            Degree::destroy($deg);
        }
        return response('تم حذف الكورس بنجاح', 200);
    }
}
