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

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#061C32]">کلاس‌ها</h1>
            <p class="mt-1 text-sm text-slate-500">مدیریت کلاس‌ها و اختصاص مربی به هرکدوم</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:flex">
            <a
                href="{{ route('admin.users.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#061C32]/15 bg-white px-4 py-2.5 text-sm font-medium text-[#061C32] transition hover:bg-[#061C32]/5"
            >
                <x-icon name="plus" class="h-4 w-4 shrink-0" />
                <span class="truncate">کاربر جدید</span>
            </a>
            <a
                href="{{ route('admin.classes.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#E9C72A] px-4 py-2.5 text-sm font-bold text-[#061C32] transition hover:brightness-95"
            >
                <x-icon name="academic-cap" class="h-4 w-4 shrink-0" />
                <span class="truncate">کلاس جدید</span>
            </a>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative w-full sm:max-w-sm">
        <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="جستجو بر اساس نام کلاس یا درس..."
            class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
        >
    </div>

    {{-- List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

        @forelse ($this->classes() as $class)
            <div
                wire:key="class-{{ $class->id }}"
                class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 last:border-b-0 hover:bg-slate-50/70 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-4 sm:px-5"
            >
                {{-- Class + subject --}}
                <div class="min-w-0 flex-1 sm:min-w-[10rem]">
                    <div class="font-bold text-[#061C32] truncate">{{ $class->name }}</div>
                    <span class="mt-1 inline-block rounded-md bg-[#061C32]/5 px-2 py-0.5 text-xs text-[#061C32]/70">
                        {{ $class->subject }}
                    </span>
                </div>

                {{-- Therapist + Actions row (mobile: side by side under class info) --}}
                <div class="flex items-center justify-between gap-3 sm:contents">

                    {{-- Therapist --}}
                    <div class="flex items-center gap-2">
                        @if ($class->therapist)
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E9C72A] text-xs font-bold text-[#061C32]">
                                {{ $this->initials($class->therapist->name) }}
                            </div>
                            <span class="text-sm text-slate-600 truncate">{{ $class->therapist->name }}</span>
                        @else
                            <span class="text-sm text-slate-400">بدون مربی</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex shrink-0 items-center gap-1">
                        <a
                            href="{{ route('admin.classes.students', $class->id) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm text-[#061C32] transition hover:bg-[#061C32]/5 sm:px-3"
                        >
                            <x-icon name="users" class="h-4 w-4 shrink-0" />
                            <span class="hidden sm:inline">دانش‌آموزها</span>
                        </a>
                        <button
                            wire:click="deleteClass({{ $class->id }})"
                            wire:confirm="مطمئنی می‌خوای این کلاس رو حذف کنی؟"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm text-red-600 transition hover:bg-red-50 sm:px-3"
                        >
                            <x-icon name="trash" class="h-4 w-4 shrink-0" />
                            <span class="hidden sm:inline">حذف</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-3 px-5 py-16 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                @if ($search)
                    <p class="text-sm text-slate-500">کلاسی با این مشخصات پیدا نشد.</p>
                @else
                    <p class="text-sm text-slate-500">هنوز کلاسی ساخته نشده.</p>
                    <a href="{{ route('admin.classes.create') }}" class="text-sm font-medium text-[#061C32] underline underline-offset-4">
                        اولین کلاس رو بساز
                    </a>
                @endif
            </div>
        @endforelse

    </div>
</div>