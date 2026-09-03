<?php

use Livewire\Volt\Component;
use App\Models\Assignment;

new class extends Component
{
    public $classId;
    public string $title = '';
    public string $description = '';
    public string $visibleAt = '';
    public string $dueDate = '';
    public bool $showForm = false;
    public ?int $editingId = null;

    public function mount($classId)
    {
        $this->classId = $classId;
    }

    public function assignments()
    {
        return Assignment::where('class_id', $this->classId)->latest()->get();
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibleAt' => 'nullable|date',
            'dueDate' => 'nullable|date|after_or_equal:visibleAt',
        ]);

        Assignment::create([
            'class_id' => $this->classId,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'visible_at' => $validated['visibleAt'] ?: null,
            'due_date' => $validated['dueDate'] ?: null,
        ]);

        $this->reset(['title', 'description', 'visibleAt', 'dueDate', 'showForm']);
    }

    public function delete($id)
    {
        Assignment::findOrFail($id)->delete();
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#061C32]">تکالیف</h2>
        <button wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
                class="inline-flex items-center gap-2 rounded-lg bg-[#E9C72A] px-4 py-2 text-sm font-bold text-[#061C32] hover:brightness-95">
            <x-icon name="plus" class="h-4 w-4" />
            افزودن تکلیف
        </button>
    </div>

    @if ($showForm)
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm text-slate-600">عنوان تکلیف</label>
                    <input type="text" wire:model="title"
                           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10">
                    @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm text-slate-600">توضیحات</label>
                    <textarea wire:model="description" rows="3"
                              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm text-slate-600">نمایان شود از تاریخ</label>
                        <x-jalali-datetime-picker model="visibleAt" />
                        @error('visibleAt') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-slate-600">مهلت پاسخگویی</label>
                        <x-jalali-datetime-picker model="dueDate" />
                        @error('dueDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-[#061C32] px-4 py-2 text-sm font-medium text-white hover:brightness-110">ذخیره</button>
                    <button type="button" wire:click="$set('showForm', false)" class="rounded-lg px-4 py-2 text-sm text-slate-500 hover:bg-slate-100">انصراف</button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @forelse ($this->assignments() as $assignment)
            <div wire:key="assignment-{{ $assignment->id }}"
                 class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50/70">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-[#061C32] truncate">{{ $assignment->title }}</div>
                    <div class="mt-1 flex flex-wrap gap-3 text-xs text-slate-400">
                        @if($assignment->visible_at)
                            <span>نمایش از: {{ $assignment->visible_at->format('Y/m/d H:i') }}</span>
                        @endif
                        @if($assignment->due_date)
                            <span>مهلت: {{ $assignment->due_date->format('Y/m/d H:i') }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button wire:click="delete({{ $assignment->id }})" wire:confirm="مطمئنی می‌خوای حذف کنی؟"
                            class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">حذف</button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">هنوز تکلیفی اضافه نشده.</p>
            </div>
        @endforelse
    </div>
</div>