<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Degreeh extends Model
{
    use HasFactory;
    protected $fillable = [
        'amt',
        'source',
    ];
    public function degrees()
    {
        return $this->hasMany(Degree::class);
    }
    public function helps()
    {
        return $this->belongsToMany(Helps::class);
    }
}
