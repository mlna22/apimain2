<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Semester;
use Illuminate\Http\Request;

class InstructorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')
            ->only(['destroy', 'create', 'update']);
    }
    public function showAll(Request $request)
    {
        $query = Instructor::select('*');
        if ($request->has('search')) {
            $query->where('name_ar', '=', '%' . $request->input('search') . '%');
        }
        $data = $query->paginate(10);
        return $data;
    }
    
    public function showSelect()
    {
        $data = Instructor::select('name_ar')->get();
        return $data;
    }
    public function create(Request $request)
    {
        $request->validate([
            'name_ar' => 'required',
            'name_en' => 'required',
        ]);
        Instructor::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);
    }
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name_ar' => 'required',
            'name_en' => 'required',
        ]);
        $instructor = Instructor::select('*')->where('id', '=', "$request->id")->first();
        $instructor->name_ar = $request->name_ar;
        $instructor->name_en = $request->name_en;
        $instructor->save();

    }
    public function destroy($id)
    {
        $semester = Semester::select('*')->get()->last();
        $id = $semester->id;
        $inscourse = Course::where("instructor_id", "=", "$id")->where("semester_id", '=', "$id")->get();
        if ($inscourse == null) {
            Instructor::destroy($id);
            return response(200);
        } else {
            return response(409);
        }
    }
    
}
