<?php

use Livewire\Volt\Component;
use App\Models\Lesson;
use App\Support\EnsuresStudentClassAccess;

new class extends Component
{
    use EnsuresStudentClassAccess;
    public $classId;
    public string $type;

    public function mount($classId, $type)
    {
        $this->classId = $this->enrolledClass($classId)->id;
        $this->type = $type;
    }

    public function lessons()
    {
        return Lesson::where('class_id', $this->classId)
            ->where('type', $this->type)
            ->latest()
            ->get();
    }

    /** تم رنگی بر اساس نوع محتوا */
    public function tone(): array
    {
        return match ($this->type) {
            'video' => [
                'bg'     => 'bg-[#fdeaea]',
                'text'   => 'text-[#dc2626]',
                'icon'   => 'video',
                'label'  => 'فیلم‌های آموزشی',
                'empty'  => 'هنوز فیلمی اضافه نشده',
                'emptyHint' => 'مربی هنوز فیلمی برای این کلاس آپلود نکرده است.',
                'badge'  => 'ویدیو',
            ],
            'note' => [
                'bg'     => 'bg-[#dff4f0]',
                'text'   => 'text-[#0d9488]',
                'icon'   => 'book-open',
                'label'  => 'جزوه و کتاب',
                'empty'  => 'هنوز جزوه‌ای اضافه نشده',
                'emptyHint' => 'مربی هنوز جزوه‌ای برای این کلاس آپلود نکرده است.',
                'badge'  => 'PDF',
            ],
            'html_game' => [
                'bg'     => 'bg-[#f3e8fa]',
                'text'   => 'text-[#9333ea]',
                'icon'   => 'sparkles',
                'label'  => 'بازی‌های آموزشی',
                'empty'  => 'هنوز بازی‌ای اضافه نشده',
                'emptyHint' => 'مربی هنوز بازی‌ای برای این کلاس آپلود نکرده است.',
                'badge'  => 'بازی',
            ],
            default => [
                'bg'     => 'bg-[#faf0d2]',
                'text'   => 'text-[#b98a10]',
                'icon'   => 'sparkles',
                'label'  => 'محتوا',
                'empty'  => 'هنوز محتوایی اضافه نشده',
                'emptyHint' => '',
                'badge'  => 'محتوا',
            ],
        };
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

@php $tone = $this->tone(); @endphp

<div class="space-y-6">

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex items-center gap-3">
        <span class="grid h-12 w-12 place-items-center rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }}">
            <x-icon name="{{ $tone['icon'] }}" class="h-6 w-6" />
        </span>
        <div>
            <h2 class="text-xl font-extrabold">{{ $tone['label'] }}</h2>
            <p class="text-xs text-slate-500">
                {{ $this->fa($this->lessons()->count()) }} آیتم در دسترس
            </p>
        </div>
    </div>

    {{-- ═══════════ لیست محتوا ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

        @forelse ($this->lessons() as $i => $lesson)
            <div
                wire:key="lesson-{{ $lesson->id }}"
                class="border-b border-[#f0e7dc] p-5 transition last:border-b-0 hover:bg-[#faf5ec] sm:p-6"
            >
                {{-- هدر درس --}}
                <div class="flex items-start gap-3">
                    {{-- شماره + آواتار --}}
                    <div class="flex shrink-0 flex-col items-center gap-1.5">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-[#faf0d2] text-xs font-extrabold text-[#b98a10]">
                            {{ $this->fa($i + 1) }}
                        </span>
                        <span class="grid h-10 w-10 place-items-center rounded-xl {{ $tone['bg'] }} {{ $tone['text'] }}">
                            <x-icon name="{{ $tone['icon'] }}" class="h-5 w-5" />
                        </span>
                    </div>

                    {{-- عنوان + توضیح + badge --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-extrabold">{{ $lesson->title }}</h3>
                            <span class="rounded-md px-2 py-0.5 text-[10px] font-bold {{ $tone['bg'] }} {{ $tone['text'] }}">
                                {{ $tone['badge'] }}
                            </span>
                        </div>

                        @if ($lesson->description)
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $lesson->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- ═══════════ پخش‌کننده ═══════════ --}}
                <div class="mt-4">

                    {{-- فیلم --}}
                    @if ($lesson->type === 'video')
                        <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-black shadow-sm">
                            <video
                                controls
                                controlsList="nodownload"
                                oncontextmenu="return false;"
                                class="w-full"
                                preload="metadata"
                                poster=""
                            >
                                <source src="{{ route('lesson.file', $lesson->id) }}" type="video/mp4">
                                مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
                            </video>
                        </div>

                        {{-- نکته محافظت --}}
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] text-slate-400">
                            <x-icon name="lock" class="h-3 w-3" />
                            دانلود غیرفعال است — فقط پخش آنلاین
                        </div>
                    @endif

                    {{-- جزوه PDF --}}
                    @if ($lesson->type === 'note')
                        <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-white shadow-sm" oncontextmenu="return false;">
                            <iframe
                                src="{{ route('lesson.file', $lesson->id) }}#toolbar=0&navpanes=0&scrollbar=0"
                                class="h-[500px] w-full sm:h-[600px]"
                                title="{{ $lesson->title }}"
                            ></iframe>
                        </div>

                        {{-- نکته محافظت --}}
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] text-slate-400">
                            <x-icon name="lock" class="h-3 w-3" />
                            محتوا حفاظت شده است — فقط قابل مشاهده
                        </div>
                    @endif

                    {{-- بازی HTML --}}
                    @if ($lesson->type === 'html_game')
                        <div class="flex flex-col items-center gap-4 rounded-2xl border-2 border-dashed border-[#e0d4bf] bg-[#faf5ec]/50 p-8 text-center">
                            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                                <x-icon name="sparkles" class="h-8 w-8" />
                            </span>
                            <div>
                                <p class="font-bold">بازی آموزشی آماده است</p>
                                <p class="mt-1 text-xs text-slate-500">با کلیک روی دکمه، بازی در تب جدید باز می‌شود.</p>
                            </div>
                            <a
                                href="{{ route('lesson.game', $lesson->id) }}"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-5 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <x-icon name="sparkles" class="h-4 w-4" />
                                شروع بازی
                                <x-icon name="arrow-left" class="h-4 w-4" />
                            </a>
                        </div>
                    @endif
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
                    @if ($tone['emptyHint'])
                        <p class="mt-1 text-sm text-slate-500">{{ $tone['emptyHint'] }}</p>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

</div>
