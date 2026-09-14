<x-layouts.guest>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- ═══════════ هدر دوره (Hero) ═══════════ --}}
        <div class="mb-8 overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

            {{-- تصویر دوره --}}
            @if ($course->image)
                <div class="relative aspect-video w-full overflow-hidden bg-[#faf0d2]/30">
                    <img
                        src="{{ asset('storage/' . $course->image) }}"
                        alt="{{ $course->title }}"
                        class="h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                    {{-- عنوان روی تصویر --}}
                    <div class="absolute bottom-0 right-0 left-0 p-5 sm:p-6">
                        <h1 class="text-2xl font-extrabold text-white drop-shadow-lg sm:text-3xl">
                            {{ $course->title }}
                        </h1>
                    </div>
                </div>
            @endif

            {{-- اطلاعات دوره --}}
            <div class="p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <div class="flex items-start gap-4">
                        @unless ($course->image)
                            <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                                <x-icon name="academic-cap" class="h-8 w-8" />
                            </span>
                        @endunless

                        <div class="min-w-0 flex-1">
                            @if ($course->image)
                                <h1 class="mb-2 text-2xl font-extrabold">{{ $course->title }}</h1>
                            @endif

                            @if ($course->description)
                                <p class="text-sm leading-relaxed text-slate-600">{{ $course->description }}</p>
                            @else
                                <p class="text-sm italic text-slate-400">توضیحاتی برای این دوره ثبت نشده است.</p>
                            @endif
                        </div>
                    </div>

                    {{-- آمار --}}
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-[#faf0d2] px-3 py-2 text-xs font-bold text-[#b98a10]">
                            <x-icon name="academic-cap" class="h-4 w-4" />
                            {{ number_format($lessons->count()) }} محتوا
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-[#e2f6e7] px-3 py-2 text-xs font-bold text-[#16a34a]">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            دسترسی فعال
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ لیست محتوا ═══════════ --}}
        <div class="space-y-5">

            @forelse ($lessons as $i => $lesson)
                @php
                    $isNote = $lesson->type === 'note';
                    $tone = $isNote
                        ? ['bg' => 'bg-[#dff4f0]', 'text' => 'text-[#0d9488]', 'icon' => 'book-open', 'badge' => 'جزوه PDF']
                        : ['bg' => 'bg-[#f3e8fa]', 'text' => 'text-[#9333ea]', 'icon' => 'sparkles', 'badge' => 'بازی HTML'];
                @endphp

                <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm transition hover:border-[#d7a92b]/40 hover:shadow-md">

                    {{-- هدرس --}}
                    <div class="flex items-start gap-3 border-b border-[#f0e7dc] p-5">
                        {{-- شماره + آواتار --}}
                        <div class="flex shrink-0 flex-col items-center gap-1.5">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-[#faf0d2] text-xs font-extrabold text-[#b98a10]">
                                {{ $i + 1 }}
                            </span>
                            <span class="grid h-11 w-11 place-items-center rounded-xl {{ $tone['bg'] }} {{ $tone['text'] }}">
                                <x-icon name="{{ $tone['icon'] }}" class="h-5 w-5" />
                            </span>
                        </div>

                        {{-- عنوان + badge --}}
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

                    {{-- محتوا --}}
                    <div class="p-5">

                        {{-- جزوه PDF --}}
                        @if ($isNote)
                            <div
                                class="overflow-hidden rounded-xl border border-[#eee4d9] bg-white shadow-sm"
                                oncontextmenu="return false;"
                            >
                                <iframe
                                    src="{{ route('course.file', [$course->id, $lesson->id]) }}#toolbar=0&navpanes=0&scrollbar=0"
                                    class="h-[500px] w-full sm:h-[600px]"
                                    title="{{ $lesson->title }}"
                                ></iframe>
                            </div>

                            <div class="mt-2 flex items-center gap-1.5 text-[10px] text-slate-400">
                                <x-icon name="lock" class="h-3 w-3" />
                                محتوا حفاظت شده است — فقط قابل مشاهده
                            </div>
                        @endif

                        {{-- بازی HTML --}}
                        @if ($lesson->type === 'html_game')
                            <div class="flex flex-col items-center gap-4 rounded-xl border-2 border-dashed border-[#e0d4bf] bg-[#faf5ec]/50 p-8 text-center">
                                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                                    <x-icon name="sparkles" class="h-8 w-8" />
                                </span>
                                <div>
                                    <p class="font-bold">بازی آموزشی آماده است</p>
                                    <p class="mt-1 text-xs text-slate-500">با کلیک روی دکمه، بازی در تب جدید باز می‌شود.</p>
                                </div>
                                <a
                                    href="{{ route('course.file', [$course->id, $lesson->id]) }}"
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
                <div class="flex flex-col items-center gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] px-5 py-20 text-center shadow-sm">
                    <span class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                        <x-icon name="academic-cap" class="h-10 w-10" />
                    </span>
                    <div>
                        <p class="text-lg font-extrabold">هنوز محتوایی اضافه نشده</p>
                        <p class="mt-2 text-sm text-slate-500">
                            به‌زودی محتوای این دوره توسط مربی بارگذاری خواهد شد.
                        </p>
                    </div>
                    <a
                        href="{{ route('courses.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#eee4d9] bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-[#faf5ec]"
                    >
                        <x-icon name="arrow-right" class="h-4 w-4" />
                        بازگشت به لیست دوره‌ها
                    </a>
                </div>
            @endforelse

        </div>

    </div>
</x-layouts.guest>