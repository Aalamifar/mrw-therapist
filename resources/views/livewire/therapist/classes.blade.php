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

        // فرض بر این است که رابطه students در مدل SchoolClass تعریف شده است
        return SchoolClass::with('students')->find($this->selectedClassId);
    }
}; ?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#061C32]">کلاس‌ها</h1>
            <p class="mt-1 text-sm text-slate-500">مدیریت کلاس‌های اختصاص یافته به شما</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:flex">
            <a
                href="{{ route('therapist.dashboard') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#061C32]/15 bg-white px-4 py-2.5 text-sm font-medium text-[#061C32] transition hover:bg-[#061C32]/5"
            >
                <x-icon name="home" class="h-4 w-4 shrink-0" />
                <span class="truncate">برگشت به داشبورد</span>
            </a>
            <a
                href="#"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#E9C72A] px-4 py-2.5 text-sm font-bold text-[#061C32] transition hover:brightness-95"
            >
                <x-icon name="chat" class="h-4 w-4 shrink-0" />
                <span class="truncate">ورود به پیام‌رسان</span>
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

                {{-- Actions row --}}
                <div class="flex items-center justify-between gap-3 sm:contents">
                    <div class="flex shrink-0 items-center gap-1">
                        
                        {{-- دکمه نمایش دانش‌آموزان (تغییر یافته به button) --}}
                        <button
                            wire:click="showStudents({{ $class->id }})"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm text-[#061C32] transition hover:bg-[#061C32]/5 sm:px-3"
                        >
                            <x-icon name="users" class="h-4 w-4 shrink-0" />
                            <span class="hidden sm:inline">دانش‌آموزها</span>
                        </button>

                        <a
                            href="{{ route('therapist.classes.show', $class->id) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm text-[#125ccf] transition hover:bg-[#061C32]/5 sm:px-3"
                        >
                            <x-icon name="inbox" class="h-4 w-4 shrink-0" />
                            <span class="hidden sm:inline">ورود به کلاس</span>
                        </a>
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
                    <p class="text-sm font-medium text-[#061C32] underline underline-offset-4">
                        به ادمین زنگ بزنید
                    </p>
                @endif
            </div>
        @endforelse

    </div>

    {{-- Modal for Students --}}
    @if ($selectedClassId)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" 
            wire:click.self="closeStudents"
        >
            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-[#061C32]">
                        دانش‌آموزان کلاس: {{ $this->selectedClass()?->name }}
                    </h3>
                    <button wire:click="closeStudents" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="max-h-[60vh] overflow-y-auto p-6">
                    @php
                        $students = $this->selectedClass()?->students ?? collect();
                    @endphp
                    
                    @if ($students->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($students as $student)
                                {{-- Modal Body --}}
                                <div class="max-h-[60vh] overflow-y-auto p-6">
                                @php
                                $students = $this->selectedClass()?->students ?? collect();
                                @endphp
                                @if ($students->isNotEmpty())
                                <div class="space-y-3">
                                @foreach ($students as $student)
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 transition hover:bg-slate-100">
                                    {{-- اطلاعات دانش‌آموز --}}
                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E9C72A]/20 text-sm font-bold text-[#061C32]">
                                                {{ mb_substr($student->name ?? ($student->first_name ?? 'ک'), 0, 1) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-medium text-[#061C32] truncate">
                                                    {{ $student->name ?? ($student->first_name . ' ' . $student->last_name) ?? 'نامشخص' }}
                                                </div>
                                                @if(isset($student->email))
                                                    <div class="mt-0.5 text-xs text-slate-500 truncate">{{ $student->email }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- 👈 دکمه جدید ارسال پیام --}}
                                        <a
                                            href="{{ route('therapist.messenger', ['student_id' => $student->id]) }}"
                                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-[#E9C72A]/10 px-3 py-2 text-sm font-medium text-[#061C32] transition hover:bg-[#E9C72A]/20"
                                            title="ارسال پیام به این دانش‌آموز"
                                        >
                                            <x-icon name="chat" class="h-4 w-4" />
                                            <span class="hidden sm:inline">پیام</span>
                                        </a>
                                    </div>
                                    @endforeach
                                    </div>
                                    @else
                                        <div class="flex flex-col items-center gap-3 py-8 text-center text-slate-500">
                                            <x-icon name="users" class="h-10 w-10 text-slate-300" />
                                            <p class="text-sm">هنوز دانش‌آموزی برای این کلاس ثبت نشده است.</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-3 py-8 text-center text-slate-500">
                            <x-icon name="users" class="h-10 w-10 text-slate-300" />
                            <p class="text-sm">هنوز دانش‌آموزی برای این کلاس ثبت نشده است.</p>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                    <button 
                        wire:click="closeStudents"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                    >
                        بستن
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>