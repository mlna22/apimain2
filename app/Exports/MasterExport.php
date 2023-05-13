<?php

namespace App\Exports;

use App\Models\Degree;
use App\Models\Semester;
use App\Models\Graduate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;

class MasterExport implements FromArray, WithMapping
{
    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct(Request $request)
    {
        $this->year = $request->year;
        $this->level=$request->level;
    }
    function array(): array
    {
        dd();
        $year = $this->year; 
        $level = $this->level; 

        //get info for first semester
        $firstsem = Degree::whereHas('courses', function ($q) use ($year,$level) {
            $semester = Semester::where('year', '=', "$year")->where("number",'=','first')->first();
            $id = $semester->id;
            $q->where('semester_id', '=', $id)->where('year', "=", "$level");
        })->with("courses")->with("student")->get();
        
        $names = ['اسم الطالب'];
        $units = ['الوحدات']; 
        $number = ['الفصل الدراسي'];
        $final = [''];
        //get student info 
        $stuid = $this->stu;
        $info = Graduate::where('id', '=', "$stuid")->first();
        $sname = $info->name_ar;
        $avg1=$info->avg1;
        $avg2=$info->avg2;
        $avg3=$info->avg3;
        $avg4=$info->avg4;
        $avg5=$info->avg5;
        $final = $info->avg_final;
        $summer=$info->summer_deg; 
        
        //get student degrees
        $all = Degree::with("courses")->where('student_id',"=","$stuid")->get();
        //get their grad year 
        $gradyear = '';
        $fifthsem = Degree::whereHas('courses', function ($q) {
            $q->where('year', '=', 'fifth');
        })->with("courses")->where('student_id', "=", "$stuid")->first();

        $fourthsem = Degree::whereHas('courses', function ($q) {
            $q->where('year', '=', 'fourth');
        })->with("courses")->where('student_id', "=", "$stuid")->first();
        if($fifthsem !=null){
            $gradyear = $fifthsem->courses->semester->year;
        }
        else {
            $gradyear = $fourthsem->courses->semester->year;
        }

        //get degrees 
        $units = ["الوحدات"];
        $coursename = ["اسم المادة"];
        $approx = ["التقدير"];
        foreach($all as $one){
            if($one->sts == 'pass'){
            array_push($units, $one->courses->unit);
            array_push($coursename, $one->courses->name_en);
            array_push($approx, $one->approx);
            }
        }
        $stuinfo = ["اسم الطالب",$sname,"السنة التخرج",$gradyear];
        $averages = ["معدل السنة الاولى",$avg1,"معدل السنة الثانية",$avg2,"معدل السنة الثالثة",$avg3,"معدل السنة الرابعة",$avg4,"معدل السنة الخامسة",$avg5,"المعدل التراكمي",$final,"درجة التدريب الصيفي",$summer];
        return [$stuinfo,$units,$coursename,$approx,$averages];

     }
    /**
     * @var Invoice $invoice
     */
    public function map($ss): array
    {
        return $ss;
    }
}
