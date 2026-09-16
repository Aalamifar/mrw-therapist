<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;
use App\Models\User;

new class extends Component
{
    public $classId;
    public $selectedStudents = [];
    public string $successMessage = '';
    public string $search = '';
    public bool $showSelectedOnly = false;

    public function mount($classId)
    {
        $this->classId = $classId;
        $class = SchoolClass::findOrFail($classId);
        $this->selectedStudents = $class->students->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    public function class()
    {
        return SchoolClass::findOrFail($this->classId);
    }

    public function allStudents()
    {
        return User::where('role', 'student')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->when($this->showSelectedOnly, function ($query) {
                $query->whereIn('id', $this->selectedStudents);
            })
            ->orderBy('name')
            ->get();
    }

    public function totalStudents(): int
    {
        return User::where('role', 'student')->count();
    }

    /** ✅ جدید: toggle انتخاب کاملاً سروری — هر کلیک = re-render فوری */
    public function toggleStudent($id)
    {
        $id = (int) $id;
        $current = array_values(array_unique(array_map('intval', $this->selectedStudents)));

        $this->selectedStudents = in_array($id, $current, true)
            ? array_values(array_diff($current, [$id]))
            : [...$current, $id];

        $this->successMessage = '';
    }

    public function saveStudents()
    {
        $class = SchoolClass::findOrFail($this->classId);
        $class->students()->sync($this->selectedStudents);

        $this->successMessage = 'دانش‌آموزهای کلاس با موفقیت به‌روزرسانی شد.';
    }

    public function selectAll()
    {
        $this->selectedStudents = User::where('role', 'student')
            ->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->successMessage = '';
    }

    public function deselectAll()
    {
        $this->selectedStudents = [];
        $this->successMessage = '';
    }

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

    public function updatedSearch()
    {
        $this->successMessage = '';
    }

    public function updatedShowSelectedOnly()
    {
        $this->successMessage = '';
    }

    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="mx-auto max-w-lg space-y-5">

        <a
        href="{{ route('admin.dashboard') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-right" class="h-4 w-4" />
        بازگشت
    </a>

    @php $class = $this->class(); @endphp

    <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">

                <div class="flex items-start gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="academic-cap" class="h-7 w-7" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="text-xl font-extrabold">مدیریت دانش‌آموزها</h2>
                <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span class="truncate">{{ $class->name }}</span>
                    <span class="rounded-md bg-[#faf0d2] px-2 py-0.5 text-xs font-bold text-[#b98a10]">{{ $class->subject }}</span>
                </p>
            </div>
        </div>

                @if ($successMessage)
            <div class="mt-6 flex items-center gap-2 rounded-xl bg-[#e2f6e7] px-3 py-2.5 text-sm font-medium text-[#16a34a]">
                <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                {{ $successMessage }}
            </div>
        @endif

                <div class="relative mt-6">
            <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="جستجوی دانش‌آموز..."
                class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-10 pr-10 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
            >
            @if ($search)
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-red-500"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            @endif
            <div wire:loading wire:target="search" class="absolute left-3 top-1/2 -translate-y-1/2">
                <svg class="h-4 w-4 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

                <div class="mt-4 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 rounded-lg bg-[#faf0d2] px-2.5 py-1.5 text-[#b98a10]">
                    <x-icon name="users" class="h-3.5 w-3.5" />
                    <span class="text-xs font-extrabold">{{ $this->fa(count($selectedStudents)) }}</span>
                    <span class="text-xs font-medium">انتخاب شده</span>
                </div>
                <span class="text-xs text-slate-400">از {{ $this->fa($this->totalStudents()) }} نفر</span>
            </div>

                        <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" wire:model.live="showSelectedOnly" class="sr-only">
                <span class="relative h-5 w-9 shrink-0 rounded-full transition {{ $showSelectedOnly ? 'bg-[#d7a92b]' : 'bg-[#e0d4bf]' }}">
                    <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-all {{ $showSelectedOnly ? 'right-[18px]' : 'right-0.5' }}"></span>
                </span>
                <span class="text-xs font-bold text-slate-600">فقط انتخاب‌شده‌ها</span>
            </label>
        </div>

                <div class="mt-4 flex gap-2">
            <button
                type="button"
                wire:click="selectAll"
                class="flex-1 rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm transition hover:border-[#d7a92b]/50 hover:bg-[#faf0d2] hover:text-[#b98a10]"
            >
                <x-icon name="check-circle" class="inline h-3.5 w-3.5" />
                انتخاب همه
            </button>
            <button
                type="button"
                wire:click="deselectAll"
                class="flex-1 rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm transition hover:border-red-300 hover:bg-red-50 hover:text-red-600"
            >
                <x-icon name="x-circle" class="inline h-3.5 w-3.5" />
                لغو انتخاب
            </button>
        </div>

                        <div
            class="mt-4 max-h-96 space-y-2 overflow-y-auto rounded-xl border border-[#eee4d9] bg-[#faf5ec] p-2 transition"
            wire:loading.class="opacity-50 pointer-events-none"
            wire:target="toggleStudent, selectAll, deselectAll"
        >
            @forelse ($this->allStudents() as $student)
                @php
                    $isSelected = in_array((int) $student->id, array_map('intval', $selectedStudents), true);
                @endphp

                                <button
                    type="button"
                    wire:key="student-{{ $student->id }}"
                    wire:click="toggleStudent({{ $student->id }})"
                    class="flex w-full items-center gap-3 rounded-xl border px-3 py-2.5 text-right transition {{ $isSelected ? 'border-[#d7a92b]/60 bg-[#faf0d2]/70 shadow-sm' : 'border-transparent bg-white hover:border-[#eee4d9] hover:shadow-sm' }}"
                >
                                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-md border-2 transition {{ $isSelected ? 'border-[#d7a92b] bg-[#d7a92b]' : 'border-[#d8cbb4] bg-white' }}">
                        <x-icon name="check" class="h-3 w-3 text-white transition {{ $isSelected ? 'opacity-100' : 'opacity-0' }}" />
                    </span>

                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $this->avatarTone($student->id) }}">
                        {{ mb_substr($student->name, 0, 1) }}
                    </span>

                                        <span class="min-w-0 flex-1 text-right">
                        <span class="block truncate text-sm font-bold">{{ $student->name }}</span>
                        <span class="block truncate text-xs text-slate-400" dir="ltr">{{ $student->phone }}</span>
                    </span>

                                        @if ($isSelected)
                        <span class="shrink-0 rounded-full bg-[#d7a92b]/20 px-2 py-0.5 text-xs font-bold text-[#b98a10]">
                            انتخاب شده
                        </span>
                    @endif
                </button>
            @empty
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <span class="mb-3 grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                        <x-icon name="user-x" class="h-8 w-8" />
                    </span>
                    <p class="mb-1 text-sm font-bold text-slate-600">
                        @if ($search)
                            نتیجه‌ای پیدا نشد
                        @elseif ($showSelectedOnly)
                            دانش‌آموزی انتخاب نشده
                        @else
                            هنوز دانش‌آموزی ثبت نشده
                        @endif
                    </p>
                    <p class="text-xs text-slate-400">
                        @if ($search)
                            «{{ $search }}» رو با عبارت دیگه‌ای امتحان کن
                        @elseif ($showSelectedOnly)
                            از لیست، دانش‌آموز انتخاب کن
                        @else
                            ابتدا از صفحهٔ کاربران، دانش‌آموز بساز
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

                <button
            wire:click="saveStudents"
            wire:loading.attr="disabled"
            wire:target="saveStudents"
            class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
        >
            <span wire:loading.remove wire:target="saveStudents" class="inline-flex items-center gap-2">
                <x-icon name="check" class="h-4 w-4" />
                ذخیره تغییرات
            </span>
            <span wire:loading wire:target="saveStudents" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                در حال ذخیره...
            </span>
        </button>
    </div>
</div>