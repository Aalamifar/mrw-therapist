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
    public string $type;

    public string $title = '';
    public string $description = '';
    public $file;

    public bool $showForm = false;

    public function mount($classId, $type)
    {
        $this->classId = $this->ownedClass($classId)->id;
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
        $lesson = Lesson::where('class_id', $this->classId)->findOrFail($lessonId);
        Storage::disk('local')->delete($lesson->content_url);
        $lesson->delete();
    }

    /** تم رنگی بر اساس نوع محتوا */
    public function tone(): array
    {
        return $this->type === 'video'
            ? [
                'bg'     => 'bg-[#fdeaea]',
                'text'   => 'text-[#dc2626]',
                'accent' => 'bg-[#dc2626]',
                'icon'   => 'video',
                'label'  => 'فیلم‌های آموزشی',
                'add'    => 'افزودن فیلم',
                'empty'  => 'هنوز فیلمی اضافه نشده',
                'emptyHint' => 'با یک فایل ویدیویی، اولین فیلم آموزشی رو اضافه کن.',
                'first'  => 'ساخت اولین فیلم',
                'badge'  => 'ویدیو',
            ]
            : [
                'bg'     => 'bg-[#dff4f0]',
                'text'   => 'text-[#0d9488]',
                'accent' => 'bg-[#0d9488]',
                'icon'   => 'book-open',
                'label'  => 'جزوه و کتاب',
                'add'    => 'افزودن جزوه',
                'empty'  => 'هنوز جزوه‌ای اضافه نشده',
                'emptyHint' => 'با یک فایل PDF، اولین جزوه رو اضافه کن.',
                'first'  => 'ساخت اولین جزوه',
                'badge'  => 'PDF',
            ];
    }

    /** محدودیت‌های فایل بر اساس نوع */
    public function fileLimits(): string
    {
        return $this->type === 'video'
            ? 'MP4، WebM، MOV — حداکثر ۵۰۰ مگابایت'
            : 'فقط PDF — حداکثر ۵۰ مگابایت';
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

@php $tone = $this->tone(); @endphp

<div class="space-y-5">

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }}">
                <x-icon name="{{ $tone['icon'] }}" class="h-6 w-6" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">{{ $tone['label'] }}</h2>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($this->lessons()->count()) }} آیتم ثبت شده
                </p>
            </div>
        </div>

        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
            {{ $showForm ? 'بستن فرم' : $tone['add'] }}
        </button>
    </div>

    {{-- ═══════════ فرم افزودن ═══════════ --}}
    @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="save" class="space-y-4">

                {{-- عنوان --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">عنوان</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="{{ $type === 'video' ? 'مثلاً: جلسه اول - مقدمات' : 'مثلاً: خلاصه فصل ۱' }}"
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
                        placeholder="توضیح کوتاه درباره این {{ $type === 'video' ? 'فیلم' : 'جزوه' }}..."
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    ></textarea>
                    @error('description') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- فایل --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">
                        {{ $type === 'video' ? 'فایل ویدیو' : 'فایل PDF' }}
                    </label>

                    <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white px-4 py-6 text-center transition hover:border-[#d7a92b]/60 hover:bg-[#faf5ec]">
                        <input
                            type="file"
                            wire:model="file"
                            accept="{{ $type === 'video' ? 'video/mp4,video/webm,video/quicktime' : '.pdf' }}"
                            class="sr-only"
                        >

                        @if ($file)
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-[#e2f6e7] text-[#16a34a]">
                                <x-icon name="check-circle" class="h-5 w-5" />
                            </span>
                            <span class="max-w-full truncate text-sm font-bold text-[#16a34a]">{{ $file->getClientOriginalName() }}</span>
                            <span class="text-[10px] text-slate-400">
                                {{ number_format($file->getSize() / 1024 / 1024, 2) }} مگابایت
                            </span>
                        @else
                            <span class="grid h-10 w-10 place-items-center rounded-full {{ $tone['bg'] }} {{ $tone['text'] }}">
                                <x-icon name="arrow-down-tray" class="h-5 w-5" />
                            </span>
                            <span class="text-sm font-bold text-slate-600">
                                {{ $type === 'video' ? 'انتخاب ویدیو' : 'انتخاب PDF' }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $this->fileLimits() }}</span>
                        @endif
                    </label>

                    {{-- loading آپلود --}}
                    <div wire:loading wire:target="file" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                        <svg class="h-3.5 w-3.5 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        در حال آپلود...
                    </div>

                    @error('file') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
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
                            ذخیره
                        </span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            در حال ذخیره...
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

    {{-- ═══════════ لیست ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->lessons() as $lesson)
            <div
                wire:key="lesson-{{ $lesson->id }}"
                class="flex flex-col gap-3 border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    {{-- آواتار --}}
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $tone['bg'] }} {{ $tone['text'] }}">
                        <x-icon name="{{ $tone['icon'] }}" class="h-5 w-5" />
                    </span>

                    {{-- اطلاعات --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-bold">{{ $lesson->title }}</span>
                            <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $tone['bg'] }} {{ $tone['text'] }}">
                                {{ $tone['badge'] }}
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
                        href="{{ route('lesson.file', $lesson->id) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-3 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        <span>مشاهده</span>
                    </a>
                    <button
                        wire:click="delete({{ $lesson->id }})"
                        wire:confirm="مطمئنی می‌خوای این {{ $type === 'video' ? 'فیلم' : 'جزوه' }} رو حذف کنی؟"
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
                <span class="grid h-16 w-16 place-items-center rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }}">
                    <x-icon name="{{ $tone['icon'] }}" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">{{ $tone['empty'] }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $tone['emptyHint'] }}</p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ $tone['first'] }}
                </button>
            </div>
        @endforelse
    </div>

</div>
