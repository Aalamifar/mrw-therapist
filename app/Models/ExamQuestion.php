<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
