<?php

use Illuminate\Http\Request;
use Livewire\Volt\Volt;
use App\Http\Controllers\LessonFileController;
use App\Http\Controllers\LessonGameController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\ExamQuestionFileController;
use App\Http\Controllers\SubmissionFileController;
use App\Http\Controllers\ExamAnswerFileController;

Route::middleware('guest')->group(function () {
    Volt::route('/', 'auth.login')->name('login');
});

Route::middleware('auth')->post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/files/lesson/{lesson}', [LessonFileController::class, 'show'])
        ->name('lesson.file');

    Route::get('/games/lesson/{lesson}/{path?}', [LessonGameController::class, 'show'])
        ->where('path', '.*')
        ->name('lesson.game');

    Route::get('/messages/attachment/{message}', [MessageAttachmentController::class, 'download'])
        ->name('message.attachment');

    Route::get('/files/exam-question/{examQuestion}', [ExamQuestionFileController::class, 'show'])
        ->name('exam-question.file');

    Route::get('/files/submission/{submission}', [SubmissionFileController::class, 'show'])
        ->name('submission.file');

    Route::get('/files/exam-answer/{examAnswer}', [ExamAnswerFileController::class, 'show'])
        ->name('exam-answer.file');

});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Volt::route('/admin/users/create', 'admin.create-user')->name('admin.users.create');
    Volt::route('/admin/classes/create', 'admin.create-class')->name('admin.classes.create');
    Volt::route('/admin/classes/{classId}/students', 'admin.manage-class-students')->name('admin.classes.students');
    Volt::route('/admin/users', 'admin.manage-users')->name('admin.users.index');
});

Route::middleware(['auth', 'role:therapist'])->group(function () {
    Volt::route('/therapist/dashboard', 'therapist.dashboard')->name('therapist.dashboard');
    Volt::route('/therapist/classes', 'therapist.classes')->name('therapist.classes');
    Volt::route('/therapist/classes/{classId}', 'therapist.class-show')->name('therapist.classes.show');
    Volt::route('/therapist/messenger', 'messenger')->name('therapist.messenger');
    Volt::route('/therapist/exams/{exam}/questions', 'therapist.exam-questions')->name('therapist.exams.questions');
    Volt::route('/therapist/assignments/{assignmentId}/submissions', 'therapist.assignment-submissions')->name('therapist.assignments.submissions');
    Volt::route('/therapist/exams/{examId}/results', 'therapist.exam-results')->name('therapist.exams.results');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Volt::route('/student/dashboard', 'student.dashboard')->name('student.dashboard');
    Volt::route('/student/classes', 'student.classes-index')->name('student.classes.index');
    Volt::route('/student/classes/{classId}', 'student.class-show')->name('student.classes.show');
    Volt::route('/student/messenger', 'messenger')->name('student.messenger');
    Volt::route('/student/calendar', 'student.calendar')->name('student.calendar');
    Volt::route('/student/assignments', 'student.all-assignments')->name('student.assignments.index');
    Volt::route('/student/exams', 'student.all-exams')->name('student.exams.index');
});