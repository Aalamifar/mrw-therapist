<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;
use App\Models\User;

new class extends Component
{
    public string $name = '';
    public string $subject = '';
    public $therapist_id = '';
    public string $successMessage = '';

    public function createClass()
    {
        $this->successMessage = '';

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'therapist_id' => 'required|exists:users,id',
        ]);

        SchoolClass::create($validated);

        $this->successMessage = 'کلاس با موفقیت ساخته شد.';
        $this->reset(['name', 'subject', 'therapist_id']);
    }

    public function therapists()
    {
        return User::where('role', 'therapist')->get();
    }
}; ?>

<div class="mx-auto max-w-lg space-y-5">

    {{-- ═══════════ بازگشت ═══════════ --}}
    <a
        href="{{ route('admin.dashboard') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-left" class="h-4 w-4" />
        بازگشت به لیست کلاس‌ها
    </a>

    {{-- ═══════════ کارت اصلی ═══════════ --}}
    <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">

        {{-- هدر با آیکون پاستلی --}}
        <div class="flex items-start gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="academic-cap" class="h-7 w-7" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">ساخت کلاس جدید</h2>
                <p class="mt-1 text-sm text-slate-500">مشخصات کلاس رو وارد کن و یه مربی بهش اختصاص بده.</p>
            </div>
        </div>

        {{-- پیام موفقیت --}}
        @if ($successMessage)
            <div class="mt-6 flex items-center gap-2 rounded-xl bg-[#e2f6e7] px-3 py-2.5 text-sm font-medium text-[#16a34a]">
                <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                {{ $successMessage }}
            </div>
        @endif

        {{-- فرم --}}
        <form wire:submit="createClass" class="mt-6 space-y-5">

            {{-- نام کلاس --}}
            <div>
                <label for="name" class="mb-1.5 block text-sm font-bold">نام کلاس</label>
                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    placeholder="مثلاً: ریاضی - گروه ۱"
                    class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                >
                @error('name')
                    <span class="mt-1.5 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            {{-- درس / موضوع --}}
            <div>
                <label for="subject" class="mb-1.5 block text-sm font-bold">درس / موضوع کلاس</label>
                <input
                    id="subject"
                    type="text"
                    wire:model="subject"
                    placeholder="مثلاً: ریاضی، خواندن، نوشتن، یا هر چیز دیگه"
                    class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                >
                @error('subject')
                    <span class="mt-1.5 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            {{-- مربی --}}
            <div>
                <label for="therapist_id" class="mb-1.5 block text-sm font-bold">مربی</label>
                <div class="relative">
                    <select
                        id="therapist_id"
                        wire:model="therapist_id"
                        class="w-full appearance-none rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    >
                        <option value="">— انتخاب کنید —</option>
                        @foreach ($this->therapists() as $therapist)
                            <option value="{{ $therapist->id }}">{{ $therapist->name }}</option>
                        @endforeach
                    </select>
                    {{-- فلش سفارشی --}}
                    <x-icon
                        name="chevron-down"
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]"
                    />
                </div>
                @error('therapist_id')
                    <span class="mt-1.5 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            {{-- دکمه submit --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="createClass"
                class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
                <span wire:loading.remove wire:target="createClass" class="inline-flex items-center gap-2">
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت کلاس
                </span>
                <span wire:loading wire:target="createClass" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    در حال ساخت...
                </span>
            </button>
        </form>
    </div>

    {{-- ═══════════ نکته راهنما ═══════════ --}}
    <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8]/60 px-5 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <x-icon name="sparkles" class="h-5 w-5 shrink-0 text-[#d7a92b]" />
            <p class="text-xs leading-relaxed text-slate-500">
                بعد از ساخت کلاس، می‌تونی از لیست کلاس‌ها بهش دانش‌آموز اختصاص بدی و جزئیات بیشتری تنظیم کنی.
            </p>
        </div>
    </div>
</div>