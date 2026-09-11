<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Support\EnsuresStudentClassAccess;

new class extends Component
{
    use WithFileUploads;
    use EnsuresStudentClassAccess;

    public $classId;
    public $answeringId = null;
    public string $answerText = '';
    public $answerFile = null;

    public function mount($classId)
    {
        $this->classId = $this->enrolledClass($classId)->id;
    }

    public function assignments()
    {
        return Assignment::where('class_id', $this->classId)
            ->where(function ($q) {
                $q->whereNull('visible_at')->orWhere('visible_at', '<=', now());
            })
            ->with(['submissions' => function ($q) {
                $q->where('student_id', auth()->id());
            }])
            ->latest()
            ->get();
    }

    public function startAnswer($assignmentId)
    {
        Assignment::where('class_id', $this->classId)->findOrFail($assignmentId);
        $this->answeringId = $assignmentId;
        $this->answerText = '';
        $this->answerFile = null;
    }

    public function submit($assignmentId)
    {
        $assignment = Assignment::where('class_id', $this->classId)
            ->where(fn ($query) => $query->whereNull('visible_at')->orWhere('visible_at', '<=', now()))
            ->findOrFail($assignmentId);

        if ($assignment->due_date && now()->greaterThan($assignment->due_date)) {
            $this->addError('answerText', 'مهلت ارسال این تکلیف به پایان رسیده است.');
            return;
        }

        $this->validate([
            'answerText' => 'nullable|string',
            'answerFile' => 'nullable|file|max:10240',
        ]);

        $filePath = $this->answerFile ? $this->answerFile->store('submissions', 'local') : null;

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignmentId, 'student_id' => auth()->id()],
            ['answer_text' => $this->answerText, 'answer_file' => $filePath, 'submitted_at' => now()]
        );

        $this->answeringId = null;
    }

    /** وضعیت هر تکلیف — badge و رنگ‌بندی هوشمند */
    public function status($assignment): array
    {
        $submission = $assignment->submissions->first();

        // اولویت ۱: نمره‌دار (بهترین حالت)
        if ($submission && $submission->grade !== null) {
            return [
                'label' => 'نمره‌دار',
                'class' => 'bg-[#faf0d2] text-[#b98a10]',
                'dot'   => 'bg-[#b98a10]',
                'icon'  => 'check-circle',
                'accent' => '#b98a10',
            ];
        }

        // اولویت ۲: ارسال‌شده و منتظر بررسی
        if ($submission) {
            return [
                'label' => 'ارسال شد',
                'class' => 'bg-[#e2f6e7] text-[#16a34a]',
                'dot'   => 'bg-[#16a34a]',
                'icon'  => 'check-circle',
                'accent' => '#16a34a',
            ];
        }

        // اولویت ۳: پایان مهلت و پاسخ نداده
        if ($assignment->due_date && $assignment->due_date->isPast()) {
            return [
                'label' => 'پایان مهلت',
                'class' => 'bg-[#fdeaea] text-[#dc2626]',
                'dot'   => 'bg-[#dc2626]',
                'icon'  => 'x-mark',
                'accent' => '#dc2626',
            ];
        }

        // اولویت ۴: فعال و منتظر پاسخ
        return [
            'label' => 'در انتظار پاسخ',
            'class' => 'bg-[#dff4f0] text-[#0d9488]',
            'dot'   => 'bg-[#0d9488]',
            'icon'  => 'clipboard-list',
            'accent' => '#0d9488',
        ];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }

    /** زمان باقی‌مانده تا مهلت */
    public function timeRemaining($dueDate): string
    {
        if (! $dueDate || $dueDate->isPast()) {
            return 'مهلت به پایان رسیده';
        }

        $diff = now()->diff($dueDate);

        if ($diff->days > 0) {
            return $this->fa($diff->days) . ' روز و ' . $this->fa($diff->h) . ' ساعت باقی‌مانده';
        }

        if ($diff->h > 0) {
            return $this->fa($diff->h) . ' ساعت و ' . $this->fa($diff->i) . ' دقیقه باقی‌مانده';
        }

        return $this->fa($diff->i) . ' دقیقه باقی‌مانده';
    }
}; ?>

