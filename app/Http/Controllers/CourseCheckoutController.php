<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseOrder;
use Illuminate\Http\Request;

class CourseCheckoutController extends Controller
{
    public function show(Course $course)
    {
        return view('course-checkout', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'required|string|max:20',
            'receipt' => 'required|image|max:5120',
        ]);

        $receiptPath = $request->file('receipt')->store('receipts', 'local');

        $order = CourseOrder::create([
            'course_id' => $course->id,
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['buyer_phone'],
            'receipt_image' => $receiptPath,
            'code' => CourseOrder::generateUniqueCode(),
            'status' => 'pending',
        ]);

        return view('course-checkout-success', ['order' => $order]);
    }
}