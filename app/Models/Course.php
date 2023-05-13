<?php

namespace App\Models;

use App\Models\Semester;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'semester_id',
        'instructor_id',
        'level',
        'year',
        'name_ar',
        'name_en',
        'code',
        'success',
        'unit'
    ];
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
    public function studentsCarry()
    {
        return $this->belongsToMany(Student::class, 'carries');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function degrees()
    {
        return $this->hasMany(Degree::class);
    }
}