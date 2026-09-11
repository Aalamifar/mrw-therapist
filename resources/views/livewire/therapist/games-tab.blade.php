<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use WithFileUploads;
    use EnsuresTherapistClassAccess;

    public $classId;
    public string $title = '';
    public string $description = '';
    public $file;
    public bool $showForm = false;

    public function mount($classId)
    {
        $this->classId = $this->ownedClass($classId)->id;
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
            'file' => 'required|file|mimes:zip|max:102400',
        ]);

        $zipPath = $this->file->store('tmp', 'local');
        $fullZipPath = Storage::disk('local')->path($zipPath);

        $extractFolder = 'lessons/games/' . uniqid();
        $fullExtractPath = Storage::disk('local')->path($extractFolder);

        $zip = new \ZipArchive;
        if ($zip->open($fullZipPath) !== true) {
            $this->addError('file', 'فایل zip قابل باز شدن نیست.');
            Storage::disk('local')->delete($zipPath);
            return;
        }

        $uncompressedSize = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = str_replace('\\', '/', $zip->getNameIndex($index));
            $stats = $zip->statIndex($index);
            $uncompressedSize += $stats['size'] ?? 0;

            if (
                str_starts_with($entry, '/')
                || str_contains($entry, "\0")
                || preg_match('#(^|/)\.\.(/|$)#', $entry)
                || $uncompressedSize > 524288000
            ) {
                $zip->close();
                Storage::disk('local')->delete($zipPath);
                $this->addError('file', 'محتوای فایل zip معتبر یا ایمن نیست.');
                return;
            }
        }

        Storage::disk('local')->makeDirectory($extractFolder);
        $zip->extractTo($fullExtractPath);
        $zip->close();

        Storage::disk('local')->delete($zipPath);

        if (! file_exists($fullExtractPath . '/index.html')) {
            $this->addError('file', 'فایل zip باید حتماً یک index.html داخلش داشته باشه.');
            Storage::disk('local')->deleteDirectory($extractFolder);
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
        $lesson = Lesson::where('class_id', $this->classId)->findOrFail($lessonId);
        Storage::disk('local')->deleteDirectory($lesson->content_url);
        $lesson->delete();
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="space-y-5">

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#f3e8fa] text-[#9333ea]">
                <x-icon name="sparkles" class="h-6 w-6" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">بازی‌های آموزشی</h2>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($this->lessons()->count()) }} بازی ثبت شده
                </p>
            </div>
        </div>

        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
            {{ $showForm ? 'بستن فرم' : 'افزودن بازی' }}
        </button>
    </div>

    {{-- ═══════════ فرم ساخت بازی ═══════════ --}}
    @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="save" class="space-y-4">

                {{-- عنوان --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">عنوان بازی</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="مثلاً: بازی کلمات - فصل ۱"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    >
                    @error('title') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- توضیحات --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">توضیحات (اختیاری)</label>
                    <textarea
                        wire:model="description"
                        rows="2"
                        placeholder="توضیح کوتاه درباره این بازی آموزشی..."
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    ></textarea>
                    @error('description') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- فایل zip --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">
                        فایل zip بازی
                        <span class="mt-0.5 block text-xs font-normal text-slate-400">باید شامل index.html باشد</span>
                    </label>

                    <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white px-4 py-6 text-center transition hover:border-[#d7a92b]/60 hover:bg-[#faf5ec]">
                        <input type="file" wire:model="file" accept=".zip" class="sr-only">

                        @if ($file)
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-[#e2f6e7] text-[#16a34a]">
                                <x-icon name="check-circle" class="h-5 w-5" />
                            </span>
                            <span class="max-w-full truncate text-sm font-bold text-[#16a34a]">{{ $file->getClientOriginalName() }}</span>
                            <span class="text-[10px] text-slate-400">
                                {{ number_format($file->getSize() / 1024 / 1024, 2) }} مگابایت
                            </span>
                        @else
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-[#f3e8fa] text-[#9333ea]">
                                <x-icon name="arrow-down-tray" class="h-5 w-5" />
                            </span>
                            <span class="text-sm font-bold text-slate-600">انتخاب فایل zip</span>
                            <span class="text-[10px] text-slate-400">حداکثر ۱۰۰ مگابایت — فقط فرمت zip</span>
                        @endif
                    </label>

                    {{-- loading آپلود --}}
                    <div wire:loading wire:target="file" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                        <svg class="h-3.5 w-3.5 animate-spin text-[#9333ea]" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        در حال آپلود و آماده‌سازی...
                    </div>

                    @error('file') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror

                    {{-- راهنمای ساختار --}}
                    <div class="mt-2 flex items-start gap-2 rounded-lg bg-[#faf0d2]/50 px-3 py-2 text-xs text-[#b98a10]">
                        <x-icon name="sparkles" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <p>
                            <b>ساختار zip:</b> همه فایل‌های بازی (HTML، CSS، JS، تصاویر) باید کنار
                            <code dir="ltr" class="rounded bg-white/70 px-1 py-0.5 font-mono text-[10px]">index.html</code>
                            باشن.
                        </p>
                    </div>
                </div>

                {{-- دکمه‌ها --}}
                <div class="flex gap-2 pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="flex-1 rounded-xl bg-[#0b1826] px-4 py-2.5 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                            <x-icon name="check" class="h-4 w-4" />
                            ذخیره بازی
                        </span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            در حال استخراج و ذخیره...
                        </span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('showForm', false)"
                        class="rounded-xl border border-[#eee4d9] bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-[#f0e7dc]"
                    >
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- ═══════════ لیست بازی‌ها ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->lessons() as $lesson)
            <div
                wire:key="game-{{ $lesson->id }}"
                class="flex flex-col gap-3 border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    {{-- آواتار --}}
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#f3e8fa] text-[#9333ea]">
                        <x-icon name="sparkles" class="h-5 w-5" />
                    </span>

                    {{-- اطلاعات --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-bold">{{ $lesson->title }}</span>
                            <span class="rounded-md bg-[#f3e8fa] px-2 py-0.5 text-xs font-bold text-[#9333ea]">
                                بازی HTML
                            </span>
                        </div>

                        @if ($lesson->description)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $lesson->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex shrink-0 items-center gap-1">
                    <a
                        href="{{ route('lesson.game', $lesson->id) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-3 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        <span>اجرای بازی</span>
                    </a>
                    <button
                        wire:click="delete({{ $lesson->id }})"
                        wire:confirm="مطمئنی می‌خوای این بازی رو حذف کنی؟"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-bold text-red-600 transition hover:bg-red-50"
                    >
                        <x-icon name="trash" class="h-4 w-4" />
                        <span class="hidden sm:inline">حذف</span>
                    </button>
                </div>
            </div>
        @empty
            {{-- حالت خالی --}}
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#f3e8fa] text-[#9333ea]">
                    <x-icon name="sparkles" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز بازی‌ای اضافه نشده</p>
                    <p class="mt-1 text-sm text-slate-500">
                        با یک فایل zip، اولین بازی آموزشی رو اضافه کن.
                    </p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت اولین بازی
                </button>
            </div>
        @endforelse
    </div>

</div>
