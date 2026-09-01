<?php

use Livewire\Volt\Component;
use App\Models\User;

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
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->get();
    }

    public function deleteUser($userId)
    {
        User::findOrFail($userId)->delete();
        $this->successMessage = 'کاربر حذف شد.';
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
        $user->update(['password' => $this->newPassword]);

        $this->successMessage = 'رمز عبور ' . $user->name . ' تغییر کرد.';
        $this->passwordEditingUserId = null;
        $this->newPassword = '';
    }
}; ?>

<div>
    <h2 class="mb-4 text-lg font-bold">مدیریت کاربران</h2>

    <input type="text" wire:model.live="search" placeholder="جستجوی نام یا تلفن..." class="w-full max-w-sm px-3 py-2 mb-4 border rounded">

    @if ($successMessage)
        <div class="mb-4 text-sm text-green-600">{{ $successMessage }}</div>
    @endif

    <div class="overflow-hidden bg-white rounded-lg shadow">
        <table class="w-full text-right">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">نام</th>
                    <th class="px-4 py-2">تلفن</th>
                    <th class="px-4 py-2">نقش</th>
                    <th class="px-4 py-2">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->users() as $user)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->phone }}</td>
                        <td class="px-4 py-2">{{ $user->role }}</td>
                        <td class="px-4 py-2 space-x-2 space-x-reverse">
                            <button wire:click="startEdit({{ $user->id }})" title="ویرایش">✏️</button>
                            <button wire:click="startEditPassword({{ $user->id }})" class="text-blue-600 underline">
                                تغییر رمز
                            </button>
                            <button wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="مطمئنی می‌خوای این کاربر رو حذف کنی؟"
                                    class="text-red-600 underline">
                                حذف
                            </button>
                        </td>
                    </tr>

                    @if ($editingUserId === $user->id)
                        <tr class="bg-gray-50">
                            <td colspan="4" class="px-4 py-3">
                                <form wire:submit="updateUser" class="flex flex-wrap items-center gap-2">
                                    <input type="text" wire:model="editName" placeholder="نام" class="px-3 py-1 border rounded">
                                    <input type="text" wire:model="editPhone" placeholder="تلفن" class="px-3 py-1 border rounded">
                                    <select wire:model="editRole" class="px-3 py-1 border rounded">
                                        <option value="student">دانش‌آموز</option>
                                        <option value="therapist">مربی</option>
                                        <option value="admin">ادمین</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1 text-white bg-blue-600 rounded">ذخیره</button>
                                    <button type="button" wire:click="$set('editingUserId', null)" class="px-3 py-1 text-gray-600">انصراف</button>
                                </form>
                                @error('editName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                @error('editPhone') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    @endif

                    @if ($passwordEditingUserId === $user->id)
                        <tr class="bg-gray-50">
                            <td colspan="4" class="px-4 py-3">
                                <form wire:submit="updatePassword" class="flex items-center gap-2">
                                    <input type="text" wire:model="newPassword" placeholder="رمز جدید" class="px-3 py-1 border rounded">
                                    <button type="submit" class="px-3 py-1 text-white bg-blue-600 rounded">ذخیره</button>
                                    <button type="button" wire:click="$set('passwordEditingUserId', null)" class="px-3 py-1 text-gray-600">انصراف</button>
                                </form>
                                @error('newPassword') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>