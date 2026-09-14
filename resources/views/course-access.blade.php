<x-layouts.guest>
    <div class="mx-auto max-w-md px-4 py-12 sm:py-16">

        {{-- ═══════════ کارت ورود ═══════════ --}}
        <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

            {{-- هدر --}}
            <div class="flex flex-col items-center gap-4 border-b border-[#f0e7dc] bg-gradient-to-b from-[#faf0d2]/40 to-transparent px-6 py-10 text-center">
                <span class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-lg ring-8 ring-[#faf0d2]/60">
                    <x-icon name="lock" class="h-10 w-10" />
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold">ورود به دوره</h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        کد پیگیری خود را وارد کنید تا به محتوای دوره دسترسی پیدا کنید.
                    </p>
                </div>
            </div>

            {{-- فرم --}}
            <form method="POST" action="{{ route('course.access.check') }}" class="space-y-5 p-6 sm:p-8">
                @csrf

                {{-- پیام خطا --}}
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-[#fdeaea] p-4">
                        <div class="flex items-start gap-2">
                            <x-icon name="x-mark" class="mt-0.5 h-4 w-4 shrink-0 text-[#dc2626]" />
                            <div class="space-y-1 text-sm text-[#dc2626]">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- کد دوره --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">کد پیگیری دوره</label>

                    <div class="relative">
                        <x-icon name="check-circle" class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#b98a10]" />
                        <input
                            type="text"
                            name="code"
                            value="{{ old('code') }}"
                            placeholder="مثلاً: MRW-XXXX-XXXX"
                            dir="ltr"
                            autocomplete="off"
                            class="w-full rounded-xl border-2 border-[#eee4d9] bg-white py-4 pl-4 pr-11 text-center font-mono text-xl font-extrabold tracking-widest text-[#b98a10] shadow-sm outline-none transition placeholder:text-sm placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 focus:border-[#d7a92b]/60 focus:ring-4 focus:ring-[#d7a92b]/15"
                            required
                        >
                    </div>

                    <p class="mt-2 text-center text-[10px] text-slate-400">
                        کد را دقیقاً همان‌طور که دریافت کرده‌اید وارد کنید.
                    </p>
                </div>

                {{-- دکمه ورود --}}
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="lock" class="h-4 w-4" />
                    ورود به دوره
                    <x-icon name="arrow-left" class="h-4 w-4" />
                </button>
            </form>

            {{-- پاورقی --}}
            <div class="border-t border-[#f0e7dc] bg-[#faf5ec]/40 px-6 py-4 text-center">
                <p class="text-xs text-slate-500">
                    کد پیگیری ندارید؟
                    <a
                        href="{{ route('courses.index') }}"
                        class="font-bold text-[#b98a10] underline underline-offset-4 transition hover:text-[#d7a92b]"
                    >
                        مشاهده دوره‌ها و ثبت‌نام
                    </a>
                </p>
            </div>
        </div>

        {{-- نکته راهنما --}}
        <div class="mt-5 flex items-start gap-2 rounded-xl border border-[#eee4d9] bg-[#fdfbf8]/60 px-4 py-3 text-xs text-slate-500 shadow-sm">
            <x-icon name="sparkles" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[#d7a92b]" />
            <p>
                کد پیگیری پس از <b>تأیید پرداخت توسط ادمین</b> برای شما فعال می‌شود. اگر پرداخت شما هنوز تأیید نشده، کمی صبر کنید.
            </p>
        </div>

    </div>
</x-layouts.guest>