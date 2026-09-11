<?php

namespace App\Support;

use App\Models\SchoolClass;

trait EnsuresStudentClassAccess
{
    protected function enrolledClass(int|string $classId): SchoolClass
    {
        return auth()->user()
            ->classesAsStudent()
            ->whereKey($classId)
            ->firstOrFail();
    }
}
