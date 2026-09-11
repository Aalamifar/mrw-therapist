<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id', 'type', 'question', 'options', 'correct_answer', 'points', 'file_path',
    ];

    protected $casts = ['options' => 'array'];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
