<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}; ?>

<div class="p-8">
    <h1 class="text-xl font-bold">خوش اومدی، {{ auth()->user()->name }}</h1>
    <p class="mt-2 text-sm text-gray-600">نقش: {{ auth()->user()->role }}</p>
    <button wire:click="logout" class="px-4 py-2 mt-4 text-white bg-red-500 rounded">خروج</button>
</div>