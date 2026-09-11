<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_result_id', 'exam_question_id', 'answer_text', 'answer_file',
        'is_correct', 'points_awarded',
    ];

    protected $casts = ['is_correct' => 'boolean'];

    public function examResult()
    {
        return $this->belongsTo(ExamResult::class);
    }

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
}
