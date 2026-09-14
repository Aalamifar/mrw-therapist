<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title', 'description', 'price', 'image'];

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class);
    }

    public function orders()
    {
        return $this->hasMany(CourseOrder::class);
    }
}