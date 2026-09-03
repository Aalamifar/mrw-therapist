<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $classId;
    public string $title = '';
    public string $description = '';
    public $file;
    public bool $showForm = false;

    public function mount($classId)
    {
        $this->classId = $classId;
    }

    public function lessons()
    {
        return Lesson::where('class_id', $this->classId)
            ->where('type', 'html_game')
            ->latest()
            ->get();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:zip|max:102400', // تا ۱۰۰ مگابایت
        ]);

        // ذخیره‌ی موقت فایل zip
        $zipPath = $this->file->store('tmp', 'local');
        $fullZipPath = Storage::disk('local')->path($zipPath);

        // مسیر مقصد برای استخراج
        $extractFolder = 'lessons/games/' . uniqid();
        $fullExtractPath = Storage::disk('local')->path($extractFolder);

        $zip = new \ZipArchive;
        if ($zip->open($fullZipPath) === true) {
            $zip->extractTo($fullExtractPath);
            $zip->close();
        } else {
            $this->addError('file', 'فایل zip قابل باز شدن نیست.');
            Storage::disk('local')->delete($zipPath);
            return;
        }

        // حذف فایل zip موقت بعد از استخراج
        Storage::disk('local')->delete($zipPath);

        // بررسی وجود index.html داخل پوشه‌ی استخراج‌شده
        if (!file_exists($fullExtractPath . '/index.html')) {
            $this->addError('file', 'فایل zip باید حتماً یک index.html داخلش داشته باشه.');
            Storage::deleteDirectory($extractFolder);
            return;
        }

        Lesson::create([
            'class_id' => $this->classId,
            'title' => $this->title,
            'type' => 'html_game',
            'content_url' => $extractFolder,
            'description' => $this->description,
        ]);

        $this->reset(['title', 'description', 'file', 'showForm']);
    }

    public function delete($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        Storage::disk('local')->deleteDirectory($lesson->content_url);
        $lesson->delete();
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#061C32]">بازی‌های آموزشی</h2>
        <button wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
                class="inline-flex items-center gap-2 rounded-lg bg-[#E9C72A] px-4 py-2 text-sm font-bold text-[#061C32] hover:brightness-95">
            <x-icon name="plus" class="h-4 w-4" />
            افزودن بازی
        </button>
    </div>

    @if ($showForm)
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm text-slate-600">عنوان بازی</label>
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
                    <label class="block mb-1 text-sm text-slate-600">فایل zip بازی (باید index.html داخلش باشه)</label>
                    <input type="file" wire:model="file" accept=".zip" class="w-full text-sm">
                    <div wire:loading wire:target="file" class="mt-1 text-xs text-slate-400">در حال آپلود...</div>
                    @error('file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-[#061C32] px-4 py-2 text-sm font-medium text-white hover:brightness-110">ذخیره</button>
                    <button type="button" wire:click="$set('showForm', false)" class="rounded-lg px-4 py-2 text-sm text-slate-500 hover:bg-slate-100">انصراف</button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @forelse ($this->lessons() as $lesson)
            <div wire:key="game-{{ $lesson->id }}"
                 class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50/70">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-[#061C32] truncate">{{ $lesson->title }}</div>
                    @if($lesson->description)
                        <div class="text-sm text-slate-500 truncate">{{ $lesson->description }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <a href="{{ route('lesson.game', $lesson->id) }}" target="_blank"
                       class="rounded-lg px-3 py-1.5 text-sm text-[#061C32] hover:bg-[#061C32]/5">اجرا</a>
                    <button wire:click="delete({{ $lesson->id }})" wire:confirm="مطمئنی می‌خوای حذف کنی؟"
                            class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">حذف</button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">هنوز بازی‌ای اضافه نشده.</p>
            </div>
        @endforelse
    </div>
</div>