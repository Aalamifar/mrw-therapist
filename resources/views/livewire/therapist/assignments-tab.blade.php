<?php

use Livewire\Volt\Component;
use App\Models\Assignment;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use EnsuresTherapistClassAccess;
    public $classId;
    public string $title = '';
    public string $description = '';
    public string $visibleAt = '';
    public string $dueDate = '';
    public bool $showForm = false;
    public ?int $editingId = null;

    public function mount($classId)
    {
        $this->classId = $this->ownedClass($classId)->id;
    }

    public function assignments()
    {
        return Assignment::where('class_id', $this->classId)
            ->withCount('submissions')
            ->latest()
            ->get();
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibleAt' => 'nullable|date',
            'dueDate' => 'nullable|date|after_or_equal:visibleAt',
        ]);

        Assignment::create([
            'class_id' => $this->classId,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'visible_at' => $validated['visibleAt'] ?: null,
            'due_date' => $validated['dueDate'] ?: null,
        ]);

        $this->reset(['title', 'description', 'visibleAt', 'dueDate', 'showForm']);
    }

    public function delete($id)
    {
        Assignment::where('class_id', $this->classId)->findOrFail($id)->delete();
    }

    /** وضعیت هر تکلیف — برای رنگ‌آمیزی badge */
    public function status($assignment): array
    {
        $now = now();

        if ($assignment->visible_at && $assignment->visible_at->isFuture()) {
            return [
                'label' => 'آینده',
                'class' => 'bg-[#f3e8fa] text-[#9333ea]',
                'dot'   => 'bg-[#9333ea]',
            ];
        }

        if ($assignment->due_date && $assignment->due_date->isPast()) {
            return [
                'label' => 'پایان یافته',
                'class' => 'bg-[#fdeaea] text-[#dc2626]',
                'dot'   => 'bg-[#dc2626]',
            ];
        }

        return [
            'label' => 'فعال',
            'class' => 'bg-[#dff4f0] text-[#0d9488]',
            'dot'   => 'bg-[#0d9488]',
        ];
    }
}; ?>

<div class="space-y-5">

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#dff4f0] text-[#0d9488]">
                <x-icon name="clipboard-list" class="h-6 w-6" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">تکالیف</h2>
                <p class="text-xs text-slate-500">
                    {{ $this->assignments()->count() }} تکلیف ثبت شده
                </p>
            </div>
        </div>

        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
            {{ $showForm ? 'بستن فرم' : 'افزودن تکلیف' }}
        </button>
    </div>

    {{-- ═══════════ فرم ساخت تکلیف ═══════════ --}}
    @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="save" class="space-y-4">

                {{-- عنوان --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">عنوان تکلیف</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="مثلاً: تمرین‌های فصل ۳ ریاضی"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    >
                    @error('title') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- توضیحات --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">توضیحات</label>
                    <textarea
                        wire:model="description"
                        rows="3"
                        placeholder="توضیحات بیشتر برای دانش‌آموزان..."
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    ></textarea>
                    @error('description') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- تاریخ‌ها --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-bold">نمایان شود از تاریخ</label>
                        <x-jalali-datetime-picker
                            model="visibleAt"
                            class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        />
                        @error('visibleAt') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-bold">مهلت پاسخگویی</label>
                        <x-jalali-datetime-picker
                            model="dueDate"
                            class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        />
                        @error('dueDate') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- دکمه‌ها --}}
                <div class="flex gap-2 pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="flex-1 rounded-xl bg-[#0b1826] px-4 py-2.5 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                            <x-icon name="check" class="h-4 w-4" />
                            ذخیره تکلیف
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

    {{-- ═══════════ لیست تکالیف ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->assignments() as $assignment)
            @php $status = $this->status($assignment); @endphp

            <div
                wire:key="assignment-{{ $assignment->id }}"
                class="flex flex-col gap-3 border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    {{-- آواتار --}}
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $status['class'] }}">
                        <x-icon name="clipboard-list" class="h-5 w-5" />
                    </span>

                    {{-- اطلاعات --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-bold">{{ $assignment->title }}</span>
                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold {{ $status['class'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        @if ($assignment->description)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $assignment->description }}</p>
                        @endif

                        {{-- تاریخ‌ها + آمار --}}
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            @if ($assignment->visible_at)
                                <span class="inline-flex items-center gap-1">
                                    <x-icon name="calendar" class="h-3.5 w-3.5 text-[#b98a10]" />
                                    نمایش از: <b class="font-bold text-slate-700">{{ \Morilog\Jalali\Jalalian::fromCarbon($assignment->visible_at)->format('Y/m/d H:i') }}</b>
                                </span>
                            @endif
                            @if ($assignment->due_date)
                                <span class="inline-flex items-center gap-1">
                                    <x-icon name="clock" class="h-3.5 w-3.5 text-red-500" />
                                    مهلت: <b class="font-bold text-slate-700">{{ \Morilog\Jalali\Jalalian::fromCarbon($assignment->due_date)->format('Y/m/d H:i') }}</b>
                                </span>
                            @endif
                            @if ($assignment->submissions_count > 0)
                                <span class="inline-flex items-center gap-1 rounded-md bg-[#e2f6e7] px-2 py-0.5 font-bold text-[#16a34a]">
                                    <x-icon name="check-circle" class="h-3 w-3" />
                                    {{ $assignment->submissions_count }} پاسخ
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- دکمه حذف --}}
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('therapist.assignments.submissions', $assignment->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-bold text-[#061C32] transition hover:bg-[#061C32]/5">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        <span class="hidden sm:inline">مشاهده پاسخ‌ها</span>
                    </a>

                    <button
                        wire:click="delete({{ $assignment->id }})"
                        wire:confirm="مطمئنی می‌خوای این تکلیف رو حذف کنی؟"
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
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#dff4f0] text-[#0d9488]">
                    <x-icon name="clipboard-list" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز تکلیفی اضافه نشده</p>
                    <p class="mt-1 text-sm text-slate-500">
                        روی «افزودن تکلیف» کلیک کن و اولین تکلیف رو بساز.
                    </p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت اولین تکلیف
                </button>
            </div>
        @endforelse
    </div>

</div>
