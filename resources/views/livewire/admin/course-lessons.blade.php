<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $courseId;
    public string $type = 'note';
    public string $title = '';
    public string $description = '';
    public $file;
    public bool $showForm = false;

    public function mount($courseId)
    {
        $this->courseId = $courseId;
    }

    public function lessons()
    {
        return CourseLesson::where('course_id', $this->courseId)->latest()->get();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => $this->type === 'note'
                ? 'required|file|mimes:pdf|max:51200'
                : 'required|file|mimes:zip|max:102400',
        ]);

        if ($this->type === 'note') {
            $path = $this->file->store('course-lessons/notes', 'local');
        } else {
            $zipPath = $this->file->store('tmp', 'local');
            $fullZipPath = Storage::disk('local')->path($zipPath);

            $extractFolder = 'course-lessons/games/' . uniqid();
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

            Storage::disk('local')->delete($zipPath);

            if (! file_exists($fullExtractPath . '/index.html')) {
                $this->addError('file', 'فایل zip باید یک index.html داشته باشد.');
                Storage::deleteDirectory($extractFolder);
                return;
            }

            $path = $extractFolder;
        }

        CourseLesson::create([
            'course_id' => $this->courseId,
            'title' => $this->title,
            'type' => $this->type,
            'content_url' => $path,
            'description' => $this->description,
        ]);

        $this->reset(['title', 'description', 'file', 'showForm']);
    }

    public function delete($id)
    {
        $lesson = CourseLesson::findOrFail($id);

        if ($lesson->type === 'note') {
            Storage::disk('local')->delete($lesson->content_url);
        } else {
            Storage::disk('local')->deleteDirectory($lesson->content_url);
        }

        $lesson->delete();
    }

    /** تم رنگی بر اساس نوع محتوا */
    public function tone(): array
    {
        return $this->type === 'note'
            ? [
                'bg'     => 'bg-[#dff4f0]',
                'text'   => 'text-[#0d9488]',
                'icon'   => 'book-open',
                'label'  => 'جزوه (PDF)',
                'accept' => '.pdf',
                'limits' => 'فقط PDF — حداکثر ۵۰ مگابایت',
                'badge'  => 'جزوه',
            ]
            : [
                'bg'     => 'bg-[#f3e8fa]',
                'text'   => 'text-[#9333ea]',
                'icon'   => 'sparkles',
                'label'  => 'بازی HTML (zip)',
                'accept' => '.zip',
                'limits' => 'فقط zip — حداکثر ۱۰۰ مگابایت',
                'badge'  => 'بازی',
            ];
    }

    /** آمار تفکیکی محتواها */
    public function counts(): array
    {
        $lessons = $this->lessons();

        return [
            'total' => $lessons->count(),
            'notes' => $lessons->where('type', 'note')->count(),
            'games' => $lessons->where('type', 'html_game')->count(),
        ];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }

    /** رنگ آواتار هر محتوا */
    public function contentTone($type): array
    {
        return $type === 'note'
            ? ['bg' => 'bg-[#dff4f0]', 'text' => 'text-[#0d9488]', 'icon' => 'book-open', 'badgeClass' => 'bg-[#dff4f0] text-[#0d9488]']
            : ['bg' => 'bg-[#f3e8fa]', 'text' => 'text-[#9333ea]', 'icon' => 'sparkles', 'badgeClass' => 'bg-[#f3e8fa] text-[#9333ea]'];
    }
}; ?>

@php
    $tone = $this->tone();
    $counts = $this->counts();
@endphp

