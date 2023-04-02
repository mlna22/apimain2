<?php

namespace App\Http\Controllers;

use App\Exports\CourseExport;
use App\Exports\FourtyExport;
use App\Exports\YearExport;
use App\Models\Carry;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Graduate;
use App\Models\Helps;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;

class DegreeController extends Controller
{

    public function getDegrees(Request $request)
    {
        $deg = Degree::whereHas('courses', function ($q) {
            $semester = Semester::select('*')->get()->last();
            $id = $semester->id;
            $q->where('semester_id', '=', $id);
        })->with("courses")->with("student")->get();
        return $deg;
    }
    
    public function getAllDegrees(Request $request)
    {
        $request->validate([
            "year" => "required",
        ]);
        $degrees = Degree::select("*")->whereHas('courses', function ($q) use ($request) {
            $year = $request->year;
            $q->whereHas('semester', function ($q) use ($year) {
                $q->where("year", "=", "$year");
            });
        })->with("courses")->with("student")->get();
        return $degrees;
    }

    public function getYearDegrees()
    {
        $semester = Semester::all()->last();
        $year = $semester->year;
        $degrees = Degree::select("*")->whereHas('courses', function ($q) use ($year) {
            $q->whereHas('semester', function ($q) use ($year) {
                $q->where("year", "=", "$year");
            });
        })->with("courses")->with("student")->get();
        return $degrees;
    }

    public function countFirst() //calc grades for first semester. Used in yearDeg page.

    {
        $deg = Degree::whereHas('courses', function ($q) {
            $cursem = Semester::where('isEnded', '=', false)->first();
            $yearsem = $cursem->year;
            $semester = Semester::where('year', "=", "$yearsem")->where('number', "=", "first")->first();
            $id = $semester->id;
            $q->where('semester_id', '=', "$id");
        })->with("courses")->with("student")->get();
        foreach ($deg as $deg) {
            $help = Helps::select('*')->where("degree_id", "=", "$deg->id")->first();
            if ($help == '[]') {
                $course = Course::select('*')->where('id', '=', "$deg->course_id")->first();
                if ($deg->sixty3 != null) {
                    $total = $deg->fourty + $deg->sixty3;
                    if ($total >= $course->success) {
                        $deg->final3 = $total;
                        $deg->sts = "pass";
                        $deg->approx = $this->getApprox($total, $course->success);
                    } else {
                        $deg->final3 = $total;
                        $deg->sts = "fail";
                        $deg->approx = $this->getApprox($total, $course->success);
                    }} else if ($deg->sixty2 != null) {
                    $total = $deg->fourty + $deg->sixty2;
                    if ($total >= $course->success) {
                        $deg->final2 = $total;
                        $deg->sts = "pass";
                        $deg->approx = $this->getApprox($total, $course->success);
                    } else {
                        $deg->final2 = $total;
                        $deg->sts = "fail";
                        $deg->approx = $this->getApprox($total, $course->success);
                    }} else if ($deg->sixty1 != null) {
                    $total = $deg->fourty + $deg->sixty1;
                    if ($total >= $course->success) {

                        $deg->final1 = $total;
                        $deg->sts = "pass";
                        $deg->approx = $this->getApprox($total, $course->success);
                    } else {
                        $deg->final1 = $total;
                        $deg->sts = 'fail';
                        $deg->approx = $this->getApprox($total, $course->success);
                    }}
                $deg->save();}
        }
    }

    public function countDegree() //calc grades for current semester no matter the number. Used in both yearDeg and DegCur.

