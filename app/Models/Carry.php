<?php

namespace App\Models;
use App\Models\Student;
use App\Models\Course;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carry extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'attend_carry'
    ];
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
