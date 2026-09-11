<?php

namespace App\Http\Controllers;

use App\Models\ExamAnswer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamAnswerFileController extends Controller
{
    public function show(ExamAnswer $examAnswer): StreamedResponse
    {
        $user = auth()->user();
        $result = $examAnswer->examResult;
        $class = $result->exam->schoolClass;
        $hasAccess = $user->role === 'admin'
            || $result->student_id === $user->id
            || ($user->role === 'therapist' && $class->therapist_id === $user->id);

        abort_unless($hasAccess && $examAnswer->answer_file, 403, 'دسترسی غیرمجاز');
        abort_unless(Storage::disk('local')->exists($examAnswer->answer_file), 404);

        return Storage::disk('local')->response($examAnswer->answer_file);
    }
}
