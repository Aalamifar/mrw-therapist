<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['class_id', 'title', 'type', 'content_url', 'description'];
    
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'lesson_id');
    }
}
