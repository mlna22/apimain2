<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DegreeController;
use App\Http\Controllers\GradController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/students', [StudentController::class, 'showAll']);
Route::post('/students/grad', [StudentController::class, 'grad']);
Route::post('/students/attend',[StudentController::class,'attendency']);
Route::post('/students/create', [StudentController::class, 'create']);
Route::post('/students/destroy/{id}', [StudentController::class, 'destroy']);
Route::post('/students/remove/{id}', [StudentController::class, 'remove']);

Route::post('/students/update',[StudentController::class,'update']);
Route::get('/students/getCurAvg',[StudentController::class,'getCurAvg']);


Route::get('/instructors', [InstructorController::class, 'showAll']);
Route::post('/instructors/create', [InstructorController::class, 'create']);
Route::post('/instructors/destroy/{id}', [InstructorController::class, 'destroy']);
Route::post('/instructors/update', [InstructorController::class, 'update']);

Route::get('degrees/excourse/', [DegreeController::class, 'exportcourse']);


Route::post('/courses/create', [CourseController::class, 'create']);
Route::get('/courses/level', [CourseController::class, 'showLevel']);
Route::get('/courses', [CourseController::class, 'showCurrent']);
Route::get('/courses/all', [CourseController::class, 'showAll']);
Route::post('/courses/update', [CourseController::class, 'update']);
Route::post('/courses/destroy/{id}', [CourseController::class, 'destroy']);

Route::get('/grads/show', [GradController::class, 'showall']);
Route::post('/grads/update',[GradController::class,'update']);
Route::get('grad/export', [GradController::class, 'exportgrad']);

Route::post('/degrees/createhelp', [HelpController::class, 'createhelp']);
Route::get('/degrees/showhelp', [HelpController::class, 'showhelp']);
Route::post('/degrees/addhelp', [HelpController::class, 'addhelp']);
Route::get('/degrees/helpstu', [HelpController::class, 'helpstu']);


Route::get('/degrees', [DegreeController::class, 'getDegrees']);
Route::get('/degrees/fourty', [DegreeController::class, 'getForty']);
Route::post('/degrees/cacl', [DegreeController::class, 'countDegree']);
Route::post('/degrees/cacl1', [DegreeController::class, 'countfirst']);
Route::post('/degrees/student', [DegreeController::class, 'getStudentDegrees']);
Route::post('/degrees/create', [DegreeController::class, 'createStudentDegrees']);
Route::get('/degrees/getall', [DegreeController::class, 'getAllDegrees']);
Route::get('/degrees/getyear', [DegreeController::class, 'getYearDegrees']);
Route::post('/degrees/grad', [DegreeController::class, 'grads']);
Route::post('/degrees/pass', [DegreeController::class, 'pass']);
Route::get('/degrees/test/{course_id}', [DegreeController::class, 'get']);


Route::post('/semesters/end', [SemesterController::class, 'end']);
Route::post('/semesters/create', [SemesterController::class, 'create']);
Route::get('/semesters/get', [SemesterController::class, 'show']);

Route::post('users/login', [UserController::class, 'login']);
Route::post('users/create', [UserController::class, 'create']);
Route::get('users/get', [UserController::class, 'get']);
Route::post('/users/update', [UserController::class, 'update']);
Route::post('/users/destroy/{id}', [UserController::class, 'destroy']);

Route::get('/homepage', [HomeController::class, 'counts']);