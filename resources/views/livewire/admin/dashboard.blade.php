<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;

new class extends Component
{
    public string $search = '';

    public function classes()
    {
        return SchoolClass::with('therapist')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->get();
    }

    public function deleteClass($classId)
    {
        SchoolClass::findOrFail($classId)->delete();
    }

    public function initials(string $name): string
    {
        return mb_substr($name, 0, 1);
    }
}; ?>

<div class="space-y-6">

    {{-- ═══════════ هدر + دکمه‌های عملیات ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold">کلاس‌ها</h1>
            <p class="mt-1 text-sm text-slate-500">مدیریت کلاس‌ها و اختصاص مربی به هرکدوم</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:flex">
            {{-- کاربر جدید --}}
            <a
                href="{{ route('admin.users.create') }}"
                class="group inline-flex items-center justify-center gap-2 rounded-xl border border-[#eee4d9] bg-[#fdfbf8] px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-[#d7a92b]/40 hover:shadow-md"
            >
                <x-icon name="plus" class="h-4 w-4 shrink-0 text-[#b98a10]" />
                <span class="truncate">کاربر جدید</span>
            </a>

            {{-- کلاس جدید (طلایی = primary) --}}
            <a
                href="{{ route('admin.classes.create') }}"
                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-bold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
            >
                <x-icon name="academic-cap" class="h-4 w-4 shrink-0" />
                <span class="truncate">کلاس جدید</span>
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
                class="group flex flex-col gap-3 border-b border-[#f0e7dc] px-4 py-4 last:border-b-0 transition hover:bg-[#faf5ec] sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-4 sm:px-5"
            >
                {{-- آواتار مربی + اطلاعات کلاس --}}
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    {{-- آواتار --}}
                    @if ($class->therapist)
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-sm font-extrabold text-[#0b1826]">
                            {{ $this->initials($class->therapist->name) }}
                        </div>
                    @else
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#fdeaea] text-[#dc2626]">
                            <x-icon name="academic-cap" class="h-5 w-5" />
                        </div>
                    @endif

                    {{-- متن --}}
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-bold">{{ $class->name }}</div>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="inline-block rounded-md bg-[#faf0d2] px-2 py-0.5 text-xs font-medium text-[#b98a10]">
                                {{ $class->subject }}
                            </span>
                            @if ($class->therapist)
                                <span class="text-xs text-slate-500">
                                    مربی: {{ $class->therapist->name }}
                                </span>
                            @else
                                <span class="text-xs font-medium text-red-500">بدون مربی</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex shrink-0 items-center gap-1">
                    {{-- دانش‌آموزها --}}
                    <a
                        href="{{ route('admin.classes.students', $class->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-[#f0e7dc] sm:px-3"
                    >
                        <x-icon name="users" class="h-4 w-4 shrink-0" />
                        <span class="hidden sm:inline">دانش‌آموزها</span>
                    </a>

                    {{-- حذف --}}
                    <button
                        wire:click="deleteClass({{ $class->id }})"
                        wire:confirm="مطمئنی می‌خوای این کلاس رو حذف کنی؟"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 sm:px-3"
                    >
                        <x-icon name="trash" class="h-4 w-4 shrink-0" />
                        <span class="hidden sm:inline">حذف</span>
                    </button>
                </div>
            </div>
        @empty
            {{-- حالت خالی --}}
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
                        <p class="font-bold">هنوز کلاسی ساخته نشده</p>
                        <p class="mt-1 text-sm text-slate-500">اولین کلاس رو بساز و شروع کن.</p>
                    </div>
                    <a
                        href="{{ route('admin.classes.create') }}"
                        class="mt-2 inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-bold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <x-icon name="academic-cap" class="h-4 w-4" />
                        ساخت اولین کلاس
                    </a>
                @endif
            </div>
        @endforelse

    </div>
</div>