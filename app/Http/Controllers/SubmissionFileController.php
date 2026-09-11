<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    public function show(AssignmentSubmission $submission): StreamedResponse
    {
        $user = auth()->user();
        $class = $submission->assignment->schoolClass;
        $hasAccess = $user->role === 'admin'
            || $submission->student_id === $user->id
            || ($user->role === 'therapist' && $class->therapist_id === $user->id);

        abort_unless($hasAccess && $submission->answer_file, 403, 'دسترسی غیرمجاز');
        abort_unless(Storage::disk('local')->exists($submission->answer_file), 404);

        return Storage::disk('local')->response($submission->answer_file);
    }
}
