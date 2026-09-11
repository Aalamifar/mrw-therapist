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
    public bool $showPassword = false;
    public string $successMessage = '';

    public function createUser()
    {
        $validated = $this->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|regex:/^09[0-9]{9}$/|unique:users,phone',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:student,therapist,admin',
        ], [
            'phone.regex' => 'شماره تلفن باید به فرم صحیح (مثل 09123456789) وارد بشه.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        $this->successMessage = 'کاربر با موفقیت ساخته شد.';
        $this->reset(['name', 'phone', 'password']);
        $this->showPassword = false;
    }
}; ?>

<div class="mx-auto max-w-lg space-y-5">

    {{-- ═══════════ بازگشت ═══════════ --}}
    <a
        href="{{ route('admin.dashboard') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-right" class="h-4 w-4" />
        بازگشت
    </a>

    {{-- ═══════════ کارت اصلی ═══════════ --}}
    <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">

        {{-- هدر با آیکون پاستلی --}}
        <div class="flex items-start gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="user" class="h-7 w-7" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">ساخت کاربر جدید</h2>
                <p class="mt-1 text-sm text-slate-500">اطلاعات کاربر رو وارد کن و نقشش رو مشخص کن.</p>
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
        <form wire:submit="createUser" class="mt-6 space-y-5">

            {{-- نام و نام خانوادگی --}}
            <div>
                <label class="mb-1.5 block text-sm font-bold">نام و نام خانوادگی</label>
                <div class="relative">
                    <x-icon name="user" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="مثلاً علی رضایی"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-3 pr-10 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20 @error('name') border-red-400 focus:border-red-400 focus:ring-red-100 @enderror"
                    >
                </div>
                @error('name') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- شماره تلفن --}}
            <div>
                <label class="mb-1.5 block text-sm font-bold">شماره تلفن</label>
                <div class="relative">
                    <x-icon name="phone" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                    <input
                        type="tel"
                        inputmode="numeric"
                        wire:model="phone"
                        placeholder="09123456789"
                        dir="ltr"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-3 pr-10 text-left text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20 @error('phone') border-red-400 focus:border-red-400 focus:ring-red-100 @enderror"
                    >
                </div>
                @error('phone') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- رمز عبور --}}
            <div>
                <label class="mb-1.5 block text-sm font-bold">رمز عبور</label>
                <div class="relative">
                    <x-icon name="lock" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                    <input
                        type="{{ $showPassword ? 'text' : 'password' }}"
                        wire:model="password"
                        placeholder="حداقل ۶ کاراکتر"
                        dir="ltr"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-10 pr-10 text-left text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20 @error('password') border-red-400 focus:border-red-400 focus:ring-red-100 @enderror"
                    >
                    <button
                        type="button"
                        wire:click="$toggle('showPassword')"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-[#b98a10]"
                        tabindex="-1"
                    >
                        <x-icon name="{{ $showPassword ? 'eye-off' : 'eye' }}" class="h-4 w-4" />
                    </button>
                </div>
                @error('password') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- نقش کاربر --}}
            <div>
                <label class="mb-1.5 block text-sm font-bold">نقش کاربر</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                        'student'   => ['دانش‌آموز', 'academic-cap'],
                        'therapist' => ['مربی', 'users'],
                        'admin'     => ['ادمین', 'shield-check'],
                    ] as $value => [$label, $icon])
                        <label wire:key="role-{{ $value }}" class="cursor-pointer">
                            <input type="radio" wire:model="role" value="{{ $value }}" class="peer sr-only">
                            <span class="flex flex-col items-center gap-1.5 rounded-xl border border-[#eee4d9] bg-white px-2 py-3 text-center text-xs font-bold text-slate-500 shadow-sm transition hover:border-[#d7a92b]/40 hover:bg-[#faf5ec] peer-checked:border-[#d7a92b] peer-checked:bg-[#faf0d2] peer-checked:text-[#b98a10] peer-checked:ring-2 peer-checked:ring-[#d7a92b]/20">
                                <x-icon name="{{ $icon }}" class="h-5 w-5" />
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('role') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- دکمه submit --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="createUser"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
                <span wire:loading.remove wire:target="createUser" class="inline-flex items-center gap-2">
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت کاربر
                </span>
                <span wire:loading wire:target="createUser" class="inline-flex items-center gap-2">
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
                شماره تلفن، نام کاربری این شخص برای ورود به سامانه خواهد بود؛ پس مطمئن شو درست و بدون تکرار وارد شده.
            </p>
        </div>
    </div>
</div>