<div class="space-y-6">

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    @php
        $allAssignments = $this->assignments();
        $total = $allAssignments->count();
        $submitted = $allAssignments->filter(fn($a) => $a->submissions->isNotEmpty())->count();
        $pending = $total - $submitted;
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#dff4f0] text-[#0d9488]">
                <x-icon name="clipboard-list" class="h-6 w-6" />
            </span>
            <div>
                <h2 class="text-xl font-extrabold">تکالیف کلاس</h2>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($total) }} تکلیف · {{ $this->fa($submitted) }} ارسال‌شده
                    @if ($pending > 0)
                        · <span class="text-[#b98a10] font-bold">{{ $this->fa($pending) }} در انتظار</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════ لیست تکالیف ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

        @forelse ($allAssignments as $assignment)
            @php
                $status = $this->status($assignment);
                $mySubmission = $assignment->submissions->first();
                $isAnswering = $answeringId === $assignment->id;
                $isOverdue = $status['label'] === 'پایان مهلت';
            @endphp

            <div
                wire:key="assignment-{{ $assignment->id }}"
                class="border-b border-[#f0e7dc] px-5 py-5 transition last:border-b-0 hover:bg-[#faf5ec] sm:px-6"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    {{-- آواتار + اطلاعات --}}
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        {{-- آواتار رنگی بر اساس وضعیت --}}
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $status['class'] }}">
                            <x-icon name="{{ $status['icon'] }}" class="h-6 w-6" />
                        </span>

                        <div class="min-w-0 flex-1">
                            {{-- عنوان + badge وضعیت --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-extrabold">{{ $assignment->title }}</h3>
                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold {{ $status['class'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                                    {{ $status['label'] }}
                                </span>
                            </div>

                            {{-- توضیحات --}}
                            @if ($assignment->description)
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $assignment->description }}</p>
                            @endif

                            {{-- مهلت + زمان باقی‌مانده --}}
                            <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                @if ($assignment->due_date)
                                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                                        <x-icon name="clock" class="h-3.5 w-3.5 text-red-500" />
                                        مهلت: <b class="font-bold text-slate-700" dir="ltr">{{ $assignment->due_date->format('Y/m/d H:i') }}</b>
                                    </span>

                                    @if (! $isOverdue && ! $mySubmission)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-[#faf0d2] px-2 py-0.5 font-bold text-[#b98a10]">
                                            <x-icon name="sparkles" class="h-3 w-3" />
                                            {{ $this->timeRemaining($assignment->due_date) }}
                                        </span>
                                    @elseif ($isOverdue)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-[#fdeaea] px-2 py-0.5 font-bold text-[#dc2626]">
                                            <x-icon name="x-mark" class="h-3 w-3" />
                                            مهلت تمام شده
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- دکمه پاسخ / badge ارسال‌شده --}}
                    <div class="shrink-0 sm:pt-1">
                        @if ($mySubmission)
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#e2f6e7] px-4 py-2 text-sm font-extrabold text-[#16a34a]">
                                <x-icon name="check-circle" class="h-4 w-4" />
                                ارسال شده
                            </span>
                        @else
                            <button
                                wire:click="startAnswer({{ $assignment->id }})"
                                @disabled($isOverdue)
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                            >
                                <x-icon name="pencil" class="h-4 w-4" />
                                پاسخ می‌دهم
                            </button>
                        @endif
                    </div>
                </div>

                {{-- ═══════════ پاسخ قبلی (ارسال‌شده) ═══════════ --}}
                @if ($mySubmission)
                    <div class="mt-4 overflow-hidden rounded-xl border border-[#eee4d9] bg-white">
                        {{-- هدر پاسخ --}}
                        <div class="flex items-center gap-2 border-b border-[#f0e7dc] bg-[#faf5ec] px-4 py-2.5">
                            <x-icon name="check-circle" class="h-4 w-4 text-[#16a34a]" />
                            <span class="text-xs font-bold text-slate-700">پاسخ شما</span>
                            <span class="text-[10px] text-slate-400" dir="ltr">
                                · {{ $mySubmission->submitted_at->format('Y/m/d H:i') }}
                            </span>
                        </div>

                        <div class="space-y-3 p-4">
                            {{-- متن پاسخ --}}
                            @if ($mySubmission->answer_text)
                                <div class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
                                    {{ $mySubmission->answer_text }}
                                </div>
                            @endif

                            {{-- فایل ضمیمه --}}
                            @if ($mySubmission->answer_file)
                                <a
                                    href="{{ Storage::disk('local')->url($mySubmission->answer_file) }}"
                                    target="_blank"
                                    class="flex items-center gap-3 rounded-xl border border-[#eee4d9] bg-[#faf5ec] px-3 py-2.5 transition hover:bg-[#f0e7dc]"
                                >
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-[#f3e8fa] text-[#9333ea]">
                                        <x-icon name="paper-clip" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-xs font-bold">فایل پاسخ ضمیمه شده</div>
                                        <div class="mt-0.5 text-[10px] text-slate-500">برای دانلود کلیک کنید</div>
                                    </div>
                                    <x-icon name="arrow-down-tray" class="h-4 w-4 text-[#b98a10]" />
                                </a>
                            @endif

                            {{-- نمره --}}
                            @if ($mySubmission->grade !== null)
                                <div class="flex items-center gap-3 rounded-xl bg-gradient-to-l from-[#faf0d2]/60 to-[#faf0d2]/20 border border-[#e8c465]/30 px-4 py-3">
                                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-xl font-extrabold text-[#0b1826] shadow-md">
                                        {{ $this->fa($mySubmission->grade) }}
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-[#b98a10]">نمره شما</div>
                                        <div class="text-[10px] text-slate-500">از طرف مربی</div>
                                    </div>
                                </div>
                            @endif

                            {{-- بازخورد مربی --}}
                            @if ($mySubmission->feedback)
                                <div class="rounded-xl bg-[#e8eef7] px-4 py-3">
                                    <div class="mb-1 flex items-center gap-1.5 text-xs font-bold text-[#33507a]">
                                        <x-icon name="chat" class="h-3.5 w-3.5" />
                                        بازخورد مربی
                                    </div>
                                    <p class="text-sm leading-relaxed text-[#33507a]">{{ $mySubmission->feedback }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ═══════════ فرم پاسخ ═══════════ --}}
                @if ($isAnswering)
                    <form
                        wire:submit="submit({{ $assignment->id }})"
                        class="mt-4 space-y-4 rounded-xl border border-[#d7a92b]/30 bg-[#faf5ec] p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-2">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-[#faf0d2] text-[#b98a10]">
                                <x-icon name="pencil" class="h-4 w-4" />
                            </span>
                            <h4 class="text-sm font-extrabold">پاسخ به تکلیف</h4>
                        </div>

                        {{-- متن پاسخ --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-600">متن پاسخ (اختیاری)</label>
                            <textarea
                                wire:model="answerText"
                                rows="4"
                                placeholder="پاسخ خود را اینجا بنویسید..."
                                class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                            ></textarea>
                            @error('answerText') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- فایل پاسخ --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-600">
                                فایل پاسخ (اختیاری)
                                <span class="mr-1 text-[10px] font-normal text-slate-400">حداکثر ۱۰ مگابایت</span>
                            </label>

                            @if ($answerFile)
                                <div class="flex items-center gap-3 rounded-xl border border-[#eee4d9] bg-white px-3 py-2.5">
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-[#e2f6e7] text-[#16a34a]">
                                        <x-icon name="check-circle" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-xs font-bold">{{ $answerFile->getClientOriginalName() }}</div>
                                        <div class="text-[10px] text-slate-500">
                                            آماده ارسال · {{ number_format($answerFile->getSize() / 1024 / 1024, 2) }} MB
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="$set('answerFile', null)"
                                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                                    >
                                        <x-icon name="x-mark" class="h-4 w-4" />
                                    </button>
                                </div>
                            @else
                                <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-dashed border-[#e0d4bf] bg-white px-4 py-5 text-center transition hover:border-[#d7a92b]/60 hover:bg-[#fdfbf8]">
                                    <input type="file" wire:model="answerFile" class="sr-only">
                                    <span class="grid h-10 w-10 place-items-center rounded-full bg-[#f3e8fa] text-[#9333ea]">
                                        <x-icon name="arrow-down-tray" class="h-5 w-5" />
                                    </span>
                                    <span class="text-sm font-bold text-slate-600">انتخاب فایل</span>
                                    <span class="text-[10px] text-slate-400">PDF، Word، تصویر و...</span>
                                </label>
                            @endif

                            <div wire:loading wire:target="answerFile" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                                <svg class="h-3.5 w-3.5 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                                در حال آپلود...
                            </div>
                            @error('answerFile') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- دکمه‌ها --}}
                        <div class="flex gap-2 pt-1">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                                class="flex-1 rounded-xl bg-[#0b1826] px-4 py-2.5 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                            >
                                <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                                    <x-icon name="check" class="h-4 w-4" />
                                    ارسال پاسخ
                                </span>
                                <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    در حال ارسال...
                                </span>
                            </button>
                            <button
                                type="button"
                                wire:click="$set('answeringId', null)"
                                class="rounded-xl border border-[#eee4d9] bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-[#f0e7dc]"
                            >
                                انصراف
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            {{-- حالت خالی --}}
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#dff4f0] text-[#0d9488]">
                    <x-icon name="clipboard-list" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">تکلیفی ثبت نشده</p>
                    <p class="mt-1 text-sm text-slate-500">
                        هنوز تکلیفی برای این کلاس تعریف نشده است.
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</div>
