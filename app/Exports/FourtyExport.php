<?php

namespace App\Exports;

use App\Models\Degree;
use App\Models\Semester;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FourtyExport implements FromArray, WithHeadings, WithMapping
{

    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */
    

    public function __construct(Request $request)
    {
        $this->course_id = $request->course_id;
    }


    function array(): array
    {
        $cid = $this->course_id;

        //get everything
        $all = Degree::whereHas('courses', function ($q) use ($cid) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->get();

        //get fourty
        $fourty = Degree::whereHas('courses', function ($q) use ($cid) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->pluck("fourty");

        $sts = [];
        $courseyear = '';
        //isOld
        foreach ($all as $one) {
            $coursename = $one->courses->name_en;

            $courseyear = $one->courses->year;
            foreach ($fourty as $deg) {
                if ($one->isOld == true) {
                    $deg = "مستوفي";
                    array_push($sts, "مستوفي");
                } else {
                    if ($deg >= 20) {
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
        $passes = Degree::whereHas('courses', function ($q) use ($cid) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
        })->with("courses")->with(["student" => function ($q) {
            $q->where('isGrad', "=", false);
        }])->where("fourty", ">=", "20")->count();

        //get number of failed students
        $fails = Degree::whereHas('courses', function ($q) use ($cid) {
            $semester = Semester::where('isEnded', '=', false)->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('id', "=", "$cid");
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
        switch ($semester->number) {
            case "first":{ $number = "الاول";
                    break;}
            case "second":{ $number = "الثاني";
                    break;}
        }
        $combined = [];
        foreach ($nameslist as $key => $val) {
            $combined[] = ['name' => $val, 'degree' => $fourty[$key], 'status' => $sts[$key]];
        }
        $try=["اسم المادة",$coursename,"المرحلة الدراسية",$year,"السنة الدراسية",$semester->year,"عدد الطلاب",$exams,"عدد الناجحين",$passes,"عدد الراسبين",$fails];
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

        return ['اسم الطالب', "السعي", "الحالة"];
    }
}
