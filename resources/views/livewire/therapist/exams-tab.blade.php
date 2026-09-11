<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use Morilog\Jalali\Jalalian;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use EnsuresTherapistClassAccess;
    public $classId;

    public string $title = '';
    public string $scheduledAt = '';
    public int $durationMinutes = 30;

    public bool $showForm = false;

    public function mount($classId)
    {
        $this->classId = $this->ownedClass($classId)->id;
    }

    public function exams()
    {
        return Exam::where('class_id', $this->classId)
            ->withCount('questions')
            ->latest()
            ->get();
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'scheduledAt' => 'nullable|date',
            'durationMinutes' => 'required|integer|min:5',
        ]);

        Exam::create([
            'class_id' => $this->classId,
            'title' => $validated['title'],
            'scheduled_at' => $validated['scheduledAt'] ?: null,
            'duration_minutes' => $validated['durationMinutes'],
        ]);

        $this->reset(['title', 'scheduledAt', 'durationMinutes', 'showForm']);
        $this->durationMinutes = 30;
    }

    public function delete($id)
    {
        Exam::where('class_id', $this->classId)
            ->findOrFail($id)
            ->delete();
    }

    /** وضعیت هر آزمون — برای رنگ‌آمیزی badge */
    public function status($exam): array
    {
        if (! $exam->scheduled_at) {
            return [
                'label' => 'بدون زمان‌بندی',
                'class' => 'bg-[#f0e7dc] text-slate-600',
                'dot'   => 'bg-slate-400',
            ];
        }

        if ($exam->scheduled_at->isFuture()) {
            return [
                'label' => 'در انتظار برگزاری',
                'class' => 'bg-[#f3e8fa] text-[#9333ea]',
                'dot'   => 'bg-[#9333ea]',
            ];
        }

        return [
            'label' => 'برگزار شده',
            'class' => 'bg-[#dff4f0] text-[#0d9488]',
            'dot'   => 'bg-[#0d9488]',
        ];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="space-y-5">

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
                <x-icon name="exam" class="h-6 w-6" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">آزمون‌ها</h2>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($this->exams()->count()) }} آزمون ثبت شده
                </p>
            </div>
        </div>

        <button
            wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
            {{ $showForm ? 'بستن فرم' : 'افزودن آزمون' }}
        </button>
    </div>

    {{-- ═══════════ فرم ساخت آزمون ═══════════ --}}
    @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="save" class="space-y-4">

                {{-- عنوان --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">عنوان آزمون</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="مثلاً آزمون ریاضی فصل اول"
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    >
                    @error('title') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- تاریخ برگزاری --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-bold">تاریخ و زمان برگزاری</label>
                        <x-jalali-datetime-picker
                            model="scheduledAt"
                            class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        />
                        @error('scheduledAt') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- مدت زمان --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-bold">مدت زمان آزمون (دقیقه)</label>
                        <input
                            type="number"
                            wire:model="durationMinutes"
                            min="5"
                            step="5"
                            class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        >
                        @error('durationMinutes') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
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
                            ذخیره آزمون
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

    {{-- ═══════════ لیست آزمون‌ها ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->exams() as $exam)
            @php $status = $this->status($exam); @endphp

            <div
                wire:key="exam-{{ $exam->id }}"
                class="flex flex-col gap-3 border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    {{-- آواتار --}}
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $status['class'] }}">
                        <x-icon name="exam" class="h-5 w-5" />
                    </span>

                    {{-- اطلاعات --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-bold">{{ $exam->title }}</span>
                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold {{ $status['class'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            @if ($exam->scheduled_at)
                                <span class="inline-flex items-center gap-1">
                                    <x-icon name="calendar" class="h-3.5 w-3.5 text-[#b98a10]" />
                                    برگزاری: برگزاری: <b class="font-bold text-slate-700">{{ \Morilog\Jalali\Jalalian::fromCarbon($exam->scheduled_at)->format('Y/m/d H:i') }}</b>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-icon name="clock" class="h-3.5 w-3.5 text-red-500" />
                                    {{ $this->fa($exam->duration_minutes) }} دقیقه
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 rounded-md bg-[#f3e8fa] px-2 py-0.5 font-bold text-[#9333ea]">
                                <x-icon name="check-list" class="h-3 w-3" />
                                {{ $this->fa($exam->questions_count) }} سؤال
                            </span>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیات --}}
                <div class="flex shrink-0 items-center gap-1">
                    <a
                        href="{{ route('therapist.exams.questions', $exam->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-3 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <x-icon name="pencil" class="h-4 w-4" />
                        <span class="hidden sm:inline">سؤال‌ها</span>
                    </a>

                    <a href="{{ route('therapist.exams.results', $exam->id) }}"
                        class="rounded-lg px-3 py-1.5 text-sm text-[#061C32] hover:bg-slate-100">
                        نتایج
                    </a>

                    <button
                        wire:click="delete({{ $exam->id }})"
                        wire:confirm="مطمئنی می‌خوای این آزمون رو حذف کنی؟"
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
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
                    <x-icon name="exam" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز آزمونی اضافه نشده</p>
                    <p class="mt-1 text-sm text-slate-500">
                        روی «افزودن آزمون» کلیک کن و اولین آزمون رو بساز.
                    </p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت اولین آزمون
                </button>
            </div>
        @endforelse
    </div>

</div>
