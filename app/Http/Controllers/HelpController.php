<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Degree;
use App\Models\Degreeh;
use App\Models\Helps;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function createhelp(Request $request)
    {$request->validate([
        'source' => 'required',
        'amt' => 'required',
    ]);

        Degreeh::create([
            'source' => $request->source,
            'amt' => $request->amt,
        ]);
    }
    public function showhelp(Request $request)
    {
        return Degreeh::select('*')->get();
    }

    public function helpstu(Request $request)
    {
        $request->validate([
            "deg_id" => "required",
        ]);
        $help = Helps::select('*')->where("degree_id", "=", "$request->deg_id")->get();

        return $help;
    }

    public function addhelp(Request $request)
    {
        $request->validate([
            "source" => "required",
            "deg_id" => "required",
        ]);
        $deg = Degree::select("*")->where("id", "=", "$request->deg_id")->first();
        $course = Course::select("*")->where("id", "=", "$deg->course_id")->first();
        $help = Degreeh::select("*")->where("source", "=", "$request->source")->first();

        $final1 = $deg->final1;
        $final2 = $deg->final2;
        $final3 = $deg->final3;
        $amt = $help->amt;

        if ($final3 != null && $final3 + $amt >= $course->success) {
            $dif = $course->success - $final3;
            $final3 = $dif + $final3;
            $helps = Helps::create([
                'degree_id' => $deg->id,
                "amt" => $dif,

            ]);
            $helps->source = $help->source;
            $helps->save();
            $deg->final3 = $final3;
            $total = $final3 + $deg->fourty;
            $deg->approx = $this->getApprox($total, $course->success);
            $deg->save();
        }
        if ($final2 != null && $final2 + $amt >= $course->success) {
            $dif = $course->success - $final2;
            $final2 = $dif + $final2;
            $helps = Helps::create([
                'degree_id' => $deg->id,
                "amt" => $dif,

            ]);
            $helps->source = $help->source;
            $helps->save();
            $deg->final2 = $final2;
            $total = $final2 + $deg->fourty;
            $deg->approx = $this->getApprox($total, $course->success);
            $deg->save();
        }

        if ($final1 != null && $final1 + $amt >= $course->success) {
            $dif = $course->success - $final1;
            $final1 = $dif + $final1;
            $helps = Helps::create([
                'degree_id' => $deg->id,
                "amt" => $dif,

            ]);
            $helps->source = $help->source;
            $helps->save();
            $deg->final1 = $final1;
            $deg->sts = "pass";
            $total = $final1 + $deg->fourty;
            $deg->approx = $this->getApprox($total, $course->success);
            $deg->save();
        }

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
}
