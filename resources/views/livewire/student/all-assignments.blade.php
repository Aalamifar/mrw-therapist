<?php

use Livewire\Volt\Component;
use App\Models\Assignment;

new class extends Component
{
    public function assignments()
    {
        $classIds = auth()->user()->classesAsStudent->pluck('id');

        return Assignment::whereIn('class_id', $classIds)
            ->where(function ($q) {
                $q->whereNull('visible_at')->orWhere('visible_at', '<=', now());
            })
            ->with(['schoolClass', 'submissions' => function ($q) {
                $q->where('student_id', auth()->id());
            }])
            ->orderByRaw('due_date IS NULL, due_date asc')
            ->get();
    }
}; ?>

<div class="space-y-4">
    <h1 class="text-xl font-extrabold text-[#061C32]">تکالیف من</h1>

    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8]">
        @forelse ($this->assignments() as $assignment)
            @php $sub = $assignment->submissions->first(); @endphp
            <a href="{{ route('student.classes.show', $assignment->class_id) }}"
               class="flex items-center justify-between gap-3 border-b border-[#f0e7dc] px-5 py-4 last:border-b-0 hover:bg-[#faf5ec]">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-[#061C32]">{{ $assignment->title }}</div>
                    <p class="mt-0.5 text-xs text-slate-400">
                        {{ $assignment->schoolClass->name }}
                        @if($assignment->due_date)
                            — مهلت: {{ $assignment->due_date->format('Y/m/d H:i') }}
                        @endif
                    </p>
                </div>

                @if ($sub)
                    <span class="shrink-0 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700">
                        {{ $sub->grade !== null ? 'نمره: ' . $sub->grade : 'ارسال شد' }}
                    </span>
                @else
                    <span class="shrink-0 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600">
                        در انتظار پاسخ
                    </span>
                @endif
            </a>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">تکلیفی برایت ثبت نشده.</p>
            </div>
        @endforelse
    </div>
</div>