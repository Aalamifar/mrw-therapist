<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Exam;
use Illuminate\Support\Facades\Storage;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use WithFileUploads;
    use EnsuresTherapistClassAccess;

    public Exam $exam;

    public string $type = 'multiple_choice';
    public string $question = '';
    public array $options = ['', ''];
    public $correctIndex = 0;
    public int $points = 1;
    public $file = null;

    public bool $showForm = false;

    public function mount(Exam $exam)
    {
        $this->ownedClass($exam->class_id);
        $this->exam = $exam->load('questions');
    }

    public function addOption()
    {
        $this->options[] = '';
    }

    public function removeOption($index)
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function saveQuestion()
    {
        $rules = [
            'question' => 'required|string',
            'points' => 'required|integer|min:1',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];

        if ($this->type === 'multiple_choice') {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'required|string';
            $rules['correctIndex'] = 'required|integer';
        }

        $validated = $this->validate($rules);

        $filePath = $this->file ? $this->file->store('exam-questions', 'local') : null;

        $this->exam->questions()->create([
            'type' => $this->type,
            'question' => $validated['question'],
            'options' => $this->type === 'multiple_choice' ? array_values($this->options) : null,
            'correct_answer' => $this->type === 'multiple_choice' ? $this->options[$this->correctIndex] : null,
            'points' => $validated['points'],
            'file_path' => $filePath,
        ]);

        $this->exam->load('questions');

        $this->reset(['question', 'options', 'correctIndex', 'points', 'file', 'showForm']);
        $this->options = ['', ''];
    }

    public function deleteQuestion($id)
    {
        $q = $this->exam->questions()->where('id', $id)->firstOrFail();

        if ($q->file_path) {
            Storage::disk('local')->delete($q->file_path);
        }

        $q->delete();
        $this->exam->load('questions');
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="space-y-6" x-data="{ open: {} }">

    {{-- ═══════════ بازگشت ═══════════ --}}
    <a
        href="{{ route('therapist.classes.show', $exam->class_id) }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-right" class="h-4 w-4" />
        بازگشت به کلاس
    </a>

    {{-- ═══════════ هدر آزمون ═══════════ --}}
    <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
                    <x-icon name="exam" class="h-7 w-7" />
                </span>
                <div>
                    <h1 class="text-xl font-extrabold">{{ $exam->title }}</h1>
                    <p class="mt-1 text-sm text-slate-500">مدیریت سؤالات آزمون</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span class="flex items-center gap-1.5 rounded-lg bg-[#faf0d2] px-2.5 py-1.5 font-bold text-[#b98a10]">
                            <x-icon name="check-list" class="h-3.5 w-3.5" />
                            {{ $this->fa($exam->questions->count()) }} سؤال
                        </span>
                        <span class="flex items-center gap-1.5 rounded-lg bg-[#f3e8fa] px-2.5 py-1.5 font-bold text-[#9333ea]">
                            <x-icon name="sparkles" class="h-3.5 w-3.5" />
                            {{ $this->fa($exam->questions->sum('points')) }} امتیاز کل
                        </span>
                    </div>
                </div>
            </div>

            <button
                wire:click="$set('showForm', {{ $showForm ? 'false' : 'true' }})"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
            >
                <x-icon name="{{ $showForm ? 'x-mark' : 'plus' }}" class="h-4 w-4" />
                {{ $showForm ? 'بستن فرم' : 'افزودن سؤال' }}
            </button>
        </div>
    </div>

    {{-- ═══════════ فرم افزودن سؤال ═══════════ --}}
    @if ($showForm)
        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm sm:p-6">
            <form wire:submit="saveQuestion" class="space-y-5">

                {{-- نوع سؤال --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">نوع سؤال</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([
                            'multiple_choice' => ['چندگزینه‌ای', 'check-list'],
                            'text' => ['تشریحی', 'pencil'],
                        ] as $value => [$label, $icon])
                            <label wire:key="type-{{ $value }}" class="cursor-pointer">
                                <input type="radio" wire:model.live="type" value="{{ $value }}" class="peer sr-only">
                                <span class="flex items-center justify-center gap-2 rounded-xl border border-[#eee4d9] bg-white px-3 py-2.5 text-sm font-bold text-slate-500 shadow-sm transition hover:border-[#d7a92b]/40 hover:bg-[#faf5ec] peer-checked:border-[#d7a92b] peer-checked:bg-[#faf0d2] peer-checked:text-[#b98a10] peer-checked:ring-2 peer-checked:ring-[#d7a92b]/20">
                                    <x-icon name="{{ $icon }}" class="h-4 w-4" />
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- متن سؤال --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold">متن سؤال</label>
                    <textarea
                        wire:model="question"
                        rows="4"
                        placeholder="متن سؤال را وارد کنید..."
                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                    ></textarea>
                    @error('question') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- گزینه‌ها --}}
                @if ($type === 'multiple_choice')
                    <div class="space-y-2">
                        <label class="block text-sm font-bold">
                            گزینه‌ها
                            <span class="mt-0.5 block text-xs font-normal text-slate-400">گزینه درست را با دکمه رادیو مشخص کن</span>
                        </label>

                        @foreach ($options as $index => $option)
                            <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5 transition {{ $correctIndex == $index ? 'border-green-400 bg-[#e2f6e7]' : 'border-[#eee4d9] bg-white' }}">
                                <label class="cursor-pointer p-1" title="علامت‌گذاری به‌عنوان پاسخ درست">
                                    <input type="radio" wire:model.live="correctIndex" value="{{ $index }}" class="sr-only">
                                    <span class="grid h-5 w-5 place-items-center rounded-full border-2 transition {{ $correctIndex == $index ? 'border-green-500 bg-green-500' : 'border-[#d8cbb4] bg-white' }}">
                                        <span class="h-2 w-2 rounded-full bg-white {{ $correctIndex == $index ? '' : 'hidden' }}"></span>
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="options.{{ $index }}"
                                    placeholder="گزینه {{ $this->fa($index + 1) }}"
                                    class="flex-1 bg-transparent text-sm outline-none placeholder:text-slate-400"
                                >
                                @if (count($options) > 2)
                                    <button type="button" wire:click="removeOption({{ $index }})" class="rounded-lg p-1.5 text-red-500 transition hover:bg-red-50">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                @endif
                            </div>
                        @endforeach

                        @error('options') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                        @error('options.*') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror

                        <button
                            type="button"
                            wire:click="addOption"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm font-bold text-[#b98a10] transition hover:bg-[#faf0d2]"
                        >
                            <x-icon name="plus" class="h-4 w-4" />
                            افزودن گزینه
                        </button>
                    </div>
                @endif

                {{-- امتیاز + فایل --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-bold">امتیاز این سؤال</label>
                        <input
                            type="number"
                            wire:model="points"
                            min="1"
                            class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                        >
                        @error('points') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold">فایل ضمیمه (اختیاری)</label>
                        <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white px-4 py-4 text-center transition hover:border-[#d7a92b]/60 hover:bg-[#faf5ec]">
                            <input type="file" wire:model="file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="sr-only">
                            @if ($file)
                                <x-icon name="paper-clip" class="h-5 w-5 text-[#16a34a]" />
                                <span class="max-w-full truncate text-sm font-bold text-[#16a34a]">{{ $file->getClientOriginalName() }}</span>
                            @else
                                <x-icon name="paper-clip" class="h-5 w-5 text-[#b98a10]" />
                                <span class="text-sm font-bold text-slate-600">انتخاب فایل</span>
                                <span class="text-[10px] text-slate-400">JPG، PNG، WEBP، PDF — حداکثر ۵ مگابایت</span>
                            @endif
                        </label>
                        <div wire:loading wire:target="file" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="h-3.5 w-3.5 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            در حال آماده‌سازی فایل...
                        </div>
                        @error('file') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- دکمه‌ها --}}
                <div class="flex gap-2 pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveQuestion"
                        class="flex-1 rounded-xl bg-[#0b1826] px-4 py-2.5 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <span wire:loading.remove wire:target="saveQuestion" class="inline-flex items-center gap-2">
                            <x-icon name="check" class="h-4 w-4" />
                            ذخیره سؤال
                        </span>
                        <span wire:loading wire:target="saveQuestion" class="inline-flex items-center gap-2">
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

    {{-- ═══════════ لیست سؤالات ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($exam->questions as $index => $q)
            <div wire:key="question-{{ $q->id }}" class="border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec]">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 flex-1 gap-3">
                        {{-- شماره سؤال --}}
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#faf0d2] text-sm font-extrabold text-[#b98a10]">
                            {{ $this->fa($index + 1) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold leading-6">{{ $q->question }}</p>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-md px-2 py-0.5 font-bold {{ $q->type === 'multiple_choice' ? 'bg-[#f3e8fa] text-[#9333ea]' : 'bg-[#dff4f0] text-[#0d9488]' }}">
                                    {{ $q->type === 'multiple_choice' ? 'چندگزینه‌ای' : 'تشریحی' }}
                                </span>
                                <span class="rounded-md bg-[#faf0d2] px-2 py-0.5 font-bold text-[#b98a10]">
                                    {{ $this->fa($q->points) }} امتیاز
                                </span>
                                @if ($q->file_path)
                                    <span class="rounded-md bg-[#e8eef7] px-2 py-0.5 font-bold text-[#33507a]">دارای ضمیمه</span>
                                @endif

                                <button
                                    type="button"
                                    @click="open['{{ $q->id }}'] = !open['{{ $q->id }}']"
                                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 font-bold text-slate-500 transition hover:bg-[#f0e7dc] hover:text-slate-700"
                                >
                                    <span x-text="open['{{ $q->id }}'] ? 'بستن جزئیات' : 'مشاهده جزئیات'"></span>
                                    <x-icon name="chevron-down" class="h-3.5 w-3.5 transition-transform" ::class="open['{{ $q->id }}'] ? 'rotate-180' : ''" />
                                </button>
                            </div>

                            {{-- جزئیات --}}
                            <div x-show="open['{{ $q->id }}']" x-cloak x-collapse class="mt-3 space-y-2 rounded-xl border border-[#eee4d9] bg-white p-4">
                                @if ($q->type === 'multiple_choice')
                                    <ul class="space-y-1.5 text-sm">
                                        @foreach ($q->options as $opt)
                                            <li class="flex items-center gap-2 rounded-lg px-3 py-2 {{ $opt === $q->correct_answer ? 'bg-[#e2f6e7] font-bold text-[#16a34a]' : 'bg-[#faf5ec] text-slate-600' }}">
                                                @if ($opt === $q->correct_answer)
                                                    <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                                                @endif
                                                {{ $opt }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-slate-500">پاسخ این سؤال به‌صورت تشریحی توسط دانش‌آموز نوشته می‌شود.</p>
                                @endif

                                @if ($q->file_path)
                                    <a
                                        href="{{ route('exam-question.file', $q->id) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#e8eef7] px-3 py-1.5 text-xs font-bold text-[#33507a] transition hover:bg-[#d8e2f0]"
                                    >
                                        <x-icon name="paper-clip" class="h-3.5 w-3.5" />
                                        مشاهده فایل ضمیمه
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- حذف --}}
                    <button
                        wire:click="deleteQuestion({{ $q->id }})"
                        wire:confirm="مطمئنی می‌خوای این سؤال رو حذف کنی؟"
                        class="shrink-0 rounded-xl p-2 text-red-600 transition hover:bg-red-50"
                        title="حذف سؤال"
                    >
                        <x-icon name="trash" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
                    <x-icon name="exam" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">هنوز سؤالی ثبت نشده</p>
                    <p class="mt-1 text-sm text-slate-500">اولین سؤال این آزمون رو بساز.</p>
                </div>
                <button
                    wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    ساخت اولین سؤال
                </button>
            </div>
        @endforelse
    </div>

</div>
