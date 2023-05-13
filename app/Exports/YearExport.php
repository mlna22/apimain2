<?php

namespace App\Exports;

use App\Models\Degree;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;

class YearExport implements FromArray, WithMapping
{
    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct(Request $request)
    {
        $this->stu = $request->stu;
        $this->dornumber = $request->number;

    }
    function array(): array
    {
        $semester = Semester::where('isEnded', '=', false)->first();
        $stuid = $this->stu;
        $dornum = $this->dornumber;
        $all = Degree::whereHas('courses', function ($q) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id);
        })->with("courses")->where('student_id', "=", "$stuid")->get();
        $info = Student::where('id', '=', "$stuid")->first();
        //dd($all);
        //get info of student
        $sname = $info->name_ar;
        $syear = $info->year;
        $finalaverage = 0;

        switch ($syear) {
            case "first":{ $syear = "المرحلة الاولى";
                    $finalaverage = $info->avg1;
                    break;}
            case "second":{ $syear = "المرحلة الثانية";
                    $finalaverage = $info->avg2;

                    break;}
            case "third":{ $syear = "المرحلة الثالثة";
                    $finalaverage = $info->avg3;

                    break;}
            case "fourth":{ $syear = "المرحلة الرابعة";
                    $finalaverage = $info->avg4;

                    break;}
            case "fifth":{ $syear = "المرحلة الخامسة";
                    $finalaverage = $info->avg5;

                    break;}
        }

        //get info of semester
        $number = "";
        switch ($semester->number) {
            case "first":{ $number = "الاول";
                    break;}
            case "second":{ $number = "الثاني";
                    break;}
        }
        //get degrees of student
        $units = ["الوحدات"];
        $coursename = ["اسم المادة"];
        $approx = ["التقدير"];
        $unitsum = 0;
        $final = 0;
        $finalnum = '';
        $sum = 0 ; 
        foreach ($all as $one) {
            //get course info
            array_push($units, $one->courses->unit);
            array_push($coursename, $one->courses->name_en);
            array_push($approx, $one->approx);

            if ($semester->number == "first") {

                $unitsum = $unitsum + $one->courses->unit;
                if($one->final3!=null){
                    $final = $one->final3;
                } else if($one->final2!=null){
                    $final = $one->final2;
                } else if($one->final1!=null){
                    $final = $one->final1;
                }
                $final = $final * $one->courses->unit;
                $sum = $final + $sum;   
            }
            
        }
        if($semester->number == "first"){
            $avg = $sum / $unitsum;
            $finalaverage = round($avg,2);

        }

        $fails = Degree::whereHas('courses', function ($q) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id);
        })->with("courses")->where('student_id', "=", "$stuid")->where('sts','=','fail')->count();
        $sts = '';
        $string = '';
        if ($fails == 0 ){
            $sts = "ناجح";
            $fails = '';
        }else{
            $sts = " معيد ";
            $string = " مادة ";
        }
        $stuinfo = ["اسم الطالب",$sname,"المرحلة",$syear,"الدور",$finalnum,"الفصل الدراسي",$number,"السنة الدراسية",$semester->year];

        $st = ["النتيجة",$sts,$fails,$string];
        $average = ["معدل الفصلين",$finalaverage];
        return [$stuinfo,$units,$coursename,$approx,$st,$average];

    }
    /**
     * @var Invoice $invoice
     */
    public function map($ss): array
    {
        return $ss;
    }

}
