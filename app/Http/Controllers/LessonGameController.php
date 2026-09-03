<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonGameController extends Controller
{
    public function show(Lesson $lesson, string $path = 'index.html'): StreamedResponse
    {
        $user = auth()->user();
        $class = $lesson->schoolClass;

        $hasAccess = $user->role === 'admin'
            || ($user->role === 'therapist' && $class->therapist_id === $user->id)
            || ($user->role === 'student' && $class->students()->where('student_id', $user->id)->exists());

        abort_unless($hasAccess, 403, 'دسترسی غیرمجاز');

        // جلوگیری از Path Traversal
        $baseDir = Storage::disk('local')->path($lesson->content_url);
        $requestedPath = realpath($baseDir . '/' . $path);

        abort_unless($requestedPath && str_starts_with($requestedPath, realpath($baseDir)), 403, 'مسیر غیرمجاز');
        abort_unless(file_exists($requestedPath), 404);

        $mimeType = mime_content_type($requestedPath) ?: 'application/octet-stream';

        return response()->stream(function () use ($requestedPath) {
            readfile($requestedPath);
        }, 200, ['Content-Type' => $mimeType]);
    }
}