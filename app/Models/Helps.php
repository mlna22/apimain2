<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Helps extends Model
{
    use HasFactory;
    protected $fillable = [
        'degreeh_id',
        'degree_id',
        'amt',
    ];
    public function degrees()
    {
        return $this->hasOne(Degree::class);
    }
    public function helps()
    {
        return $this->hasMany(Degreeh::class);
    }
}
