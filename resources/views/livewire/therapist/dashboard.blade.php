<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;
use App\Models\Message;

new class extends Component
{
    public int $classesCount = 0;
    public int $studentsCount = 0;
    public int $unreadMessages = 0;

    public function mount()
    {
        $therapistId = auth()->id();

        // 1. شمارش کلاس‌ها
        $this->classesCount = SchoolClass::where('therapist_id', $therapistId)->count();

        // 2. شمارش دانش‌آموزان
        $this->studentsCount = SchoolClass::where('therapist_id', $therapistId)
            ->withCount('students')
            ->get()
            ->sum('students_count');

        // 3. شمارش واقعی پیام‌های خوانده‌نشده
        $this->unreadMessages = Message::whereHas('conversation.users', function ($q) use ($therapistId) {
                $q->where('users.id', $therapistId);
            })
            ->where('sender_id', '!=', $therapistId)
            ->whereNull('read_at')
            ->count();
    }

    /** کلاس‌های اخیر (متد، نه property — تا بین درخواست‌ها serialize نشه) */
    public function recentClasses()
    {
        return SchoolClass::where('therapist_id', auth()->id())
            ->latest()
            ->take(4)
            ->get();
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="space-y-8">

        <section class="space-y-3">
        <h1 class="flex flex-wrap items-center justify-start gap-3 text-xl font-extrabold lg:justify-center lg:text-3xl">
            <span>سلام {{ auth()->user()->name }}، امروز هم یک قدم دیگر جلو میریم</span>
            <x-icon name="sparkles" class="h-7 w-7 shrink-0 text-[#d7a92b] lg:h-9 lg:w-9" />
        </h1>
        <p class="text-sm text-slate-500 lg:text-center">
            {{ $this->fa($classesCount) }} کلاس فعال با {{ $this->fa($studentsCount) }} دانش‌آموز داری
            @if($unreadMessages > 0)
                و {{ $this->fa($unreadMessages) }} پیام در انتظار پاسخ است.
            @else
                و هیچ پیام بی‌پاسخی نداری.
            @endif
        </p>
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-3">

                <section class="space-y-5 xl:col-span-2">

                        <div class="grid gap-5 sm:grid-cols-3">

                                <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                        <x-icon name="academic-cap" class="h-6 w-6" />
                    </span>
                    <div class="mt-4 text-2xl font-extrabold">{{ $this->fa($classesCount) }}</div>
                    <div class="mt-1 text-xs text-slate-500">کلاس فعال</div>
                </div>

                                <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#dff4f0] text-[#0d9488]">
                        <x-icon name="users" class="h-6 w-6" />
                    </span>
                    <div class="mt-4 text-2xl font-extrabold">{{ $this->fa($studentsCount) }}</div>
                    <div class="mt-1 text-xs text-slate-500">دانش‌آموز</div>
                </div>

                                <div class="relative overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm">
                    @if($unreadMessages > 0)
                        <span class="absolute left-0 top-0 h-full w-1 bg-[#d7a92b]"></span>
                    @endif
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e2f6e7] text-[#16a34a]">
                        <x-icon name="chat" class="h-6 w-6" />
                    </span>
                    <div class="mt-4 text-2xl font-extrabold">{{ $this->fa($unreadMessages) }}</div>
                    <div class="mt-1 text-xs text-slate-500">پیام جدید</div>
                </div>
            </div>

                        <div class="grid gap-5 sm:grid-cols-2">

                                <a href="{{ route('therapist.classes') }}" class="group flex flex-col gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#0b1826] text-[#e8c465] transition group-hover:scale-105">
                        <x-icon name="book" class="h-7 w-7" />
                    </span>
                    <span>
                        <h3 class="font-bold">مدیریت کلاس‌ها</h3>
                        <p class="mt-1 text-xs text-slate-500">مشاهده لیست و ورود به کلاس</p>
                    </span>
                </a>

                                <a href="{{ route('therapist.messenger') }}" class="group relative flex flex-col gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    @if($unreadMessages > 0)
                        <span class="absolute left-4 top-4 flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#d7a92b] opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#d7a92b]"></span>
                        </span>
                    @endif
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#f3e8fa] text-[#9333ea] transition group-hover:scale-105">
                        <x-icon name="chat" class="h-7 w-7" />
                    </span>
                    <span>
                        <h3 class="font-bold">پیام‌رسان</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            @if($unreadMessages > 0)
                                <span class="font-bold text-[#b98a10]">{{ $this->fa($unreadMessages) }} پیام خوانده‌نشده</span>
                            @else
                                پاسخ به پیام‌های دانش‌آموزان
                            @endif
                        </p>
                    </span>
                </a>
            </div>
        </section>

                <aside class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
            <header class="flex items-center justify-between border-b border-[#f0e7dc] px-5 py-4">
                <h2 class="font-bold">کلاس‌های اخیر شما</h2>
                <a href="{{ route('therapist.classes') }}" class="flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
                    همه
                    <x-icon name="arrow-left" class="h-4 w-4" />
                </a>
            </header>

            @php $recent = $this->recentClasses(); @endphp

            @if ($recent->count())
                <ul class="divide-y divide-[#f0e7dc]">
                    @foreach ($recent as $class)
                        <li class="flex items-center gap-4 px-5 py-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[#f3e8fa] text-[#9333ea]">
                                <x-icon name="academic-cap" class="h-5 w-5" />
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-bold">{{ $class->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $class->subject }}</p>
                            </div>
                            <a href="{{ route('therapist.classes.show', $class->id) }}"
                               class="rounded-full bg-[#0b1826] px-4 py-1.5 text-xs font-bold text-[#e8c465] transition hover:bg-[#132840]">
                                ورود
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-5 py-10 text-center">
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                        <x-icon name="academic-cap" class="h-7 w-7" />
                    </span>
                    <p class="mt-4 text-sm text-slate-500">هنوز کلاسی نساخته‌ای.</p>
                    <a href="{{ route('therapist.classes') }}" class="mt-2 inline-block font-bold text-[#b98a10] hover:underline">
                        ساخت اولین کلاس
                    </a>
                </div>
            @endif
        </aside>
    </div>
</div>