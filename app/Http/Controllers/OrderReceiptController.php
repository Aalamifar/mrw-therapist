<?php

namespace App\Http\Controllers;

use App\Models\CourseOrder;
use Illuminate\Support\Facades\Storage;

class OrderReceiptController extends Controller
{
    public function show(CourseOrder $order)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
        abort_unless(Storage::disk('local')->exists($order->receipt_image), 404);

        return Storage::disk('local')->response($order->receipt_image);
    }
}