<?php

namespace App\Http\Controllers;

use App\Models\Carry;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Helps;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SemesterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')
            ->only(['destroy'/*, 'create'*/, 'update']);
    }

    public function create(Request $request)
    {
        $request->validate([
           //'number' => 'required',
            'year' => 'required',
        ]);
        $semester = Semester::select("*")->selectRaw('substr(year,1,4) as first')->orderBy("first", "DESC")->orderBy("number", "DESC")->first();
        $isEnded = $semester->isEnded;
        $number = $semester->number;
       /* if (!Gate::allows('is-super')) {
           return response('انت غير مخول', 403);
        }*/

        //first
        $check = Semester::select("*")->selectRaw('substr(year,1,4) as first')->where("number", "=", "second")->orderBy("first", "DESC")->first();
            $sem = Semester::create([
                'isEnded' => false,
                'number' => 'first',
                'year' => $request->year,

            ]);
            $id = $sem->id;
            $courses = Course::select('*')->where('semester_id', '=', "$check->id")->get();

            foreach ($courses as $courses) {

                $carries = Carry::select("*")->where('course_id', "=", "$courses->id")->get();
                if ($carries != "[]") {
                    $new = Course::create([
                        'name_ar' => $courses->name_ar,
                        'name_en' => $courses->name_en,
                        'instructor_id' => $courses->instructor_id,
                        'level' => $courses->level,
                        'code' => $courses->code,
                        'semester_id' => $id,
                        'unit' => $courses->unit,
                        'year' => $courses->year,
                        'success' => $courses->success,
                    ]);
                    $nid = $new->id;
                    foreach ($carries as $carries) {
                        $carries->course_id = $nid;
                        $carries->save();
                    }
                }

            }

        //second
        $check = Semester::select("*")->selectRaw('substr(year,1,4) as first')->where("number", "=", "first")->orderBy("first", "DESC")->first();
        $sem = Semester::create([
            'isEnded' => false,
            'number' => 'second',
            'year' => $request->year,

        ]);
        $id = $sem->id;
        $courses = Course::select('*')->where('semester_id', '=', "$check->id")->get();
        foreach ($courses as $courses) {
            $carries = Carry::select("*")->where('course_id', "=", "$courses->id")->get();
            if ($carries != "[]") {
                $new = Course::create([
                    'name_ar' => $courses->name_ar,
                    'name_en' => $courses->name_en,
                    'instructor_id' => $courses->instructor_id,
                    'level' => $courses->level,
                    'code' => $courses->code,
                    'semester_id' => $id,
                    'unit' => $courses->unit,
                    'year' => $courses->year,
                    'success' => $courses->success,
                ]);
                $nid = $new->id;
                foreach ($carries as $carries) {
                    $carries->course_id = $nid;
                    $carries->save();
                }

                $yearcor = Degree::select("*")->whereHas("courses", function ($q) use ($new, $check) {
                    $q->where("semester_id", "=", "$check->id")->where("year", "=", "$new->year")->where("id", "!=", "$new->id");
                })->get();
                foreach ($yearcor as $yearcor) {
                    $oldcourse = Course::select("*")->where("id", "=", "$yearcor->course_id")->first();
                    $newcourse = Course::select("*")->where("semester_id", "=", "$id")->where("name_en", "=", "$oldcourse->name_en")->first();
                    if ($newcourse == null) {
                        $newcourse = Course::create([
                            'name_ar' => $oldcourse->name_ar,
                            'name_en' => $oldcourse->name_en,
                            'instructor_id' => $oldcourse->instructor_id,
                            'level' => $oldcourse->level,
                            'code' => $oldcourse->code,
                            'semester_id' => $id,
                            'unit' => $oldcourse->unit,
                            'year' => $oldcourse->year,
                            'success' => $oldcourse->success,
                        ]);
                        $newdeg=Degree::create([
                            "student_id"=> $yearcor->student_id,
                            "course_id"=>$newcourse->id,
                            "fourty"=>$yearcor->fourty,
                            "sixty1"=>$yearcor->sixty1,
                            "sixty2"=>$yearcor->sixty2,
                            "sixty3"=>$yearcor->sixty3,
                            "final1"=>$yearcor->final1,
                            "final2"=>$yearcor->final2,
                            "final3"=>$yearcor->final3,
                            "sts"=>$yearcor->sts,
                            "approx"=>$yearcor->approx,
                            "isOld"=>true,
                        ]);
                        $help=Helps::select("*")->where("degree_id","=","$yearcor->id")->get();
                        if($help!="[]"){
                        foreach($help as $help)
                        Helps::create([
                            "degree_id"=>$newdeg->id,
                            "amt"=>$help->amt,
                            "source"=>$help->source
                        ]);}
                    }
                }
            }

        }
        /*
        if ($isEnded == 1 && $number == "first") {

            if ($semester->year != $request->year) {
                return response("لا يوجد فصل اول لهذه السنة الدراسية", 410);
            }
            $check = Semester::select("*")->selectRaw('substr(year,1,4) as first')->where("number", "=", "second")->orderBy("first", "DESC")->first();
            $sem = Semester::create([
                'isEnded' => false,
                'number' => $request->number,
                'year' => $request->year,

            ]);
            $id = $sem->id;
            $courses = Course::select('*')->where('semester_id', '=', "$check->id")->get();

            foreach ($courses as $courses) {

                $carries = Carry::select("*")->where('course_id', "=", "$courses->id")->get();
                if ($carries != "[]") {
                    $new = Course::create([
                        'name_ar' => $courses->name_ar,
                        'name_en' => $courses->name_en,
                        'instructor_id' => $courses->instructor_id,
                        'level' => $courses->level,
                        'code' => $courses->code,
                        'semester_id' => $id,
                        'unit' => $courses->unit,
                        'year' => $courses->year,
                        'success' => $courses->success,
                    ]);
                    $nid = $new->id;
                    foreach ($carries as $carries) {
                        $carries->course_id = $nid;
                        $carries->save();
                    }
                }

            }

        } else if ($isEnded == 1 && $number == "second") {
            $check = Semester::select("*")->selectRaw('substr(year,1,4) as first')->where("number", "=", "first")->orderBy("first", "DESC")->first();
            $sem = Semester::create([
                'isEnded' => false,
                'number' => $request->number,
                'year' => $request->year,

            ]);
            $id = $sem->id;
            $courses = Course::select('*')->where('semester_id', '=', "$check->id")->get();
            foreach ($courses as $courses) {
                $carries = Carry::select("*")->where('course_id', "=", "$courses->id")->get();
                if ($carries != "[]") {
                    $new = Course::create([
                        'name_ar' => $courses->name_ar,
                        'name_en' => $courses->name_en,
                        'instructor_id' => $courses->instructor_id,
                        'level' => $courses->level,
                        'code' => $courses->code,
                        'semester_id' => $id,
                        'unit' => $courses->unit,
                        'year' => $courses->year,
                        'success' => $courses->success,
                    ]);
                    $nid = $new->id;
                    foreach ($carries as $carries) {
                        $carries->course_id = $nid;
                        $carries->save();
                    }
                    $yearcor = Degree::select("*")->whereHas("courses", function ($q) use ($new, $check) {
                        $q->where("semester_id", "=", "$check->id")->where("year", "=", "$new->year")->where("id", "!=", "$new->id");
                    })->get();
                    foreach ($yearcor as $yearcor) {
                        $oldcourse = Course::select("*")->where("id", "=", "$yearcor->course_id")->first();
                        $newcourse = Course::select("*")->where("semester_id", "=", "$id")->where("name_en", "=", "$oldcourse->name_en")->first();
                        if ($newcourse == null) {
                            $newcourse = Course::create([
                                'name_ar' => $oldcourse->name_ar,
                                'name_en' => $oldcourse->name_en,
                                'instructor_id' => $oldcourse->instructor_id,
                                'level' => $oldcourse->level,
                                'code' => $oldcourse->code,
                                'semester_id' => $id,
                                'unit' => $oldcourse->unit,
                                'year' => $oldcourse->year,
                                'success' => $oldcourse->success,
                            ]);
                            $newdeg=Degree::create([
                                "student_id"=> $yearcor->student_id,
                                "course_id"=>$newcourse->id,
                                "fourty"=>$yearcor->fourty,
                                "sixty1"=>$yearcor->sixty1,
                                "sixty2"=>$yearcor->sixty2,
                                "sixty3"=>$yearcor->sixty3,
                                "final1"=>$yearcor->final1,
                                "final2"=>$yearcor->final2,
                                "final3"=>$yearcor->final3,
                                "sts"=>$yearcor->sts,
                                "approx"=>$yearcor->approx,
                                "isOld"=>true,
                            ]);
                            $help=Helps::select("*")->where("degree_id","=","$yearcor->id")->get();
                            if($help!="[]"){
                            foreach($help as $help)
                            Helps::create([
                                "degree_id"=>$newdeg->id,
                                "amt"=>$help->amt,
                                "source"=>$help->source
                            ]);}
                        }
                    }
                }

            }
        } else {
            return response('غير مسموح', 409);
        }
        */

    }
    public function show()
    {
        $sem = Semester::select("*")->selectRaw('substr(year,1,4) as first')->where("number", "=", "first")->orderBy("first", "ASC")->get();
        return $sem;
    }

    public function end()
    {

        $semester = Semester::select("*")->where("isEnded", "=", false)->first();
        $isEnded = $semester->isEnded;
        if ($isEnded == 0) {
            $semester->isEnded = 1;
            $semester->save();
        } else {
            return response('غير مسموح', 409);
        }

    }
}
