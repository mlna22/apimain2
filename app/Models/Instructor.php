<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{

    use HasFactory;
    protected $fillable = [
        'name_ar',
        'name_en',
    ];
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}