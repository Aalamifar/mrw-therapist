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
                $query->where(function ($q) {
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
            'editName'  => 'required|string|max:255',
            'editPhone' => 'required|string|unique:users,phone,' . $this->editingUserId,
            'editRole'  => 'required|in:student,therapist,admin',
        ]);

        User::findOrFail($this->editingUserId)->update([
            'name'  => $validated['editName'],
            'phone' => $validated['editPhone'],
            'role'  => $validated['editRole'],
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

    /** رنگ‌بندی و برچسب هر نقش — یک‌جا و قابل نگهداری */
    public function roleInfo(string $role): array
    {
        return match ($role) {
            'admin' => [
                'label'  => 'ادمین',
                'badge'  => 'bg-[#faf0d2] text-[#b98a10]',
                'avatar' => 'bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826]',
            ],
            'therapist' => [
                'label'  => 'مربی',
                'badge'  => 'bg-[#f3e8fa] text-[#9333ea]',
                'avatar' => 'bg-[#f3e8fa] text-[#9333ea]',
            ],
            default => [
                'label'  => 'دانش‌آموز',
                'badge'  => 'bg-[#dff4f0] text-[#0d9488]',
                'avatar' => 'bg-[#dff4f0] text-[#0d9488]',
            ],
        };
    }
}; ?>

<div class="space-y-6">

    {{-- ═══════════ هدر + دکمه کاربر جدید ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold">مدیریت کاربران</h1>
            <p class="mt-1 text-sm text-slate-500">مشاهده، ویرایش و مدیریت دسترسی کاربران سیستم</p>
        </div>
        <a
            href="{{ route('admin.users.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="plus" class="h-4 w-4 shrink-0" />
            <span>کاربر جدید</span>
        </a>
    </div>

    {{-- ═══════════ جستجو ═══════════ --}}
    <div class="relative w-full sm:max-w-sm">
        <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="جستجوی نام یا شماره تلفن..."
            class="w-full rounded-xl border border-[#eee4d9] bg-[#fdfbf8] py-2.5 pl-3 pr-10 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
        >
    </div>

    {{-- ═══════════ پیام موفقیت ═══════════ --}}
    @if ($successMessage)
        <div class="flex items-center gap-2 rounded-xl border border-[#c9ecd2] bg-[#e2f6e7] px-3 py-2.5 text-sm font-medium text-[#16a34a]">
            <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
            {{ $successMessage }}
        </div>
    @endif

    {{-- ═══════════ لیست کاربران ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->users() as $user)
            @php $info = $this->roleInfo($user->role); @endphp

            <div wire:key="user-{{ $user->id }}" class="border-b border-[#f0e7dc] px-4 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:px-5">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    {{-- اطلاعات کاربر --}}
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $info['avatar'] }}">
                            {{ $this->initials($user->name) }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate font-bold">{{ $user->name }}</div>
                            <div class="mt-0.5 text-xs text-slate-500" dir="ltr">{{ $user->phone }}</div>
                        </div>
                    </div>

                    {{-- نقش + عملیات --}}
                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <span class="rounded-md px-2.5 py-1 text-xs font-bold {{ $info['badge'] }}">
                            {{ $info['label'] }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button
                                wire:click="startEdit({{ $user->id }})"
                                class="rounded-lg p-2 text-slate-600 transition hover:bg-[#f0e7dc] hover:text-slate-800"
                                title="ویرایش"
                            >
                                <x-icon name="pencil" class="h-4 w-4" />
                            </button>
                            <button
                                wire:click="startEditPassword({{ $user->id }})"
                                class="rounded-lg p-2 text-[#9333ea] transition hover:bg-[#f3e8fa]"
                                title="تغییر رمز"
                            >
                                <x-icon name="lock" class="h-4 w-4" />
                            </button>
                            <button
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="مطمئنی می‌خوای این کاربر رو حذف کنی؟"
                                class="rounded-lg p-2 text-red-600 transition hover:bg-red-50"
                                title="حذف"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ═══ فرم ویرایش اطلاعات ═══ --}}
                @if ($editingUserId === $user->id)
                    <div class="mt-4 rounded-xl border border-[#eee4d9] bg-[#faf5ec] p-4">
                        <form wire:submit="updateUser" class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">نام</label>
                                <input
                                    type="text"
                                    wire:model="editName"
                                    class="w-full rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                >
                                @error('editName') <span class="mt-1 block text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">تلفن</label>
                                <input
                                    type="text"
                                    wire:model="editPhone"
                                    dir="ltr"
                                    class="w-full rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-left text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                >
                                @error('editPhone') <span class="mt-1 block text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">نقش</label>
                                <div class="relative">
                                    <select
                                        wire:model="editRole"
                                        class="w-full appearance-none rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                    >
                                        <option value="student">دانش‌آموز</option>
                                        <option value="therapist">مربی</option>
                                        <option value="admin">ادمین</option>
                                    </select>
                                    <x-icon name="chevron-down" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                                </div>
                                @error('editRole') <span class="mt-1 block text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="flex-1 rounded-xl bg-[#0b1826] py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                                >
                                    ذخیره
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('editingUserId', null)"
                                    class="flex-1 rounded-xl border border-[#eee4d9] bg-white py-2 text-sm text-slate-600 transition hover:bg-[#f0e7dc]"
                                >
                                    انصراف
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- ═══ فرم تغییر رمز ═══ --}}
                @if ($passwordEditingUserId === $user->id)
                    <div class="mt-4 rounded-xl border border-[#eee4d9] bg-[#faf5ec] p-4">
                        <form wire:submit="updatePassword" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="flex-1">
                                <label class="mb-1 block text-xs font-bold text-slate-600">رمز عبور جدید</label>
                                <input
                                    type="password"
                                    wire:model="newPassword"
                                    dir="ltr"
                                    class="w-full rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-left text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                >
                                @error('newPassword') <span class="mt-1 block text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="flex-1 rounded-xl bg-[#0b1826] py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                                >
                                    به‌روزرسانی رمز
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('passwordEditingUserId', null)"
                                    class="flex-1 rounded-xl border border-[#eee4d9] bg-white py-2 text-sm text-slate-600 transition hover:bg-[#f0e7dc]"
                                >
                                    انصراف
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                    <x-icon name="users" class="h-8 w-8" />
                </span>
                @if ($search)
                    <div>
                        <p class="font-bold">نتیجه‌ای پیدا نشد</p>
                        <p class="mt-1 text-sm text-slate-500">کاربری با این مشخصات وجود ندارد.</p>
                    </div>
                @else
                    <div>
                        <p class="font-bold">هنوز کاربری ساخته نشده</p>
                        <p class="mt-1 text-sm text-slate-500">اولین کاربر رو بساز و شروع کن.</p>
                    </div>
                    <a
                        href="{{ route('admin.users.create') }}"
                        class="mt-2 inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-bold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        ساخت اولین کاربر
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</div>