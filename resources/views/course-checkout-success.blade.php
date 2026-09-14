<x-layouts.guest>
    <div class="mx-auto max-w-lg px-4 py-12 sm:py-16">

        {{-- ═══════════ کارت تأییدیه ═══════════ --}}
        <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

            {{-- هدر موفقیت --}}
            <div class="flex flex-col items-center gap-4 border-b border-[#f0e7dc] bg-gradient-to-b from-[#e2f6e7]/40 to-transparent px-6 py-10 text-center">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-gradient-to-br from-[#4ade80] to-[#16a34a] text-white shadow-lg ring-8 ring-[#e2f6e7]/60">
                    <x-icon name="check" class="h-10 w-10" />
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold">پرداخت شما ثبت شد</h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        درخواست شما با موفقیت ثبت شد و در صف بررسی قرار گرفت.
                        <br>
                        پس از تأیید ادمین، دسترسی شما به دوره فعال می‌شود.
                    </p>
                </div>
            </div>

            {{-- کد پیگیری --}}
            <div
                x-data="{
                    copied: false,
                    async copy() {
                        try {
                            await navigator.clipboard.writeText('{{ $order->code }}');
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2500);
                        } catch (e) {}
                    }
                }"
                class="p-6"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-500">کد پیگیری شما</p>
                    <button
                        type="button"
                        @click="copy()"
                        class="inline-flex items-center gap-1 rounded-lg bg-[#0b1826] px-3 py-1.5 text-xs font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <span x-show="!copied" class="inline-flex items-center gap-1">
                            <x-icon name="paper-clip" class="h-3.5 w-3.5" />
                            کپی کد
                        </span>
                        <span x-show="copied" x-cloak class="inline-flex items-center gap-1 text-[#16a34a]">
                            <x-icon name="check-circle" class="h-3.5 w-3.5" />
                            کپی شد
                        </span>
                    </button>
                </div>

                <div class="select-all rounded-xl border-2 border-dashed border-[#d7a92b]/50 bg-gradient-to-l from-[#faf0d2]/40 to-white px-4 py-4 text-center">
                    <p class="font-mono text-2xl font-extrabold tracking-widest text-[#b98a10]" dir="ltr">
                        {{ $order->code }}
                    </p>
                </div>

                <div class="mt-3 flex items-start gap-2 rounded-lg bg-[#faf0d2]/50 px-3 py-2 text-xs text-[#b98a10]">
                    <x-icon name="sparkles" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <p>
                        این کد را حتماً <b>یادداشت یا اسکرین‌شات</b> بگیرید — برای ورود به دوره به آن نیاز دارید.
                    </p>
                </div>
            </div>

            {{-- مراحل بعدی --}}
            <div class="border-t border-[#f0e7dc] bg-[#faf5ec]/40 px-6 py-5">
                <p class="mb-4 text-xs font-bold text-slate-500">مراحل بعدی:</p>

                <div class="space-y-3">
                    {{-- مرحله ۱: انجام شده --}}
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#e2f6e7] text-[#16a34a]">
                            <x-icon name="check" class="h-4 w-4" />
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-700">ثبت درخواست پرداخت</p>
                            <p class="text-[10px] text-slate-400">انجام شد</p>
                        </div>
                    </div>

                    {{-- خط اتصال --}}
                    <div class="mr-4 h-4 w-px bg-[#e0d4bf]"></div>

                    {{-- مرحله ۲: در انتظار --}}
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#faf0d2] text-[#b98a10]">
                            <x-icon name="clock" class="h-4 w-4" />
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-700">بررسی و تأیید ادمین</p>
                            <p class="text-[10px] text-slate-400">معمولاً کمتر از ۲۴ ساعت</p>
                        </div>
                        <span class="rounded-full bg-[#faf0d2] px-2.5 py-1 text-[10px] font-bold text-[#b98a10]">
                            در انتظار
                        </span>
                    </div>

                    {{-- خط اتصال --}}
                    <div class="mr-4 h-4 w-px bg-[#e0d4bf]"></div>

                    {{-- مرحله ۳: آینده --}}
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#f0e7dc] text-slate-400">
                            <x-icon name="academic-cap" class="h-4 w-4" />
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-500">فعال‌سازی دسترسی به دوره</p>
                            <p class="text-[10px] text-slate-400">با کد پیگیری وارد شوید</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="border-t border-[#f0e7dc] p-6">
                <a
                    href="{{ route('course.access.form') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="lock" class="h-4 w-4" />
                    رفتن به صفحه «ورود به دوره»
                    <x-icon name="arrow-left" class="h-4 w-4" />
                </a>
                <p class="mt-3 text-center text-[10px] text-slate-400">
                    در صورت تأیید نشدن تا ۲۴ ساعت، با پشتیبانی تماس بگیرید.
                </p>
            </div>
        </div>

    </div>
</x-layouts.guest>