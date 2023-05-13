<?php

namespace App\Exports;

use App\Models\Degree;
use App\Models\Semester;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CourseExport implements FromArray ,WithHeadings, WithMapping
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(Request $request)
    {
        $this->course_id = $request->course_id;
        $this->dornumber = $request->number;
    }
    public function array(): array
    {
        $cid = $this->course_id;
        $dornum = $this->dornumber;
        $all = Degree::whereHas('courses', function ($q) use ($cid) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
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
        $sixty = [];
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
            switch ($dornum) {
                case 1:{
                        $sums = Degree::whereHas('courses', function ($q) use ($cid) {
                            $semester = Semester::where('isEnded', '=', false)->first();
                            $id = $semester->id;
                            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
                        })->with("courses")->with(["student" => function ($q) {
                            $q->where('isGrad', "=", false);
                        }])->avg('final1');

                        $finalex = $one->final1;
                        array_push($final, $finalex);
                        array_push($sixty, $one->sixty1);
                        $sts2 = $one->sts === "pass" ? "ناجح" : "راسب";
                        array_push($sts, $sts2);
                        array_push($approx, $one->approx);
                        $finalnum = "الاول";
                        break;
                    }
                case 2:{
                        $sums = Degree::whereHas('courses', function ($q) use ($cid) {
                            $semester = Semester::where('isEnded', '=', false)->first();
                            $id = $semester->id;
                            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
                        })->with("courses")->with(["student" => function ($q) {
                            $q->where('isGrad', "=", false);
                        }])->avg('final2');

                        $finalex = $one->final2;
                        $six2 = $one->sixty2;
                        $sts2 = $one->sts == "pass" ? "ناجح" : "راسب";
                        $approx2 = $one->approx;
                        array_push($sixty, $six2);
                        array_push($final, $finalex);
                        array_push($sts, $sts2);
                        array_push($approx, $approx2);
                        $finalnum = "الثاني";
                        break;
                    }
                case 3:{
                        $sums = Degree::whereHas('courses', function ($q) use ($cid) {
                            $semester = Semester::where('isEnded', '=', false)->first();
                            $id = $semester->id;
                            $q->where('semester_id', '=', $id)->where('id', "=", "$cid->course_id");
                        })->with("courses")->with(["student" => function ($q) {
                            $q->where('isGrad', "=", false);
                        }])->avg('final3');
                        $finalex = $one->final3;
                        $six3 = $one->sixty3;
                        array_push($sixty, $six3);
                        array_push($final, $finalex);
                        $sts2 = $one->sts === "pass" ? "ناجح" : "معيد";
                        $approx2 = $one->approx;
                        array_push($sts, $sts2);
                        array_push($approx, $approx2);
                        $finalnum = "الثالث";
                        break;
                    }

            }
            $passes = Degree::whereHas('courses', function ($q) use ($cid) {
                $semester = Semester::where('isEnded', '=', false)->first();
                $id = $semester->id;
                $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
            })->with("courses")->with(["student" => function ($q) {
                $q->where('isGrad', "=", false);
            }])->where("sts", "=", "success")->count();

            //get number of failed students
            $fails = Degree::whereHas('courses', function ($q) use ($cid) {
                $semester = Semester::where('isEnded', '=', false)->first();
                $id = $semester->id;
                $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
            })->with("courses")->with(["student" => function ($q) {
                $q->where('isGrad', "=", false);
            }])->where("sts", "=", "fail")->count();
        }
        $combined = [];
        foreach ($nameslist as $key => $val) {
            $combined[] = ['name' => $val, 'degree' => $fourty[$key],"sixty"=>$sixty[$key],"final"=>$final[$key], 'status' => $sts[$key], 'approx'=>$approx[$key]];
        }
        $year='';
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
        $try=["اسم المادة",$coursename,"الوحدة",$unit,"درجة النجاح الصغرى",$success,"المرحلة الدراسية",$year,"السنة الدراسية",$semester->year,"الدور",$finalnum,"عدد الطلاب",count($all),"عدد الناجحين",$passes,"عدد الراسبين",$fails,"نسبة النجاح",((count($all) - $fails) / count($all)) * 100 . "%","معدل الدرجة",round($sums,2) . "%"];
        return [$combined,$try];

    }
        /**
     * @var Invoice $invoice
     */
    public function map($ss): array
    {   
        return $ss;
    }
    public function headings(): array
    {

        return ['اسم الطالب', "السعي", "درجة الامتحان","الدرجة النهائية","الحالة","التقدير"];
    }
}
