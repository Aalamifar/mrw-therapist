<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public $editingUserId = null;
    public string $editName = '';
    public string $editPhone = '';
    public string $editRole = '';

    public $passwordEditingUserId = null;
    public string $newPassword = '';

    public string $successMessage = '';
    public string $search = '';

    public function users()
    {
        return User::when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->get();
    }

    public function deleteUser($userId)
    {
        User::findOrFail($userId)->delete();
        $this->successMessage = 'کاربر با موفقیت حذف شد.';
    }

    public function startEdit($userId)
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $userId;
        $this->editName = $user->name;
        $this->editPhone = $user->phone;
        $this->editRole = $user->role;
        $this->passwordEditingUserId = null;
    }

    public function updateUser()
    {
        $validated = $this->validate([
            'editName' => 'required|string|max:255',
            'editPhone' => 'required|string|unique:users,phone,' . $this->editingUserId,
            'editRole' => 'required|in:student,therapist,admin',
        ]);

        User::findOrFail($this->editingUserId)->update([
            'name' => $validated['editName'],
            'phone' => $validated['editPhone'],
            'role' => $validated['editRole'],
        ]);

        $this->successMessage = 'اطلاعات کاربر به‌روزرسانی شد.';
        $this->editingUserId = null;
    }

    public function startEditPassword($userId)
    {
        $this->passwordEditingUserId = $userId;
        $this->newPassword = '';
        $this->editingUserId = null;
    }

    public function updatePassword()
    {
        $this->validate([
            'newPassword' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($this->passwordEditingUserId);
        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->successMessage = 'رمز عبور ' . $user->name . ' تغییر کرد.';
        $this->passwordEditingUserId = null;
        $this->newPassword = '';
    }

    public function initials(string $name): string
    {
        return mb_substr($name, 0, 1);
    }
}; ?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#061C32]">مدیریت کاربران</h1>
            <p class="mt-1 text-sm text-slate-500">مشاهده، ویرایش و مدیریت دسترسی کاربران سیستم</p>
        </div>
        <a
            href="{{ route('admin.users.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#E9C72A] px-4 py-2.5 text-sm font-bold text-[#061C32] transition hover:brightness-95"
        >
            <x-icon name="plus" class="h-4 w-4 shrink-0" />
            <span>کاربر جدید</span>
        </a>
    </div>

    {{-- Search --}}
    <div class="relative w-full sm:max-w-sm">
        <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="جستجوی نام یا شماره تلفن..."
            class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
        >
    </div>

    {{-- Success Message --}}
    @if ($successMessage)
        <div class="flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2.5 text-sm text-green-700 border border-green-100">
            <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
            {{ $successMessage }}
        </div>
    @endif

    {{-- Users List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @forelse ($this->users() as $user)
            <div wire:key="user-{{ $user->id }}" class="border-b border-slate-100 px-4 py-4 last:border-b-0 hover:bg-slate-50/70 sm:px-5">
                
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    
                    {{-- User Info --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#061C32] text-xs font-bold text-white">
                            {{ $this->initials($user->name) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-[#061C32] truncate">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->phone }}</div>
                        </div>
                    </div>

                    {{-- Role & Actions --}}
                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <span class="rounded-md bg-[#061C32]/5 px-2 py-0.5 text-xs text-[#061C32]/70">
                            {{ $user->role === 'admin' ? 'ادمین' : ($user->role === 'therapist' ? 'مربی' : 'دانش‌آموز') }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button 
                                wire:click="startEdit({{ $user->id }})"
                                class="p-1.5 text-slate-600 transition hover:bg-slate-100 rounded-lg"
                                title="ویرایش"
                            >
                                <x-icon name="pencil" class="h-4 w-4" />
                            </button>
                            <button 
                                wire:click="startEditPassword({{ $user->id }})"
                                class="p-1.5 text-blue-600 transition hover:bg-blue-50 rounded-lg"
                                title="تغییر رمز"
                            >
                                <x-icon name="lock" class="h-4 w-4" />
                            </button>
                            <button 
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="مطمئنی می‌خوای این کاربر رو حذف کنی؟"
                                class="p-1.5 text-red-600 transition hover:bg-red-50 rounded-lg"
                                title="حذف"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Inline Edit Form --}}
                @if ($editingUserId === $user->id)
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <form wire:submit="updateUser" class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">نام</label>
                                <input type="text" wire:model="editName" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#061C32]/10">
                                @error('editName') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">تلفن</label>
                                <input type="text" wire:model="editPhone" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#061C32]/10">
                                @error('editPhone') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">نقش</label>
                                <select wire:model="editRole" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#061C32]/10">
                                    <option value="student">دانش‌آموز</option>
                                    <option value="therapist">مربی</option>
                                    <option value="admin">ادمین</option>
                                </select>
                                @error('editRole') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-[#061C32] text-white py-2 rounded-lg text-sm font-bold hover:brightness-110">ذخیره</button>
                                <button type="button" wire:click="$set('editingUserId', null)" class="flex-1 bg-white border border-slate-200 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50">انصراف</button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Password Edit Form --}}
                @if ($passwordEditingUserId === $user->id)
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <form wire:submit="updatePassword" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-500 mb-1">رمز عبور جدید</label>
                                <input type="password" wire:model="newPassword" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#061C32]/10">
                                @error('newPassword') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-[#061C32] text-white py-2 rounded-lg text-sm font-bold hover:brightness-110">به‌روزرسانی رمز</button>
                                <button type="button" wire:click="$set('passwordEditingUserId', null)" class="flex-1 bg-white border border-slate-200 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50">انصراف</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center gap-3 px-5 py-16 text-center">
                <x-icon name="users" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">کاربری یافت نشد.</p>
            </div>
        @endforelse
    </div>
</div>
