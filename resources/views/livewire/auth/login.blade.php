<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $phone = '';
    public string $password = '';
    public string $error = '';

    public function login()
    {
        $this->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['phone' => $this->phone, 'password' => $this->password])) {
            session()->regenerate();

            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'therapist' => redirect()->route('therapist.dashboard'),
                'student' => redirect()->route('student.dashboard'),
            };
        }

        $this->error = 'شماره تلفن یا رمز عبور اشتباه است.';
    }
}; ?>

<div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm p-6 bg-white rounded-lg shadow">
        <h2 class="mb-4 text-lg font-bold">ورود به حساب کاربری</h2>

        @if ($error)
            <div class="mb-4 text-sm text-red-600">{{ $error }}</div>
        @endif

        <form wire:submit="login">
            <div class="mb-4">
                <label class="block mb-1 text-sm">شماره تلفن</label>
                <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded">
                @error('phone') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block mb-1 text-sm">رمز عبور</label>
                <input type="password" wire:model="password" class="w-full px-3 py-2 border rounded">
                @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full py-2 text-white bg-yellow-500 rounded hover:bg-yellow-600">
                ورود
            </button>
        </form>
    </div>
</div>