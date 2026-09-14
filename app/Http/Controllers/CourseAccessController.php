<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseAccessController extends Controller
{
    /** فرم وارد کردن کد */
    public function showForm()
    {
        return view('course-access');
    }

    /** بررسی کد و ورود به دوره */
    public function checkCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $order = CourseOrder::where('code', strtoupper($request->code))->first();

        if (!$order) {
            return back()->withErrors(['code' => 'کد وارد شده معتبر نیست.']);
        }

        if ($order->status !== 'approved') {
            return back()->withErrors(['code' => 'پرداخت شما هنوز تأیید نشده است.']);
        }

        // ذخیره‌ی دسترسی توی سشن
        session()->put('course_access_' . $order->course_id, true);

        return redirect()->route('course.view', $order->course_id);
    }

    /** نمایش صفحه‌ی دوره (فقط اگه دسترسی داشته باشه) */
    public function view(Course $course)
    {
        abort_unless(session('course_access_' . $course->id), 403, 'برای دسترسی به این دوره، ابتدا کد خود را وارد کنید.');

        $lessons = $course->lessons;

        return view('course-view', compact('course', 'lessons'));
    }

    /** سرو کردن فایل محتوای دوره (جزوه/بازی) */
    public function file(Course $course, $lessonId, string $path = '')
    {
        abort_unless(session('course_access_' . $course->id), 403);

        $lesson = $course->lessons()->findOrFail($lessonId);

        if ($lesson->type === 'note') {
            abort_unless(Storage::disk('local')->exists($lesson->content_url), 404);

            return Storage::disk('local')->response($lesson->content_url, null, [
                'Content-Disposition' => 'inline; filename="' . basename($lesson->content_url) . '"',
            ]);
        }

        // بازی HTML — سرو کردن فایل از پوشه‌ی استخراج‌شده
        $baseDir = Storage::disk('local')->path($lesson->content_url);
        $requestedPath = realpath($baseDir . '/' . ($path ?: 'index.html'));

        abort_unless($requestedPath && str_starts_with($requestedPath, realpath($baseDir)), 403);
        abort_unless(file_exists($requestedPath), 404);

        $mimeType = mime_content_type($requestedPath) ?: 'application/octet-stream';

        return response()->stream(function () use ($requestedPath) {
            readfile($requestedPath);
        }, 200, ['Content-Type' => $mimeType]);
    }
}