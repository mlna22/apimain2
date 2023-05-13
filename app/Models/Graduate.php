<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graduate extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'level',
        'year',
        'avg1',
        'avg2',
        'avg3',
        'isGrad',
        'avg_final',
        'avg4',
        'avg5',
        'avg6',
        'avg7',
        'avg8',
        'avg10',
        'note',
        'summer_deg',
        'isEnded',
    ];
}
