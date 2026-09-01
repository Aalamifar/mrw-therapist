<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $password = '';
    public string $role = 'student';
    public string $successMessage = '';

    public function createUser()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:student,therapist,admin',
        ]);

        User::create($validated);

        $this->successMessage = 'کاربر با موفقیت ساخته شد.';
        $this->reset(['name', 'phone', 'password']);
    }
}; ?>

<div class="max-w-md p-6 bg-white rounded-lg shadow">
    <h2 class="mb-4 text-lg font-bold">ساخت کاربر جدید</h2>

    @if ($successMessage)
        <div class="mb-4 text-sm text-green-600">{{ $successMessage }}</div>
    @endif

    <form wire:submit="createUser">
        <div class="mb-4">
            <label class="block mb-1 text-sm">نام و نام خانوادگی</label>
            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded">
            @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm">شماره تلفن</label>
            <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded">
            @error('phone') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm">رمز عبور</label>
            <input type="text" wire:model="password" class="w-full px-3 py-2 border rounded">
            @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm">نقش</label>
            <select wire:model="role" class="w-full px-3 py-2 border rounded">
                <option value="student">دانش‌آموز</option>
                <option value="therapist">مربی</option>
                <option value="admin">ادمین</option>
            </select>
        </div>

        <button type="submit" class="w-full py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
            ساخت کاربر
        </button>
    </form>
</div>