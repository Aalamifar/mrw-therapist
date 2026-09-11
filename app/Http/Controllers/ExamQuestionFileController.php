<?php

namespace App\Http\Controllers;

use App\Models\ExamQuestion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamQuestionFileController extends Controller
{
    public function show(ExamQuestion $examQuestion): StreamedResponse
    {
        $class = $examQuestion->exam->schoolClass;
        $user = auth()->user();
        $hasAccess = $user->role === 'admin'
            || ($user->role === 'therapist' && $class->therapist_id === $user->id)
            || ($user->role === 'student' && $class->students()->whereKey($user->id)->exists());

        abort_unless($hasAccess && $examQuestion->file_path, 403, 'دسترسی غیرمجاز');
        abort_unless(Storage::disk('local')->exists($examQuestion->file_path), 404);

        return Storage::disk('local')->response($examQuestion->file_path);
    }
}
