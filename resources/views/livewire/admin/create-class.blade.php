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

<div class="max-w-md p-6 bg-white rounded-lg shadow">
    <h2 class="mb-4 text-lg font-bold">ساخت کلاس جدید</h2>

    @if ($successMessage)
        <div class="mb-4 text-sm text-green-600">{{ $successMessage }}</div>
    @endif

    <form wire:submit="createClass">
        <div class="mb-4">
            <label class="block mb-1 text-sm">نام کلاس</label>
            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded" placeholder="مثلاً: ریاضی - گروه ۱">
            @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm">درس / موضوع کلاس</label>
            <input type="text" wire:model="subject" class="w-full px-3 py-2 border rounded" placeholder="مثلاً: ریاضی، خواندن، نوشتن، یا هر چیز دیگه">
            @error('subject') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm">مربی</label>
            <select wire:model="therapist_id" class="w-full px-3 py-2 border rounded">
                <option value="">— انتخاب کنید —</option>
                @foreach($this->therapists() as $therapist)
                    <option value="{{ $therapist->id }}">{{ $therapist->name }}</option>
                @endforeach
            </select>
            @error('therapist_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
            ساخت کلاس
        </button>
    </form>
</div>