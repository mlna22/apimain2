<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_ar',
        'name_en',
        'level',
        'year',
        'avg1',
        'avg2',
        'avg3',
        'avg4',
        'avg5',
        'avg6',
        'avg7',
        'avg8',
        'avg10',
        'note',
        'isEnded',
    ];

    public function courseCarry()
    {
        return $this->hasOne(Course::class, 'carries');
    }

    public function degrees()
    {
        return $this->hasMany(Degree::class);
    }
}