<div class="space-y-6">

    {{-- ═══════════ بازگشت ═══════════ --}}
    <a
        href="{{ route('admin.dashboard') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-right" class="h-4 w-4" />
        بازگشت به لیست دوره‌ها
    </a>

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="academic-cap" class="h-6 w-6" />
            </span>
            <div>
                <h1 class="text-xl font-extrabold">محتوای دوره</h1>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($counts['total']) }} محتوا
                    @if ($counts['notes'] > 0)
                        · <span class="font-bold text-[#0d9488]">{{ $this->fa($counts['notes']) }} جزوه</span>
                    @endif
                    @if ($counts['games'] > 0)
                        · <span class="font-bold text-[#9333ea]">{{ $this->fa($counts['games']) }} بازی</span>
                    @endif
                </p>
            </div>
        </div>

        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
            {{ $showForm ? 'بستن فرم' : 'افزودن محتوا' }}
        </button>
    </div>

    {{-- ═══════════ فرم افزودن محتوا ═══════════ --}}
    @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="save" class="space-y-5">

                {{-- نوع محتوا (Radio Cards) --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">نوع محتوا</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ([
                            'note' => ['جزوه (PDF)', 'book-open', 'bg-[#dff4f0] text-[#0d9488]', 'فایل PDF قابل نمایش در مرورگر'],
                            'html_game' => ['بازی HTML (zip)', 'sparkles', 'bg-[#f3e8fa] text-[#9333ea]', 'فایل zip شامل HTML، CSS، JS'],
                        ] as $value => [$label, $icon, $iconClass, $desc])
                            <label wire:key="type-{{ $value }}" class="cursor-pointer">
                                <input type="radio" wire:model.live="type" value="{{ $value }}" class="peer sr-only">
                                <span class="flex h-full flex-col gap-2 rounded-xl border border-[#eee4d9] bg-white p-4 shadow-sm transition peer-checked:border-[#d7a92b] peer-checked:bg-[#faf5ec] peer-checked:ring-2 peer-checked:ring-[#d7a92b]/20 hover:border-[#d7a92b]/40">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl {{ $iconClass }}">
                                        <x-icon name="{{ $icon }}" class="h-5 w-5" />
                                    </span>
                                    <span class="text-sm font-extrabold">{{ $label }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $desc }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('type') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- عنوان --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">عنوان</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="{{ $type === 'note' ? 'مثلاً: خلاصه فصل ۱' : 'مثلاً: بازی کلمات - سطح ۱' }}"
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
                        placeholder="توضیح کوتاه درباره این محتوا..."
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    ></textarea>
                    @error('description') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- فایل --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">
                        {{ $type === 'note' ? 'فایل PDF' : 'فایل zip بازی' }}
                        <span class="mt-0.5 block text-xs font-normal text-slate-400">{{ $tone['limits'] }}</span>
                    </label>

                    <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white px-4 py-6 text-center transition hover:border-[#d7a92b]/60 hover:bg-[#faf5ec]">
                        <input
                            type="file"
                            wire:model="file"
                            accept="{{ $tone['accept'] }}"
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
                            <span class="text-sm font-bold text-slate-600">انتخاب فایل</span>
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

                    {{-- راهنمای ساختار zip (فقط برای بازی) --}}
                    @if ($type === 'html_game')
                        <div class="mt-2 flex items-start gap-2 rounded-lg bg-[#faf0d2]/50 px-3 py-2 text-xs text-[#b98a10]">
                            <x-icon name="sparkles" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            <p>
                                <b>ساختار zip:</b> همه فایل‌های بازی (HTML، CSS، JS، تصاویر) باید کنار
                                <code dir="ltr" class="rounded bg-white/70 px-1 py-0.5 font-mono text-[10px]">index.html</code>
                                باشن.
                            </p>
                        </div>
                    @endif
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
                            ذخیره محتوا
                        </span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            {{ $type === 'html_game' ? 'در حال استخراج و ذخیره...' : 'در حال ذخیره...' }}
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

    {{-- ═══════════ لیست محتوا ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->lessons() as $i => $lesson)
            @php $contentTone = $this->contentTone($lesson->type); @endphp

            <div
                wire:key="lesson-{{ $lesson->id }}"
                class="flex flex-col gap-3 border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:items-center sm:justify-between sm:gap-4"
            >
                {{-- اطلاعات --}}
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    {{-- شماره + آواتار --}}
                    <div class="flex shrink-0 flex-col items-center gap-1.5">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-[#faf0d2] text-[10px] font-extrabold text-[#b98a10]">
                            {{ $this->fa($i + 1) }}
                        </span>
                        <span class="grid h-11 w-11 place-items-center rounded-xl {{ $contentTone['bg'] }} {{ $contentTone['text'] }}">
                            <x-icon name="{{ $contentTone['icon'] }}" class="h-5 w-5" />
                        </span>
                    </div>

                    {{-- متن --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-bold">{{ $lesson->title }}</span>
                            <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $contentTone['badgeClass'] }}">
                                {{ $lesson->type === 'note' ? 'جزوه' : 'بازی' }}
                            </span>
                        </div>

                        @if ($lesson->description)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $lesson->description }}</p>
                        @endif

                        <p class="mt-1.5 text-[10px] text-slate-400">
                            اضافه شده: {{ $lesson->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                {{-- عملیات --}}
                <button
                    wire:click="delete({{ $lesson->id }})"
                    wire:confirm="مطمئنی می‌خوای این محتوا رو حذف کنی؟"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-bold text-red-600 transition hover:bg-red-50"
                >
                    <x-icon name="trash" class="h-4 w-4" />
                    <span class="hidden sm:inline">حذف</span>
                </button>
            </div>
        @empty
            {{-- حالت خالی --}}
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                    <x-icon name="academic-cap" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز محتوایی اضافه نشده</p>
                    <p class="mt-1 text-sm text-slate-500">
                        با یک جزوه PDF یا بازی HTML، اولین محتوای دوره رو بساز.
                    </p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت اولین محتوا
                </button>
            </div>
        @endforelse
    </div>

</div>