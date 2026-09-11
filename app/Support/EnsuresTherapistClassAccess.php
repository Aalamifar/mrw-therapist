<?php

namespace App\Support;

use App\Models\SchoolClass;

trait EnsuresTherapistClassAccess
{
    protected function ownedClass(int|string $classId): SchoolClass
    {
        return SchoolClass::query()
            ->whereKey($classId)
            ->where('therapist_id', auth()->id())
            ->firstOrFail();
    }
}
