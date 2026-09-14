<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Course;

new #[Layout('components.layouts.guest')] class extends Component
{
    public function courses()
    {
        return Course::withCount('lessons')->latest()->get();
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int|string $number): string
    {
        return str_replace(
            range('0', '9'),
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            (string) $number
        );
    }

    /** فرمت قیمت به تومان با جداکننده هزارگان فارسی */
    public function formatPrice($price): string
    {
        return $this->fa(number_format((int) $price));
    }
}; ?>

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- ═══════════ هدر ═══════════ --}}
    <div class="mb-8 flex items-center gap-3">
        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
            <x-icon name="academic-cap" class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-2xl font-extrabold sm:text-3xl">دوره‌های آموزشی</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $this->fa($this->courses()->count()) }} دوره برای شروع یادگیری
            </p>
        </div>
    </div>

    {{-- ═══════════ گرید دوره‌ها ═══════════ --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

        @forelse ($this->courses() as $course)
            <a
                href="{{ route('course.checkout', $course->id) }}"
                class="group flex flex-col overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm transition hover:-translate-y-1 hover:border-[#d7a92b]/40 hover:shadow-lg"
            >
                {{-- تصویر دوره (یا placeholder) --}}
                <div class="relative aspect-video w-full overflow-hidden bg-[#faf0d2]/30">
                    @if ($course->image)
                        <img
                            src="{{ asset('storage/' . $course->image) }}"
                            alt="{{ $course->title }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        >
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#faf0d2]/40 to-[#fdfbf8]">
                            <span class="grid h-20 w-20 place-items-center rounded-2xl bg-[#fdfbf8] text-[#b98a10] shadow-sm">
                                <x-icon name="academic-cap" class="h-10 w-10" />
                            </span>
                        </div>
                    @endif

                    {{-- Badge قیمت --}}
                    @if ($course->price > 0)
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-3 py-1.5 text-xs font-extrabold text-[#0b1826] shadow-md">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            {{ $this->formatPrice($course->price) }}
                        </span>
                    @else
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-[#e2f6e7] px-3 py-1.5 text-xs font-extrabold text-[#16a34a] shadow-sm">
                            <x-icon name="check-circle" class="h-3 w-3" />
                            رایگان
                        </span>
                    @endif

                    {{-- Badge تعداد محتوا --}}
                    @if ($course->lessons_count > 0)
                        <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-sm">
                            <x-icon name="video" class="h-3 w-3" />
                            {{ $this->fa($course->lessons_count) }} محتوا
                        </span>
                    @endif
                </div>

                {{-- محتوا --}}
                <div class="flex flex-1 flex-col p-5">
                    <h3 class="line-clamp-1 text-base font-extrabold transition group-hover:text-[#b98a10]">
                        {{ $course->title }}
                    </h3>

                    @if ($course->description)
                        <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-slate-500">
                            {{ $course->description }}
                        </p>
                    @else
                        <p class="mt-2 flex-1 text-sm italic text-slate-400">
                            توضیحاتی برای این دوره ثبت نشده است.
                        </p>
                    @endif

                    {{-- دکمه خرید --}}
                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-[#f0e7dc] pt-4">
                        <div>
                            <div class="text-[10px] text-slate-400">قیمت دوره</div>
                            <div class="mt-0.5 text-base font-extrabold text-[#b98a10]">
                                @if ($course->price > 0)
                                    {{ $this->formatPrice($course->price) }}
                                    <span class="text-xs font-bold text-slate-500">تومان</span>
                                @else
                                    <span class="text-[#16a34a]">رایگان</span>
                                @endif
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-4 py-2 text-sm font-extrabold text-[#e8c465] transition group-hover:bg-[#132840]">
                            {{ $course->price > 0 ? 'ثبت‌نام' : 'شروع دوره' }}
                            <x-icon name="arrow-left" class="h-4 w-4 transition group-hover:-translate-x-1" />
                        </span>
                    </div>
                </div>
            </a>
        @empty
            {{-- حالت خالی --}}
            <div class="col-span-full flex flex-col items-center gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] px-5 py-20 text-center shadow-sm">
                <span class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                    <x-icon name="academic-cap" class="h-10 w-10" />
                </span>
                <div>
                    <p class="text-lg font-extrabold">هنوز دوره‌ای ثبت نشده</p>
                    <p class="mt-2 text-sm text-slate-500">
                        به‌زودی دوره‌های آموزشی جدیدی به این بخش اضافه خواهند شد.
                    </p>
                </div>
            </div>
        @endforelse

    </div>

</div>