    {
        $deg = Degree::whereHas('courses', function ($q) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id);
        })->with("courses")->with("student")->get();
        foreach ($deg as $deg) {
            $help = Helps::select('*')->where("degree_id", "=", "$deg->id")->first();
            if ($help == '[]' || $help == null) {
                $course = Course::select('*')->where('id', '=', "$deg->course_id")->first();
                if ($deg->sixty3 != null) {
                    $total = $deg->fourty + $deg->sixty3;
                    if ($total >= $course->success) {
                        $deg->final3 = $total;
                        $deg->sts = "pass";
                        $deg->approx = $this->getApprox($total, $course->success);
                    } else {
                        $deg->final3 = $total;
                        $deg->sts = "fail";
                        $deg->approx = $this->getApprox($total, $course->success);
                    }} else if ($deg->sixty2 != null) {
                    $total = $deg->fourty + $deg->sixty2;
                    if ($total >= $course->success) {
                        $deg->final2 = $total;
                        $deg->sts = "pass";
                        $deg->approx = $this->getApprox($total, $course->success);
                    } else {
                        $deg->final2 = $total;
                        $deg->sts = "fail";
                        $deg->approx = $this->getApprox($total, $course->success);
                    }} else if ($deg->sixty1 != null) {
                    $total = $deg->fourty + $deg->sixty1;
                    if ($total >= $course->success) {

                        $deg->final1 = $total;
                        $deg->sts = "pass";
                        $deg->approx = $this->getApprox($total, $course->success);
                    } else {
                        $deg->final1 = $total;
                        $deg->sts = 'fail';
                        $deg->approx = $this->getApprox($total, $course->success);
                    }}
                $deg->save();
            }}
    }

    public function pass(Request $request)
    {
        $request->validate([
            "grad_year" => "required",
            "level" => "required",
        ]);
        $semester = Semester::select('*')->where("isEnded", "=", "0")->first();
        if ($semester == null) {
            return response('الكورس الدراسي منتهي و لا يمكن عبور الطلبة منه', 409);
        } else if ($semester->number == 'first') {
            return response('لا يمكن عبور الطلبة من الكورس الاول', 410);
        } else if ($semester->bcalc == true || $semester->mcalc == true || $semester->pcalc == true) {
            return response('تم الاحتساب مسبقاً', 411);
        } else {

            //register that the process is calculated for the level, prevents it from happening twice.
            /*switch ($request->level) {
            case "bachaelor":{
            $semester->bcalc = true;
            break;}
            case "master":{ $semester->mcalc = true;
            break;}
            case "pHD":{ $semester->pcalc = true;
            break;}
            }
            $semester->save();
            */

            //the process is performed for each student.
            $stu = Student::select('*')->where("level", "=", "$request->level")->where('isGrad', '=', false)->where("isEnded", "=", false)->get();
            foreach ($stu as $stu) {

                //get average of student for courses only INSIDE their year. 
                $degrees = Degree::select("*")->whereHas('courses', function ($q) use ($semester) {
                    $year = $semester->year;
                    $q->where("isCounts", "=", true)->whereHas('semester', function ($q) use ($year) {
                        $q->where("year", "=", "$year");
                    }
                    );
                })->where("student_id", "=", "$stu->id")->get();

                $sum = 0;
                $final = 0;
                $unit = 0;
                foreach ($degrees as $degree) {
                    $units = Course::select("unit")->where("id", "=", "$degree->course_id")->first();
                    $unit = $unit + $units->unit;
                    if ($degree->final3 != null) {
                        $final = $degree->final3;
                    } else if ($degree->final2 != null) {
                        $final = $degree->final2;
                    } else if ($degree->final1 != null) {
                        $final = $degree->final1;
                    }
                    $final = $final * $units->unit;
                    $sum = $final + $sum;
                }
                /*
                //save average of student
                $avg = $sum / $unit;
                $this->setAvg($stu, $avg);
                */
                //Determine the average of the courses the student carries
                $carries = Carry::select("*")->where("student_id", "=", "$stu->id")->get();
                if ($carries == '[]') {
                } else {
                    foreach ($carries as $carries) {
                        //select all courses within the previous year.
                        $year = Course::select("year")->where("id", "=", "$carries->course_id")->first();
                        $degrees = Degree::whereHas("courses", function ($q) use ($year) {
                            $q->where("year", "=", "$year->year");
                        })->where("student_id", "=", "$stu->id")->get();
                        $sum = 0;
                        $final = 0;
                        $unit = 0;
                        foreach ($degrees as $degrees) {
                            $units = Course::select("*")->where("id", "=", "$degrees->course_id")->first();
                            $unit = $unit + $units->unit;
                            if ($degrees->final3 != null) {
                                $final = $degrees->final3;
                            } else if ($degrees->final2 != null) {
                                $final = $degrees->final2;
                            } else if ($degrees->final1 != null) {
                                $final = $degrees->final1;
                            }
                            $final = $final * $units->unit;
                            $sum = $final + $sum;

                        }
                        //calculate new average of the carried/attended year
                        $avg = $sum / $unit;
                        $this->setAvgYear($stu, $year->year, $avg);
                    }

                }

                //count failed courses for first semester
                $fails1 = Degree::select("*")->whereHas('courses', function ($q) use ($semester) {
                    $year = $semester->year;
                    $q->whereHas('semester', function ($q) use ($year) {
                        $q->where("year", "=", "$year")->where("number", "=", "first");
                    }
                    );
                })->where("student_id", "=", "$stu->id")->where("sts", "=", "fail")->get();

                $failed1 = count($fails1);

                //set carried courses
                if ($failed1 != 0) {
                    foreach ($fails1 as $fails1) {
                        $failedcourse = $fails1->course_id;
                        Carry::create([
                            'course_id' => $failedcourse,
                            'student_id' => $stu->id,
                            'attend_carry' => 'carry',
                        ]);
                    }

                }

                //count failed courses for second semester
                $fails2 = Degree::select("*")->whereHas('courses', function ($q) use ($semester) {
                    $year = $semester->year;
                    $q->whereHas('semester', function ($q) use ($year) {
                        $q->where("year", "=", "$year")->where("number", "=", "second");
                    }
                    );
                })->where("student_id", "=", "$stu->id")->where("sts", "=", "fail")->get();
                $failed2 = count($fails2);
                //set carried courses
                if ($failed2 != 0) {
                    foreach ($fails2 as $fails2) {
                        $failedcourse = $fails2->course_id;
                        Carry::create([
                            'course_id' => $failedcourse,
                            'student_id' => $stu->id,
                            'attend_carry' => 'carry',
                        ]);
                    }

                }
                $failed = false;
                if ($request->passavg != "" && $avg < $request->passavg) {
                    $failed = true;

                } 
                if ((($failed1 + $failed2) <= 2) && !$failed) {
                    $this->nextyear($stu); //pass student to next year if they have 2 or less fails for each semester
                }

                //Pass or graduate student
              else if ($stu->year == $request->grad_year && $failed1 == 0 && $failed2 == 0 && !$failed) {
                    $this->grad($stu, $request->grad_year);
                } //move graduate to graduates table if they have no fails

            }

        }
    }
    public function grad($stu, $gradyear)
    {
        if ($stu->year == $gradyear) {
            $stu->isGrad = true;
            $stu->save();
            Student::query()
                ->where('id', '=', "$stu->id")->each(function ($oldRecord) {
                $newRecord = $oldRecord->replicate();
                $newRecord->setTable('graduates');
                $newRecord->save();
            });
            $curgrad = Graduate::select('*')->where('name_ar', '=', "$stu->name_ar")->first();
            $finalavg = $this->getFinalAvg($curgrad);
            $curgrad->avg_final = $finalavg;
            $curgrad->save();
        }
    }
    public function getFinalAvg($stu)
    {
        $avg1 = $stu->avg1;
        $avg2 = $stu->avg2;
        $avg3 = $stu->avg3;
        $avg4 = $stu->avg4;
       
         if ($stu->year == 'fourth') {
            return ((0.1 * $avg1) + (0.2 * $avg2) + (0.3 * $avg3) + (0.4 * $avg4));
        }
    }

    public function nextyear($stu)
    {

        $year = $stu->year;
        if ($year == 'first') {
            $stu->year = 'second';
            $stu->save();
        } else if ($year == 'second') {
            $stu->year = 'third';
            $stu->save();
        } else if ($year == 'third') {
            $stu->year = 'fourth';
            $stu->save();
        } 
        
    }

    public function setAvg($stu, $avg)
    {
        $year = $stu->year;
        if ($year == "first") {
            $stu->avg1 = $avg;
        } else if ($year == "second") {
            $stu->avg2 = $avg;
        } else if ($year == "third") {
            $stu->avg3 = $avg;
        } else if ($year == "fourth") {
            $stu->avg4 = $avg;
        
        }
        $stu->save();
    }
    public function setAvgYear($stu, $year, $avg)
    {
        if ($year == "first") {
            $stu->avg1 = $avg;
        } else if ($year == "second") {
            $stu->avg2 = $avg;
        } else if ($year == "third") {
            $stu->avg3 = $avg;
        } else if ($year == "fourth") {
            $stu->avg4 = $avg;
        } 
        $stu->save();
    }
    public function getApprox($deg, $success)
    {
        if ($deg < $success) {
            return "F";
        }

        if ($deg <= 100 && $deg >= 90) {
            return "A";
        }

        if ($deg < 90 && $deg >= 80) {
            return "B";
        }

        if ($deg < 80 && $deg >= 70) {
            return "C";
        }

        if ($deg < 70 && $deg >= 60) {
            return "D";
        }

        if ($deg < 60 && $deg >= 50) {
            return "E";
        }

    }

    public function getForty(Request $request)
    {

        $request->validate([
            'course_id' => "required",
        ]);
        $deg = Degree::whereHas('courses', function ($q) use ($request) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->get();
        return $deg;

    }
    public function getStudentDegrees(Request $request)
    {
        $request->validate([
            'course_id' => "required",
            'student_id' => "required",
        ]);
        $results = Degree::where('student_id', "=", "$request->student_id")->where('course_id', '=', "$request->course_id")->first();
        return response($results, 200);
    }
    public function createStudentDegrees(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'course_id' => 'required',
        ]);
        $course = Course::select('*')->where('id', '=', "$request->course_id")->first();
        $student = Student::select('*')->where('id', '=', "$request->student_id")->first();

        $results = Degree::where('student_id', "=", "$student->id")->where('course_id', '=', "$course->id")->first();

        //code...
        if ($results == null) {

            Degree::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'fourty' => $request->fourty,
                'sixty1' => $request->sixty1,

            ]);
            return response('تم الاضافة', 200);
        } else if ($results != null) {
            $deg = Degree::select('*')->where('student_id', "=", "$student->id")->where("course_id", "=", "$course->id")->first();
            $deg->fourty = $request->fourty;
            $deg->sixty1 = $request->sixty1;
            $deg->sixty2 = $request->sixty2;
            $deg->sixty3 = $request->sixty3;
            $deg->save();
            return response('تم التحديث', 200);

        };

    }

    public function exportfourty(Request $request)
    {
        return (new FourtyExport($request))->download("coursefourty.xlsx");
    }
    public function exportcourse(Request $request)
    {
        return (new CourseExport($request))->download("course.xlsx");
    }
    public function exportresult(Request $request)
    {
        return (new YearExport($request))->download("student.xlsx");
    }

}
