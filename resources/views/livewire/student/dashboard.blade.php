<?php

use Livewire\Volt\Component;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Message;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    private function classIds(): array
    {
        return auth()->user()->classesAsStudent->pluck('id')->toArray();
    }

    private function pendingAssignmentsCount(): int
    {
        return Assignment::whereIn('class_id', $this->classIds())
            ->where(function ($q) {
                $q->whereNull('visible_at')->orWhere('visible_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('due_date')->orWhere('due_date', '>=', now());
            })
            ->whereDoesntHave('submissions', function ($q) {
                $q->where('student_id', auth()->id());
            })
            ->count();
    }

    private function nextExam(): ?Exam
    {
        return Exam::whereIn('class_id', $this->classIds())
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();
    }

    private function unreadMessagesCount(): int
    {
        return Message::whereHas('conversation.users', function ($q) {
                $q->where('users.id', auth()->id());
            })
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function cards(): array
    {
        $pendingCount = $this->pendingAssignmentsCount();
        $nextExam = $this->nextExam();
        $unreadCount = $this->unreadMessagesCount();

        return [
            [
                'title' => 'کلاس‌های من',
                'desc' => auth()->user()->classesAsStudent->count() . ' کلاس فعال',
                'icon' => 'video',
                'bg' => 'bg-[#f3e8fa]',
                'text' => 'text-[#9333ea]',
                'href' => route('student.classes.index'),
            ],
            [
                'title' => 'آزمون‌ها',
                'desc' => $nextExam ? 'آزمون بعدی: ' . $nextExam->title : 'آزمونی در پیش نیست',
                'icon' => 'exam',
                'bg' => 'bg-[#faf0d2]',
                'text' => 'text-[#b98a10]',
                'href' => route('student.exams.index'),
            ],
            [
                'title' => 'تقویم',
                'desc' => 'مشاهده برنامه هفتگی',
                'icon' => 'calendar',
                'bg' => 'bg-[#fdeaea]',
                'text' => 'text-[#dc2626]',
                'href' => route('student.calendar'),
            ],
            [
                'title' => 'تکالیف',
                'desc' => $pendingCount > 0 ? "{$pendingCount} تکلیف در انتظار ارسال" : 'تکلیفی در انتظار نیست',
                'icon' => 'clipboard-list',
                'bg' => 'bg-[#dff4f0]',
                'text' => 'text-[#0d9488]',
                'href' => route('student.assignments.index'),
            ],
            [
                'title' => 'راهنما و پشتیبانی',
                'desc' => 'سوالی داری؟ همیشه بپرس',
                'icon' => 'sparkles',
                'bg' => 'bg-[#fae4fb]',
                'text' => 'text-[#c026d3]',
                'href' => '#',
            ],
            [
                'title' => 'پیام‌رسان',
                'desc' => $unreadCount > 0 ? "{$unreadCount} پیام خوانده‌نشده" : 'پیامی در انتظار نیست',
                'icon' => 'chat',
                'bg' => 'bg-[#e2f6e7]',
                'text' => 'text-[#16a34a]',
                'href' => route('student.messenger'),
            ],
        ];
    }

    public function schedule(): array
    {
        $classIds = $this->classIds();
        $items = collect();

        Assignment::whereIn('class_id', $classIds)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->each(function ($a) use ($items) {
                $j = Jalalian::fromDateTime($a->due_date);
                $items->push([
                    'day' => $j->format('j'),
                    'month' => $j->format('F'),
                    'title' => 'تحویل تکلیف: ' . $a->title,
                    'time' => 'ساعت ' . $a->due_date->format('H:i'),
                    'color' => 'bg-[#f4e4f2] text-[#6b4a6b]',
                    'sort' => $a->due_date,
                ]);
            });

        Exam::whereIn('class_id', $classIds)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->each(function ($e) use ($items) {
                $j = Jalalian::fromDateTime($e->scheduled_at);
                $items->push([
                    'day' => $j->format('j'),
                    'month' => $j->format('F'),
                    'title' => 'آزمون: ' . $e->title,
                    'time' => 'ساعت ' . $e->scheduled_at->format('H:i'),
                    'color' => 'bg-[#fdeaea] text-[#dc2626]',
                    'sort' => $e->scheduled_at,
                ]);
            });

        return $items->sortBy('sort')->take(5)->values()->all();
    }

    public function greeting(): string
    {
        $name = auth()->user()->name;

        return match (auth()->user()->role) {
            'student' => "سلام {$name}، امروز یک قدم دیگر جلو میریم",
            'therapist' => "خوش اومدی {$name}، دانش‌آموزان امروز منتظرت هستن",
            'admin' => "خوش اومدی {$name}، مدیریت سامانه در دستان شماست",
            default => "خوش اومدی {$name}",
        };
    }
}; ?>

<div class="space-y-8">
    <section class="space-y-3">
        <h1 class="flex flex-wrap items-center justify-start gap-3 text-xl font-extrabold lg:justify-center lg:text-3xl">
            <span>{{ $this->greeting() }}</span>
            <x-icon name="sparkles" class="h-7 w-7 shrink-0 text-[#d7a92b] lg:h-9 lg:w-9" />
        </h1>
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <section class="grid gap-5 sm:grid-cols-2 xl:col-span-2">
            @foreach ($this->cards() as $card)
                <a href="{{ $card['href'] }}" class="group flex flex-col gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl {{ $card['bg'] }} {{ $card['text'] }}">
                        <x-icon :name="$card['icon']" class="h-7 w-7" />
                    </span>
                    <span>
                        <h3 class="font-bold group-hover:text-[#d7a92b]">{{ $card['title'] }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $card['desc'] }}</p>
                    </span>
                </a>
            @endforeach
        </section>

        <aside class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
            <header class="flex items-center justify-between border-b border-[#f0e7dc] px-5 py-4">
                <h2 class="font-bold">برنامه پیش رو</h2>
            </header>
            <ul class="divide-y divide-[#f0e7dc]">
                @forelse ($this->schedule() as $s)
                    <li class="flex items-center gap-4 px-5 py-4">
                        <span class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl {{ $s['color'] }}">
                            <b class="text-sm leading-4">{{ $s['day'] }}</b>
                            <small class="text-[10px]">{{ $s['month'] }}</small>
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-bold">{{ $s['title'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $s['time'] }}</p>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">چیزی در پیش نیست.</li>
                @endforelse
            </ul>
        </aside>
    </div>
</div>