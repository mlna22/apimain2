<?php

namespace App\Http\Controllers;

use App\Exports\GradExport;
use App\Models\Graduate;
use Illuminate\Http\Request;

class GradController extends Controller
{
    public function showall(){
        return Graduate::select('*')->get();
    }
    public function update(Request $request){
        $request->validate([
            'id' => 'required',
            ]);
            $students = Graduate::select('*')->where('id','=',"$request->id")->first();
            $students->note = $request->note;
            $students->summer_deg = $request->summer_deg;

            $students-> save();
    }

    public function exportgrad(Request $request)
    {
        return (new GradExport($request))->download("graduate.xlsx");
    }
}
