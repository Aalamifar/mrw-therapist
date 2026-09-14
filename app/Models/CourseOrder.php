<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CourseOrder extends Model
{
    protected $fillable = ['course_id', 'buyer_name', 'buyer_phone', 'receipt_image', 'code', 'status'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}