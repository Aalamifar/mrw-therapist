<?php

use Livewire\Volt\Component;

new class extends Component
{
    public function classes()
    {
        return auth()->user()->classesAsStudent()
            ->with('therapist')
            ->withCount(['lessons', 'assignments', 'exams'])
            ->get();
    }

    /** رنگ آواتار — ثابت و هماهنگ با پالت طرح */
    public function avatarTone($id): string
    {
        $tones = [
            'bg-[#f3e8fa] text-[#9333ea]',
            'bg-[#dff4f0] text-[#0d9488]',
            'bg-[#fdeaea] text-[#dc2626]',
            'bg-[#e2f6e7] text-[#16a34a]',
            'bg-[#e8eef7] text-[#33507a]',
            'bg-[#fae4fb] text-[#c026d3]',
        ];

        return $tones[abs(crc32((string) $id)) % count($tones)];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="space-y-6">

    {{-- ═══════════ هدر ═══════════ --}}
    <div class="flex items-center gap-3">
        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
            <x-icon name="academic-cap" class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-xl font-extrabold">کلاس‌های من</h1>
            <p class="text-xs text-slate-500">
                {{ $this->fa($this->classes()->count()) }} کلاس فعال
            </p>
        </div>
    </div>

    {{-- ═══════════ کارت‌های کلاس ═══════════ --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->classes() as $class)
            <a
                href="{{ route('student.classes.show', $class->id) }}"
                class="group flex flex-col gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#d7a92b]/40 hover:shadow-md"
            >
                {{-- آواتار + فلش ورود --}}
                <div class="flex items-start justify-between">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10] transition group-hover:scale-105">
                        <x-icon name="academic-cap" class="h-6 w-6" />
                    </span>
                    <x-icon name="arrow-left" class="h-4 w-4 text-slate-300 transition group-hover:text-[#b98a10]" />
                </div>

                {{-- عنوان + موضوع --}}
                <div>
                    <h3 class="font-extrabold transition group-hover:text-[#b98a10]">{{ $class->name }}</h3>
                    <span class="mt-2 inline-block rounded-md bg-[#faf0d2] px-2 py-0.5 text-xs font-bold text-[#b98a10]">
                        {{ $class->subject }}
                    </span>
                </div>

                {{-- آمار محتوا --}}
                <div class="flex flex-wrap gap-1.5 text-[10px]">
                    <span class="flex items-center gap-1 rounded-md bg-[#fdeaea] px-2 py-1 font-bold text-[#dc2626]">
                        <x-icon name="video" class="h-3 w-3" />
                        {{ $this->fa($class->lessons_count) }} درس
                    </span>
                    <span class="flex items-center gap-1 rounded-md bg-[#dff4f0] px-2 py-1 font-bold text-[#0d9488]">
                        <x-icon name="clipboard-list" class="h-3 w-3" />
                        {{ $this->fa($class->assignments_count) }} تکلیف
                    </span>
                    <span class="flex items-center gap-1 rounded-md bg-[#e8eef7] px-2 py-1 font-bold text-[#33507a]">
                        <x-icon name="exam" class="h-3 w-3" />
                        {{ $this->fa($class->exams_count) }} آزمون
                    </span>
                </div>

                {{-- مربی --}}
                @if ($class->therapist)
                    <div class="mt-auto flex items-center gap-2 border-t border-[#f0e7dc] pt-3">
                        <span class="grid h-7 w-7 place-items-center rounded-full text-xs font-extrabold {{ $this->avatarTone($class->therapist->id) }}">
                            {{ mb_substr($class->therapist->name, 0, 1) }}
                        </span>
                        <span class="truncate text-xs text-slate-500">
                            مربی: <b class="font-bold text-slate-700">{{ $class->therapist->name }}</b>
                        </span>
                    </div>
                @endif
            </a>
        @empty
            {{-- حالت خالی --}}
            <div class="col-span-full flex flex-col items-center gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] px-5 py-16 text-center shadow-sm">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                    <x-icon name="academic-cap" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز در هیچ کلاسی ثبت‌نام نشده‌ای</p>
                    <p class="mt-1 text-sm text-slate-500">
                        برای اضافه شدن به کلاس، با ادمین یا مربی خودت تماس بگیر.
                    </p>
                </div>
                <a
                    href="{{ route('student.dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="arrow-right" class="h-4 w-4" />
                    بازگشت به داشبورد
                </a>
            </div>
        @endforelse
    </div>

</div>