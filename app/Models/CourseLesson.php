<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    protected $fillable = ['course_id', 'title', 'type', 'content_url', 'description'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}