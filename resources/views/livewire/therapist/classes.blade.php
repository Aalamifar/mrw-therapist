<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;

new class extends Component
{
    public string $search = '';
    public ?int $selectedClassId = null;

    public function classes()
    {
        return SchoolClass::where('therapist_id', auth()->id())
            ->withCount('students')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->get();
    }

    public function showStudents(int $classId)
    {
        $this->selectedClassId = $classId;
    }

    public function closeStudents()
    {
        $this->selectedClassId = null;
    }

    public function selectedClass()
    {
        if (! $this->selectedClassId) {
            return null;
        }

        return SchoolClass::with('students')->find($this->selectedClassId);
    }

    /** رنگ آواتار دانش‌آموز — ثابت و هماهنگ با پالت طرح */
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
}; ?>

<div class="space-y-6">

    {{-- ═══════════ هدر ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold">کلاس‌های من</h1>
            <p class="mt-1 text-sm text-slate-500">مدیریت کلاس‌های اختصاص یافته به شما</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:flex">
            <a
                href="{{ route('therapist.dashboard') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#eee4d9] bg-[#fdfbf8] px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-[#d7a92b]/40 hover:shadow-md"
            >
                <x-icon name="arrow-right" class="h-4 w-4 shrink-0 text-[#b98a10]" />
                <span class="truncate">بازگشت</span>
            </a>
            <a
                href="{{ route('therapist.messenger') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
            >
                <x-icon name="chat" class="h-4 w-4 shrink-0" />
                <span class="truncate">ورود به پیام‌رسان</span>
            </a>
        </div>
    </div>

    {{-- ═══════════ جستجو ═══════════ --}}
    <div class="relative w-full sm:max-w-sm">
        <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="جستجو بر اساس نام کلاس یا درس..."
            class="w-full rounded-xl border border-[#eee4d9] bg-[#fdfbf8] py-2.5 pl-3 pr-10 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
        >
    </div>

    {{-- ═══════════ لیست کلاس‌ها ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

        @forelse ($this->classes() as $class)
            <div
                wire:key="class-{{ $class->id }}"
                class="flex flex-col gap-3 border-b border-[#f0e7dc] px-4 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-4 sm:px-5"
            >
                {{-- اطلاعات کلاس + آواتار --}}
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#faf0d2] text-[#b98a10]">
                        <x-icon name="academic-cap" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-bold">{{ $class->name }}</div>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-[#faf0d2] px-2 py-0.5 text-xs font-bold text-[#b98a10]">
                                {{ $class->subject }}
                            </span>
                            <span class="text-xs text-slate-500">
                                {{ $class->students_count }} دانش‌آموز
                            </span>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex shrink-0 items-center gap-1">
                    <button
                        wire:click="showStudents({{ $class->id }})"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-[#f0e7dc] sm:px-3"
                    >
                        <x-icon name="users" class="h-4 w-4 shrink-0" />
                        <span class="hidden sm:inline">دانش‌آموزها</span>
                    </button>
                    <a
                        href="{{ route('therapist.classes.show', $class->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-3 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840] sm:px-3"
                    >
                        <x-icon name="arrow-left" class="h-4 w-4 shrink-0" />
                        <span class="hidden sm:inline">ورود</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                    <x-icon name="inbox" class="h-8 w-8" />
                </span>

                @if ($search)
                    <div>
                        <p class="font-bold">نتیجه‌ای پیدا نشد</p>
                        <p class="mt-1 text-sm text-slate-500">کلاسی با این مشخصات وجود ندارد.</p>
                    </div>
                @else
                    <div>
                        <p class="font-bold">هنوز کلاسی به شما اختصاص داده نشده</p>
                        <p class="mt-1 text-sm text-slate-500">برای ساخت یا اختصاص کلاس جدید، با ادمین تماس بگیرید.</p>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ═══════════ Modal دانش‌آموزان ═══════════ --}}
    @if ($selectedClassId)
        @php
            $class = $this->selectedClass();
            $students = $class?->students ?? collect();
        @endphp

        <div
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 backdrop-blur-sm sm:items-center sm:p-4"
            wire:click.self="closeStudents"
            wire:keydown.escape.window="closeStudents"
        >
            <div class="w-full max-w-2xl overflow-hidden rounded-t-3xl bg-[#fdfbf8] shadow-2xl sm:rounded-2xl">

                {{-- Modal Header --}}
                <div class="flex items-center gap-4 border-b border-[#f0e7dc] px-6 py-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                        <x-icon name="academic-cap" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-lg font-extrabold">
                            {{ $class?->name }}
                        </h3>
                        <p class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span class="rounded-md bg-[#faf0d2] px-2 py-0.5 font-bold text-[#b98a10]">{{ $class?->subject }}</span>
                            <span>{{ $students->count() }} دانش‌آموز</span>
                        </p>
                    </div>
                    <button
                        wire:click="closeStudents"
                        class="rounded-lg p-2 text-slate-400 transition hover:bg-[#f0e7dc] hover:text-slate-600"
                    >
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="max-h-[60vh] overflow-y-auto p-6">
                    @if ($students->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($students as $student)
                                <div class="flex items-center gap-3 rounded-xl border border-[#eee4d9] bg-white px-4 py-3 transition hover:border-[#d7a92b]/50 hover:shadow-sm">
                                    {{-- اطلاعات دانش‌آموز --}}
                                    <div class="flex min-w-0 flex-1 items-center gap-3">
                                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $this->avatarTone($student->id) }}">
                                            {{ mb_substr($student->name ?? ($student->first_name ?? 'ک'), 0, 1) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-bold">
                                                {{ $student->name ?? (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: 'نامشخص' }}
                                            </div>
                                            @if (isset($student->email))
                                                <div class="mt-0.5 truncate text-xs text-slate-500">{{ $student->email }}</div>
                                            @elseif (isset($student->phone))
                                                <div class="mt-0.5 truncate text-xs text-slate-500" dir="ltr">{{ $student->phone }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- دکمه ارسال پیام --}}
                                    <a
                                        href="{{ route('therapist.messenger', ['user_id' => $student->id]) }}"
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#f3e8fa] px-3 py-2 text-xs font-bold text-[#9333ea] transition hover:bg-[#e9d5f7]"
                                        title="ارسال پیام به این دانش‌آموز"
                                    >
                                        <x-icon name="chat" class="h-3.5 w-3.5" />
                                        <span class="hidden sm:inline">پیام</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-4 py-10 text-center">
                            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                                <x-icon name="users" class="h-8 w-8" />
                            </span>
                            <div>
                                <p class="font-bold">هنوز دانش‌آموزی ثبت نشده</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    برای این کلاس هنوز دانش‌آموزی اختصاص داده نشده است.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end border-t border-[#f0e7dc] bg-[#faf5ec]/50 px-6 py-4">
                    <button
                        wire:click="closeStudents"
                        class="rounded-xl border border-[#eee4d9] bg-white px-5 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-[#f0e7dc]"
                    >
                        بستن
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
