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

<div class="mx-auto max-w-md space-y-4">

    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#061C32]">
        <x-icon name="home" class="h-3.5 w-3.5" />
        بازگشت به لیست کلاس‌ها
    </a>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-1 text-lg font-bold text-[#061C32]">ساخت کلاس جدید</h2>
        <p class="mb-5 text-sm text-slate-500">مشخصات کلاس رو وارد کن و یه مربی بهش اختصاص بده.</p>

        @if ($successMessage)
            <div class="mb-5 flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2.5 text-sm text-green-700">
                <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                {{ $successMessage }}
            </div>
        @endif

        <form wire:submit="createClass" class="space-y-4">
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">نام کلاس</label>
                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    placeholder="مثلاً: ریاضی - گروه ۱"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
                >
                @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="subject" class="mb-1 block text-sm font-medium text-slate-700">درس / موضوع کلاس</label>
                <input
                    id="subject"
                    type="text"
                    wire:model="subject"
                    placeholder="مثلاً: ریاضی، خواندن، نوشتن، یا هر چیز دیگه"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
                >
                @error('subject') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="therapist_id" class="mb-1 block text-sm font-medium text-slate-700">مربی</label>
                <select
                    id="therapist_id"
                    wire:model="therapist_id"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
                >
                    <option value="">— انتخاب کنید —</option>
                    @foreach ($this->therapists() as $therapist)
                        <option value="{{ $therapist->id }}">{{ $therapist->name }}</option>
                    @endforeach
                </select>
                @error('therapist_id') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="createClass"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#E9C72A] py-2.5 text-sm font-bold text-[#061C32] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="createClass">ساخت کلاس</span>
                <span wire:loading wire:target="createClass">در حال ساخت...</span>
            </button>
        </form>
    </div>
</div>