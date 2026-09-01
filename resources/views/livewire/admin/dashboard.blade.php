<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;

new class extends Component
{
    public string $search = '';
    
    public function classes()
    {
        return SchoolClass::with('therapist')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%');
            })
            ->get();
    }

    public function deleteClass($classId)
    {
    \App\Models\SchoolClass::findOrFail($classId)->delete();
    }


}; ?>

<div>
    <h1 class="mb-4 text-xl font-bold">داشبورد ادمین</h1>

    <div class="flex gap-3 mb-6">
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 text-white bg-green-600 rounded">
            ساخت کاربر جدید
        </a>
        <a href="{{ route('admin.classes.create') }}" class="px-4 py-2 text-white bg-purple-600 rounded">
            ساخت کلاس جدید
        </a>
    </div>

    <h2 class="mb-3 text-lg font-bold">لیست کلاس‌ها</h2>

    <input type="text" wire:model.live="search" placeholder="جستجوی کلاس..." class="w-full max-w-sm px-3 py-2 mb-4 border rounded">

    <div class="overflow-hidden bg-white rounded-lg shadow">
        <table class="w-full text-right">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">نام کلاس</th>
                    <th class="px-4 py-2">درس</th>
                    <th class="px-4 py-2">مربی</th>
                    <th class="px-4 py-2">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->classes() as $class)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $class->name }}</td>
                        <td class="px-4 py-2">{{ $class->subject }}</td>
                        <td class="px-4 py-2">{{ $class->therapist->name }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.classes.students', $class->id) }}" class="text-blue-600 underline">
                                مدیریت دانش‌آموزها
                            </a>
                            <button wire:click="deleteClass({{ $class->id }})"
                                    wire:confirm="مطمئنی می‌خوای این کلاس رو حذف کنی؟"
                                    class="mr-3 text-red-600 underline">
                                حذف
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>