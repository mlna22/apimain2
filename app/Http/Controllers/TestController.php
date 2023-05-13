<?php

namespace App\Http\Controllers;

use App\Models\Degree;
use App\Models\Semester;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function exfourty(Request $request)
    {
        //get everything
        $all = Degree::whereHas('courses', function ($q) use ($request) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->get();

        //get fourty
        $fourty = Degree::whereHas('courses', function ($q) use ($request) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->pluck("fourty");

        $sts = [];
        //isOld
        foreach ($all as $one) {
            $coursename = $one->courses->name_en;
            $courseyear = $one->courses->year;

            foreach ($fourty as $fourty) {
                if ($one->isOld == true) {
                    $fourty = "مستوفي";
                    array_push($sts, "مستوفي");
                } else {
                    if ($fourty >= 20) {
                        array_push($sts, "ناجح");
                    } else {
                        array_push($sts, "راسب");
                    }
                }
            }

        }
        //get number of examined students
        $exams = count($all);

        //get number of passed students
        $passes = Degree::whereHas('courses', function ($q) use ($request) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->where("fourty", ">=", "20")->count();

        //get number of failed students
        $fails = Degree::whereHas('courses', function ($q) use ($request) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->where("fourty", "<", "20")->count();

        //get name of each student
        $nameslist = [];
        foreach ($all as $names) {
            if ($names->student->year != $names->courses->year) {
                $name = $names->student->name_ar . " (محمل)";
            } else {
                $name = $names->student->name_ar;
            }
            array_push($nameslist, $name);
        }
        $year = "";
        switch ($courseyear) {
            case "first":{ $year = "المرحلة الاولى";
                    break;}
            case "second":{ $year = "المرحلة الثانية";
                    break;}
            case "third":{ $year = "المرحلة الثالثة";
                    break;}
            case "fourth":{ $year = "المرحلة الرابعة";
                    break;}
            case "fifth":{ $year = "المرحلة الخامسة";
                    break;}
        }
        $semester = Semester::where('isEnded', '=', false)->first();
        $number = "";
        switch ($semester->number) {
            case "first":{ $number = "الاول";
                    break;}
            case "second":{ $number = "الثاني";
                    break;}
        }
        return [
            "اسم المادة" => $coursename,
            "المرحلة" => $year,
            "السنة الدراسية" => $semester->year,
            "الكورس الدراسي" => $number,
            "اسماء الطلاب" => $nameslist,
            "الدرجات" => $fourty,
            "الحالة" => $sts,
            "عدد الطلاب" => $exams,
            "عدد الناجحين" => $passes,
            "عدد الراسبين" => $fails,
        ];
    }

    public function exfinal(Request $request)
    {
        $all = Degree::whereHas('courses', function ($q) use ($request) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->get();
        //get name of each student
        $nameslist = [];
        foreach ($all as $names) {
            if ($names->student->year != $names->courses->year) {
                $name = $names->student->name_ar . " (محمل)";
            } else {
                $name = $names->student->name_ar;
            }
            array_push($nameslist, $name);
        }

        $semester = Semester::where('isEnded', '=', false)->first();
        $number = "";
        switch ($semester->number) {
            case "first":{ $number = "الاول";
                    break;}
            case "second":{ $number = "الثاني";
                    break;}
        }
        $fourty = [];
        $sixty1 = [];
        $sixty2 = [];
        $sixty3 = [];
        $final = [];
        $sts = [];
        $approx = [];
        $finalnum = '';
        foreach ($all as $one) {
            array_push($fourty, $one->fourty);
            $coursename = $one->courses->name_en;
            $courseyear = $one->courses->year;
            $unit = $one->courses->unit;
            $success = $one->courses->success;
            switch ($request->number) {
                case 1:{
                        $sums = Degree::whereHas('courses', function ($q) use ($request) {
                            $semester = Semester::where('isEnded', '=', false)->first();
                            $id = $semester->id;
                            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
                        })->with("courses")->with(["student" => function ($q) {
                            $q->where('isGrad', "=", false);
                        }])->avg('final1');

                        $finalex = $one->final1;
                        array_push($final, $finalex);
                        array_push($sixty1, $one->sixty1);
                        $sts2 = $one->sts === "pass" ? "ناجح" : "راسب";
                        array_push($sts, $sts2);
                        array_push($approx, $one->approx);
                        $finalnum = "الاول";
                        break;
                    }
                case 2:{
                        $sums = Degree::whereHas('courses', function ($q) use ($request) {
                            $semester = Semester::where('isEnded', '=', false)->first();
                            $id = $semester->id;
                            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
                        })->with("courses")->with(["student" => function ($q) {
                            $q->where('isGrad', "=", false);
                        }])->avg('final2');

                        $finalex = $one->final2;
                        $six2 = $one->sixty2;
                        $sts2 = $one->sts == "pass" ? "ناجح" : "راسب";
                        $approx2 = $one->approx;
                        array_push($sixty2, $six2);
                        array_push($final, $finalex);
                        array_push($sts, $sts2);
                        array_push($approx, $approx2);
                        $finalnum = "الثاني";
                        break;
                    }
                case 3:{
                        $sums = Degree::whereHas('courses', function ($q) use ($request) {
                            $semester = Semester::where('isEnded', '=', false)->first();
                            $id = $semester->id;
                            $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
                        })->with("courses")->with(["student" => function ($q) {
                            $q->where('isGrad', "=", false);
                        }])->avg('final3');
                        $finalex = $one->final3;
                        $six3 = $one->sixty3;
                        array_push($sixty3, $six3);
                        array_push($final, $finalex);
                        $sts2 = $one->sts === "pass" ? "ناجح" : "معيد";
                        $approx2 = $one->approx;
                        array_push($sts, $sts2);
                        array_push($approx, $approx2);
                        $finalnum = "الثالث";
                        break;
                    }

            }
            $passes = Degree::whereHas('courses', function ($q) use ($request) {
                $semester = Semester::where('isEnded', '=', false)->first();
                $id = $semester->id;
                $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
            })->with("courses")->with(["student" => function ($q) {
                $q->where('isGrad', "=", false);
            }])->where("sts", "=", "success")->count();

            //get number of failed students
            $fails = Degree::whereHas('courses', function ($q) use ($request) {
                $semester = Semester::where('isEnded', '=', false)->first();
                $id = $semester->id;
                $q->where('semester_id', '=', $id)->where('id', "=", "$request->course_id");
            })->with("courses")->with(["student" => function ($q) {
                $q->where('isGrad', "=", false);
            }])->where("sts", "=", "fail")->count();
        }
        return [
            "السنة الدراسية" => $semester->year,
            "الكورس الدراسي" => $number,
            "المادة" => $coursename,
            "الصف" => $courseyear,
            "اسماء الطلاب" => $nameslist,
            "الدور" => $finalnum,
            "السعي" => $fourty,
            "درجة الامتحان النهائي" => $final,
            "درجة النجاح الصغرى" => $success,
            "النتيجة النهائية" => $sts,
            "التقدير" => $approx,
            "عدد الممتحنين" => count($all),
            "عدد الناجحين" => $passes,
            "عدد المعيدين" => $fails,
            "نسبة النجاح" => ((count($all) - $fails) / count($all)) * 100 . "%",
            "معدل الدرجة" => round($sums,2) . "%",
        ];

    }
}

//FOR ALL COURSES

/* $all = Degree::whereHas('courses', function ($q) use ($request) {
$semester = Semester::where('isEnded', '=', false)->first();
$id = $semester->id;
$q->where('semester_id', '=', $id);
})->with("courses")->with(["student" => function ($q) {
$q->where('isGrad', "=", false);
}])->get();
//get name of each student
$nameslist = [];
foreach ($all as $names) {
if ($names->student->year != $names->courses->year) {
$name = $names->student->name_ar . " (محمل)";
} else {
$name = $names->student->name_ar;
}
array_push($nameslist, $name);
}

$semester = Semester::where('isEnded', '=', false)->first();
$number = "";
switch ($semester->number) {
case "first":{ $number = "الاول";
break;}
case "second":{ $number = "الثاني";
break;}
}
$fourty = [];
$sixty1 = [];
$sixty2 = [];
$sixty3 = [];
$final = [];
$sts = [];
$approx = [];
$finalnum = '';

foreach ($all as $one) {
array_push($fourty, $one->fourty);
switch ($request->number) {
case 1:{
$finalex = $one->final1;
array_push($final, $finalex);
array_push($sixty1, $one->sixty1);
array_push($sts, $one->sts);
array_push($approx, $one->approx);
$finalnum = "الاول";
break;
}
case 2:{
$finalex = $one->final2;
$six2 = $one->sixty2;
$sts2 = $one->sts;
$approx2 = $one->approx;
array_push($sixty2, $six2 );
array_push($final, $finalex);
array_push($sts, $sts2 );
array_push($approx, $approx2 );
$finalnum = "الثاني";
break;
}
case 3:{
$finalex = $one->final3;
$six3 = $one->sixty3;
array_push($sixty3, $six3 );
array_push($final, $finalex);
$sts2 = $one->sts;
$approx2 = $one->approx;
array_push($sts, $sts2 );
array_push($approx, $approx2 );
$finalnum = "الثالث";
break;
}

}
}
return [
"السنة الدراسية" => $semester->year,
"الكورس الدراسي" => $number,
"اسماء الطلاب" => $nameslist,
"الدور" => $finalnum,
"السعي" => $fourty,
"درجة الامتحان النهائي"=>$final,

];
 */
