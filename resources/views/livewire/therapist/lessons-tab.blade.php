<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $classId;
    public string $type; // 'video' یا 'pdf'

    public string $title = '';
    public string $description = '';
    public $file;

    public bool $showForm = false;

    public function mount($classId, $type)
    {
        $this->classId = $classId;
        $this->type = $type;
    }

    public function lessons()
    {
        return Lesson::where('class_id', $this->classId)
            ->where('type', $this->type)
            ->latest()
            ->get();
    }

    public function save()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => $this->type === 'video'
                ? 'required|file|mimetypes:video/mp4,video/webm,video/quicktime|max:512000'
                : 'required|file|mimes:pdf|max:51200',
        ];

        $this->validate($rules);

        $path = $this->file->store('lessons/' . $this->type . 's', 'local');

        Lesson::create([
            'class_id' => $this->classId,
            'title' => $this->title,
            'type' => $this->type,
            'content_url' => $path,
            'description' => $this->description,
        ]);

        $this->reset(['title', 'description', 'file', 'showForm']);
    }

    public function delete($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        Storage::disk('local')->delete($lesson->content_url);
        $lesson->delete();
    }
}; ?>

<div class="space-y-6">

    {{-- دکمه‌ی افزودن --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#061C32]">
            {{ $type === 'video' ? 'فیلم‌های آموزشی' : 'جزوه و کتاب' }}
        </h2>
        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E9C72A] px-4 py-2 text-sm font-bold text-[#061C32] transition hover:brightness-95"
        >
            <x-icon name="plus" class="h-4 w-4" />
            {{ $type === 'video' ? 'افزودن فیلم' : 'افزودن جزوه' }}
        </button>
    </div>

    {{-- فرم افزودن --}}
    @if ($showForm)
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm text-slate-600">عنوان</label>
                    <input type="text" wire:model="title"
                           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10">
                    @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm text-slate-600">توضیحات (اختیاری)</label>
                    <textarea wire:model="description" rows="2"
                              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"></textarea>
                </div>

                <div>
                    <label class="block mb-1 text-sm text-slate-600">
                        {{ $type === 'video' ? 'فایل ویدیو' : 'فایل PDF' }}
                    </label>
                    <input type="file" wire:model="file"
                           class="w-full text-sm">
                    <div wire:loading wire:target="file" class="mt-1 text-xs text-slate-400">در حال آپلود...</div>
                    @error('file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-[#061C32] px-4 py-2 text-sm font-medium text-white hover:brightness-110">
                        ذخیره
                    </button>
                    <button type="button" wire:click="$set('showForm', false)"
                            class="rounded-lg px-4 py-2 text-sm text-slate-500 hover:bg-slate-100">
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- لیست --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @forelse ($this->lessons() as $lesson)
            <div wire:key="lesson-{{ $lesson->id }}"
                 class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50/70">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-[#061C32] truncate">{{ $lesson->title }}</div>
                    @if($lesson->description)
                        <div class="text-sm text-slate-500 truncate">{{ $lesson->description }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <a href="{{ route('lesson.file', $lesson->id) }}" target="_blank"
                       class="rounded-lg px-3 py-1.5 text-sm text-[#061C32] hover:bg-[#061C32]/5">
                        مشاهده
                    </a>
                    <button wire:click="delete({{ $lesson->id }})"
                            wire:confirm="مطمئنی می‌خوای حذف کنی؟"
                            class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">
                        حذف
                    </button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">هنوز چیزی اضافه نشده.</p>
            </div>
        @endforelse
    </div>
</div>