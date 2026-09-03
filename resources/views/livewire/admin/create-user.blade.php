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

<div class="mx-auto max-w-md space-y-4">

    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#061C32]">
        <x-icon name="home" class="h-3.5 w-3.5" />
        بازگشت
    </a>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-1 text-lg font-bold text-[#061C32]">ساخت کاربر جدید</h2>
        <p class="mb-5 text-sm text-slate-500">اطلاعات کاربر رو وارد کن و نقشش رو مشخص کن</p>

        @if ($successMessage)
            <div class="mb-5 flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2.5 text-sm text-green-700">
                <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                {{ $successMessage }}
            </div>
        @endif

        <form wire:submit="createUser" class="space-y-4">

            {{-- Name --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام و نام خانوادگی</label>
                <div class="relative">
                    <x-icon name="user" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="مثلا علی رضایی"
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10 @error('name') border-red-300 @enderror"
                    >
                </div>
                @error('name') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">شماره تلفن</label>
                <div class="relative">
                    <x-icon name="phone" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        type="tel"
                        inputmode="numeric"
                        wire:model="phone"
                        placeholder="09123456789"
                        dir="ltr"
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-left text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10 @error('phone') border-red-300 @enderror"
                    >
                </div>
                @error('phone') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">رمز عبور</label>
                <div class="relative">
                    <x-icon name="lock" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        type="{{ $showPassword ? 'text' : 'password' }}"
                        wire:model="password"
                        placeholder="حداقل ۶ کاراکتر"
                        dir="ltr"
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-left text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10 @error('password') border-red-300 @enderror"
                    >
                    <button
                        type="button"
                        wire:click="$toggle('showPassword')"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-[#061C32]"
                        tabindex="-1"
                    >
                        <x-icon name="{{ $showPassword ? 'eye-off' : 'eye' }}" class="h-4 w-4" />
                    </button>
                </div>
                @error('password') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نقش کاربر</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                        'student' => ['دانش‌آموز', 'academic-cap'],
                        'therapist' => ['مربی', 'users'],
                        'admin' => ['ادمین', 'shield-check'],
                    ] as $value => [$label, $icon])
                        <label wire:key="role-{{ $value }}" class="cursor-pointer">
                            <input type="radio" wire:model="role" value="{{ $value }}" class="peer sr-only">
                            <span class="flex flex-col items-center gap-1.5 rounded-lg border border-slate-200 px-2 py-3 text-center text-xs font-medium text-slate-500 transition hover:bg-slate-50 peer-checked:border-[#E9C72A] peer-checked:bg-[#E9C72A]/15 peer-checked:text-[#061C32]">
                                <x-icon name="{{ $icon }}" class="h-5 w-5" />
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('role') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="createUser"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#E9C72A] py-2.5 text-sm font-bold text-[#061C32] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="createUser">ساخت کاربر</span>
                <span wire:loading wire:target="createUser">در حال ساخت...</span>
            </button>
        </form>
    </div>
</div>
