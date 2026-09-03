<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonFileController extends Controller
{
    public function show(Lesson $lesson): StreamedResponse
    {
        $user = auth()->user();
        $class = $lesson->schoolClass;

        $hasAccess = $user->role === 'admin'
            || ($user->role === 'therapist' && $class->therapist_id === $user->id)
            || ($user->role === 'student' && $class->students()->where('student_id', $user->id)->exists());

        abort_unless($hasAccess, 403, 'دسترسی غیرمجاز');
        abort_unless(Storage::disk('local')->exists($lesson->content_url), 404);

        return Storage::disk('local')->response($lesson->content_url, null, [
            'Content-Disposition' => 'inline; filename="' . basename($lesson->content_url) . '"',
        ]);
    }
}