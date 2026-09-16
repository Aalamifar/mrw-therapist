<x-layouts.guest>
    <div class="mx-auto max-w-lg px-4 py-8 sm:py-10">

                <div class="mb-6 flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                <x-icon name="check-circle" class="h-6 w-6" />
            </span>
            <div>
                <p class="text-xs font-bold text-[#b98a10]">تکمیل خرید</p>
                <h1 class="text-xl font-extrabold line-clamp-1">{{ $course->title }}</h1>
            </div>
        </div>

                <div class="mb-6 overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

                        <div class="flex items-center justify-between border-b border-[#f0e7dc] p-5">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#faf0d2] text-[#b98a10]">
                        <x-icon name="academic-cap" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-xs text-slate-500">مبلغ قابل پرداخت</p>
                        <p class="mt-0.5 text-xl font-extrabold text-[#b98a10]">
                            @if ($course->price > 0)
                                {{ number_format($course->price) }}
                                <span class="text-xs font-bold text-slate-500">تومان</span>
                            @else
                                <span class="text-[#16a34a]">رایگان</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

                        <div class="p-5">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-[#0b1826] text-[10px] font-extrabold text-[#e8c465]">۱</span>
                    واریز مبلغ به شماره کارت زیر
                </h3>

                                <div
                    x-data="{
                        copied: false,
                        async copy() {
                            try {
                                await navigator.clipboard.writeText('6037697642416025');
                                this.copied = true;
                                setTimeout(() => this.copied = false, 2000);
                            } catch (e) {}
                        }
                    }"
                    class="relative overflow-hidden rounded-xl border border-[#eee4d9] bg-gradient-to-l from-[#faf0d2]/40 to-white p-4"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-[#b98a10]">شماره کارت</p>
                            <p class="mt-1 select-all font-mono text-lg font-extrabold tracking-wider text-slate-800" dir="ltr">
                                6037-6976-4241-6025
                            </p>
                            <p class="mt-0.5 text-[10px] text-slate-500">به نام: صادرات_ سارا دولتی</p>
                        </div>
                        <button
                            type="button"
                            @click="copy()"
                            class="shrink-0 rounded-lg bg-[#0b1826] px-3 py-2 text-xs font-bold text-[#e8c465] transition hover:bg-[#132840]"
                        >
                            <span x-show="!copied" class="inline-flex items-center gap-1">
                                <x-icon name="paper-clip" class="h-3.5 w-3.5" />
                                کپی
                            </span>
                            <span x-show="copied" x-cloak class="inline-flex items-center gap-1 text-[#16a34a]">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                کپی شد
                            </span>
                        </button>
                    </div>
                </div>

                <div class="mt-3 flex items-start gap-2 rounded-lg bg-[#faf0d2]/50 px-3 py-2 text-xs text-[#b98a10]">
                    <x-icon name="sparkles" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <p>
                        پس از واریز، <b>عکس رسید</b> را در فرم زیر آپلود کنید. پس از تأیید توسط ادمین، دسترسی به دوره فعال می‌شود.
                    </p>
                </div>
            </div>
        </div>

                @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-[#fdeaea] p-4">
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

                <form
            method="POST"
            action="{{ route('course.checkout.submit', $course->id) }}"
            enctype="multipart/form-data"
            class="space-y-5 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6"
            x-data="{
                receiptPreview: null,
                fileName: null,
                updatePreview(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.receiptPreview = URL.createObjectURL(file);
                    } else {
                        this.fileName = null;
                        this.receiptPreview = null;
                    }
                }
            }"
        >
            @csrf

            <div class="flex items-center gap-2">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-[#0b1826] text-[10px] font-extrabold text-[#e8c465]">۲</span>
                <h3 class="text-sm font-extrabold">اطلاعات خریدار و رسید پرداخت</h3>
            </div>

                        <div>
                <label class="mb-1.5 block text-sm font-bold">نام و نام خانوادگی</label>
                <div class="relative">
                    <x-icon name="user" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                    <input
                        type="text"
                        name="buyer_name"
                        value="{{ old('buyer_name') }}"
                        placeholder="مثلاً: علی رضایی"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-3 pr-10 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        required
                    >
                </div>
            </div>

                        <div>
                <label class="mb-1.5 block text-sm font-bold">شماره تلفن</label>
                <div class="relative">
                    <x-icon name="phone" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                    <input
                        type="tel"
                        name="buyer_phone"
                        value="{{ old('buyer_phone') }}"
                        placeholder="09123456789"
                        dir="ltr"
                        inputmode="numeric"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-3 pr-10 text-left text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        required
                    >
                </div>
            </div>

                        <div>
                <label class="mb-1.5 block text-sm font-bold">
                    عکس رسید واریزی
                    <span class="mt-0.5 block text-xs font-normal text-slate-400">PNG، JPG یا PDF — حداکثر ۵ مگابایت</span>
                </label>

                <label class="flex cursor-pointer flex-col items-center gap-2 overflow-hidden rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white transition hover:border-[#d7a92b]/60 hover:bg-[#faf5ec]">
                    <template x-if="receiptPreview">
                        <div class="relative w-full">
                            <img :src="receiptPreview" alt="پیش‌نمایش رسید" class="max-h-60 w-full rounded-lg object-contain">
                            <span class="absolute bottom-2 left-2 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-bold text-white" x-text="fileName"></span>
                        </div>
                    </template>

                    <template x-if="!receiptPreview">
                        <div class="flex flex-col items-center gap-2 py-8 text-center">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                                <x-icon name="paper-clip" class="h-6 w-6" />
                            </span>
                            <div>
                                <div class="text-sm font-bold text-slate-600">انتخاب عکس رسید</div>
                                <div class="mt-0.5 text-[10px] text-slate-400">کلیک کن و فایل رو انتخاب کن</div>
                            </div>
                        </div>
                    </template>

                    <input
                        type="file"
                        name="receipt"
                        accept="image/*"
                        class="hidden"
                        x-on:change="updatePreview($event)"
                        required
                    >
                </label>

                @error('receipt')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

                        <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
            >
                <x-icon name="check-circle" class="h-5 w-5" />
                تأیید و ثبت پرداخت
                <x-icon name="arrow-left" class="h-4 w-4" />
            </button>

            <p class="text-center text-[10px] text-slate-400">
                با ثبت این فرم، اطلاعات شما برای بررسی توسط تیم پشتیبانی ارسال می‌شود.
            </p>
        </form>

    </div>
</x-layouts.guest>