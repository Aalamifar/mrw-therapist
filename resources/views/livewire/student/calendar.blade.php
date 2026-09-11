<?php

use Livewire\Volt\Component;
use App\Models\Assignment;
use App\Models\Exam;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    public int $jy;
    public int $jm;

    public function mount()
    {
        $now = Jalalian::now();
        $this->jy = (int) $now->format('Y');
        $this->jm = (int) $now->format('m');
    }

    public function prevMonth()
    {
        $this->jm--;
        if ($this->jm < 1) { $this->jm = 12; $this->jy--; }
    }

    public function nextMonth()
    {
        $this->jm++;
        if ($this->jm > 12) { $this->jm = 1; $this->jy++; }
    }

    private function classIds(): array
    {
        $user = auth()->user();

        return match ($user->role) {
            'student' => $user->classesAsStudent->pluck('id')->toArray(),
            'therapist' => $user->classesAsTherapist->pluck('id')->toArray(),
            default => [],
        };
    }

    public function monthLabel(): string
    {
        return Jalalian::fromFormat('Y/m/d', sprintf('%04d/%02d/01', $this->jy, $this->jm))->format('F Y');
    }

    public function grid()
    {
        $start = Jalalian::fromFormat('Y/m/d', sprintf('%04d/%02d/01', $this->jy, $this->jm));

        // شمارش تعداد روزهای ماه با پیمایش دستی (مستقل از نسخه‌ی کتابخونه)
        $daysInMonth = 0;
        $cursor = $start;
        while ((int) $cursor->format('m') === $this->jm) {
            $daysInMonth++;
            $cursor = $cursor->addDay();
        }

        $startGregorian = $start->toCarbon()->startOfDay();
        $endGregorianExclusive = $cursor->toCarbon()->startOfDay();

        $classIds = $this->classIds();

        $assignments = Assignment::whereIn('class_id', $classIds)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startGregorian, $endGregorianExclusive])
            ->get();

        $exams = Exam::whereIn('class_id', $classIds)
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$startGregorian, $endGregorianExclusive])
            ->get();

        $eventsByDay = [];

        foreach ($assignments as $a) {
            $day = (int) Jalalian::fromDateTime($a->due_date)->format('j');
            $eventsByDay[$day][] = ['title' => $a->title, 'type' => 'assignment', 'time' => $a->due_date->format('H:i')];
        }

        foreach ($exams as $e) {
            $day = (int) Jalalian::fromDateTime($e->scheduled_at)->format('j');
            $eventsByDay[$day][] = ['title' => $e->title, 'type' => 'exam', 'time' => $e->scheduled_at->format('H:i')];
        }

        // آفست روز اول ماه نسبت به شنبه (شروع هفته‌ی فارسی)
        $carbonDow = $start->toCarbon()->dayOfWeek; // یکشنبه=0 ... شنبه=6
        $offset = ($carbonDow - 6 + 7) % 7;

        $cells = array_fill(0, $offset, null);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cells[] = ['day' => $d, 'events' => $eventsByDay[$d] ?? []];
        }

        return $cells;
    }
}; ?>

<div class="space-y-5">
    <div class="flex items-center justify-between rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-4">
        <button wire:click="prevMonth" class="rounded-lg px-3 py-1.5 text-sm hover:bg-[#f0e7dc]">‹ ماه قبل</button>
        <h2 class="font-extrabold text-[#061C32]">{{ $this->monthLabel() }}</h2>
        <button wire:click="nextMonth" class="rounded-lg px-3 py-1.5 text-sm hover:bg-[#f0e7dc]">ماه بعد ›</button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8]">
        <div class="grid grid-cols-7 border-b border-[#f0e7dc] bg-[#faf5ec] text-center text-xs font-bold text-slate-500">
            @foreach (['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'] as $d)
                <div class="py-2">{{ $d }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach ($this->grid() as $cell)
                <div class="min-h-[90px] border-b border-l border-[#f0e7dc] p-1.5 text-xs">
                    @if ($cell)
                        <div class="mb-1 font-bold text-slate-600">{{ $cell['day'] }}</div>
                        @foreach ($cell['events'] as $event)
                            <div class="mb-1 truncate rounded px-1.5 py-0.5 text-[10px] font-bold
                                {{ $event['type'] === 'exam' ? 'bg-[#fdeaea] text-[#dc2626]' : 'bg-[#f4e4f2] text-[#6b4a6b]' }}"
                                 title="{{ $event['title'] }} - {{ $event['time'] }}">
                                {{ $event['title'] }}
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex gap-4 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#dc2626]"></span> آزمون</span>
        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#6b4a6b]"></span> مهلت تکلیف</span>
    </div>
</div>