<?php

use Livewire\Volt\Component;
use App\Models\Exam;

new class extends Component
{
    public function exams()
    {
        $classIds = auth()->user()->classesAsStudent->pluck('id');

        return Exam::whereIn('class_id', $classIds)
            ->with(['schoolClass', 'results' => function ($q) {
                $q->where('student_id', auth()->id());
            }])
            ->orderByRaw('scheduled_at IS NULL, scheduled_at asc')
            ->get();
    }
}; ?>

<div class="space-y-4">
    <h1 class="text-xl font-extrabold text-[#061C32]">آزمون‌های من</h1>

    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8]">
        @forelse ($this->exams() as $exam)
            @php $result = $exam->results->first(); @endphp
            <a href="{{ route('student.classes.show', $exam->class_id) }}"
               class="flex items-center justify-between gap-3 border-b border-[#f0e7dc] px-5 py-4 last:border-b-0 hover:bg-[#faf5ec]">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-[#061C32]">{{ $exam->title }}</div>
                    <p class="mt-0.5 text-xs text-slate-400">
                        {{ $exam->schoolClass->name }}
                        @if($exam->scheduled_at)
                            — زمان: {{ $exam->scheduled_at->format('Y/m/d H:i') }}
                        @endif
                    </p>
                </div>

                @if ($result)
                    <span class="shrink-0 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700">
                        نمره: {{ $result->score }}
                    </span>
                @else
                    <span class="shrink-0 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">
                        شرکت نکرده‌ای
                    </span>
                @endif
            </a>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">آزمونی برایت ثبت نشده.</p>
            </div>
        @endforelse
    </div>
</div>