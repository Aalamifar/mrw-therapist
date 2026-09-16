<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Course;

new class extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $description = '';
    public $price = '';
    public $image = null;
    public bool $showForm = false;

    public function courses()
    {
        return Course::withCount('lessons')->latest()->get();
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->image ? $this->image->store('courses', 'public') : null;

        Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'image' => $imagePath,
        ]);

        $this->reset(['title', 'description', 'price', 'image', 'showForm']);
    }

    public function delete($id)
    {
        $course = Course::findOrFail($id);

        // حذف تصویر قدیمی از Storage
        if ($course->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($course->image);
        }

        $course->delete();
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

    /** مجموع ارزش دوره‌ها */
    public function totalValue(): int
    {
        return (int) $this->courses()->sum('price');
    }
}; ?>

<div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="academic-cap" class="h-6 w-6" />
            </span>
            <div>
                <h1 class="text-xl font-extrabold">دوره‌ها</h1>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($this->courses()->count()) }} دوره فعال
                    @if ($this->totalValue() > 0)
                        · ارزش کل: <span class="font-bold text-[#b98a10]">{{ $this->formatPrice($this->totalValue()) }}</span> تومان
                    @endif
                </p>
            </div>
        </div>

        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
            {{ $showForm ? 'بستن فرم' : 'افزودن دوره' }}
        </button>
    </div>

        @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="save" class="space-y-5">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                        <div class="space-y-4">
                                                <div>
                            <label class="mb-1.5 block text-sm font-bold">عنوان دوره</label>
                            <input
                                type="text"
                                wire:model="title"
                                placeholder="مثلاً: آموزش مقدماتی ریاضی"
                                class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                            >
                            @error('title') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                                                <div>
                            <label class="mb-1.5 block text-sm font-bold">توضیحات</label>
                            <textarea
                                wire:model="description"
                                rows="4"
                                placeholder="توضیح کوتاه درباره این دوره..."
                                class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                            ></textarea>
                            @error('description') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                                                <div>
                            <label class="mb-1.5 block text-sm font-bold">قیمت (تومان)</label>
                            <div class="relative">
                                <input
                                    type="number"
                                    wire:model="price"
                                    placeholder="مثلاً: ۵۰۰۰۰۰"
                                    dir="ltr"
                                    class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 pl-16 text-left text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                >
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#b98a10]">
                                    تومان
                                </span>
                            </div>
                            @error('price') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror

                            @if ($price && is_numeric($price) && $price > 0)
                                <div class="mt-2 rounded-lg bg-[#faf0d2]/50 px-3 py-2 text-xs font-bold text-[#b98a10]">
                                    معادل: {{ $this->formatPrice($price) }} تومان
                                </div>
                            @endif
                        </div>
                    </div>

                                        <div x-data="{
                        previewUrl: null,
                        updatePreview(event) {
                            const file = event.target.files[0];
                            if (file) {
                                this.previewUrl = URL.createObjectURL(file);
                            } else {
                                this.previewUrl = null;
                            }
                        }
                    }">
                        <label class="mb-1.5 block text-sm font-bold">
                            تصویر دوره
                            <span class="mt-0.5 block text-xs font-normal text-slate-400">PNG یا JPG — حداکثر ۲ مگابایت</span>
                        </label>

                        <label class="flex cursor-pointer flex-col items-center justify-center gap-2 overflow-hidden rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white transition hover:border-[#d7a92b]/60 hover:bg-[#faf5ec]"
                               style="min-height: 240px;">

                                                        <template x-if="previewUrl">
                                <div class="relative h-full w-full">
                                    <img :src="previewUrl" class="h-full w-full object-cover" alt="پیش‌نمایش">
                                    <span class="absolute bottom-2 left-2 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-bold text-white">
                                        پیش‌نمایش
                                    </span>
                                </div>
                            </template>

                                                        <template x-if="!previewUrl">
                                <div class="flex flex-col items-center gap-2 py-8 text-center">
                                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                                        <x-icon name="photo" class="h-6 w-6" />
                                    </span>
                                    <div>
                                        <div class="text-sm font-bold text-slate-600">انتخاب تصویر</div>
                                        <div class="mt-0.5 text-[10px] text-slate-400">کلیک کن یا فایل رو اینجا بنداز</div>
                                    </div>
                                </div>
                            </template>

                            <input
                                type="file"
                                wire:model="image"
                                accept="image/*"
                                class="hidden"
                                x-on:change="updatePreview($event)"
                            >
                        </label>

                        @if ($image)
                            <div class="mt-2 flex items-center gap-2 text-xs text-[#16a34a]">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                <span class="truncate font-bold">{{ $image->getClientOriginalName() }}</span>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500">
                                    {{ number_format($image->getSize() / 1024 / 1024, 2) }} MB
                                </span>
                            </div>
                        @endif

                        <div wire:loading wire:target="image" class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="h-3.5 w-3.5 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            در حال آپلود تصویر...
                        </div>

                        @error('image') <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                                <div class="flex gap-2 pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="flex-1 rounded-xl bg-[#0b1826] px-4 py-2.5 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                            <x-icon name="check" class="h-4 w-4" />
                            ذخیره دوره
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

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->courses() as $course)
            <div
                wire:key="course-{{ $course->id }}"
                class="group flex flex-col overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm transition hover:-translate-y-0.5 hover:border-[#d7a92b]/40 hover:shadow-md"
            >
                                <div class="relative aspect-video w-full overflow-hidden bg-[#faf0d2]/30">
                    @if ($course->image)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->image) }}"
                            alt="{{ $course->title }}"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        >
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                                <x-icon name="academic-cap" class="h-8 w-8" />
                            </span>
                        </div>
                    @endif

                                        @if ($course->price > 0)
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-3 py-1 text-xs font-extrabold text-[#0b1826] shadow-md">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            {{ $this->formatPrice($course->price) }}
                        </span>
                    @else
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-[#e2f6e7] px-3 py-1 text-xs font-extrabold text-[#16a34a]">
                            <x-icon name="check-circle" class="h-3 w-3" />
                            رایگان
                        </span>
                    @endif
                </div>

                                <div class="flex flex-1 flex-col p-5">
                    <h3 class="line-clamp-1 text-base font-extrabold">{{ $course->title }}</h3>

                    @if ($course->description)
                        <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-slate-500">
                            {{ $course->description }}
                        </p>
                    @else
                        <p class="mt-1.5 text-sm italic text-slate-400">بدون توضیحات</p>
                    @endif

                                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-md bg-[#f3e8fa] px-2 py-1 text-[10px] font-bold text-[#9333ea]">
                            <x-icon name="video" class="h-3 w-3" />
                            {{ $this->fa($course->lessons_count) }} محتوا
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-[#faf0d2] px-2 py-1 text-[10px] font-bold text-[#b98a10]">
                            <x-icon name="sparkles" class="h-3 w-3" />
                            دوره فعال
                        </span>
                    </div>

                                        <div class="mt-auto flex items-center gap-2 border-t border-[#f0e7dc] pt-4">
                        <a
                            href="{{ route('admin.courses.lessons', $course->id) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0b1826] px-3 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                        >
                            <x-icon name="pencil" class="h-4 w-4" />
                            مدیریت محتوا
                        </a>
                        <button
                            wire:click="delete({{ $course->id }})"
                            wire:confirm="مطمئنی می‌خوای این دوره رو حذف کنی؟"
                            class="inline-flex items-center justify-center rounded-xl border border-[#eee4d9] bg-white p-2 text-red-500 transition hover:bg-red-50 hover:text-red-600"
                            title="حذف دوره"
                        >
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        @empty
                        <div class="col-span-full flex flex-col items-center gap-4 rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] px-5 py-16 text-center shadow-sm">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                    <x-icon name="academic-cap" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز دوره‌ای ساخته نشده</p>
                    <p class="mt-1 text-sm text-slate-500">اولین دوره‌ات رو بساز و شروع کن.</p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت اولین دوره
                </button>
            </div>
        @endforelse
    </div>

</div>