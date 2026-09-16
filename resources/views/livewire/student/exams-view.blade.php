<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamAnswer;
use Livewire\WithFileUploads;
use Morilog\Jalali\Jalalian;
use App\Support\EnsuresStudentClassAccess;

new class extends Component
{
    use WithFileUploads;
    use EnsuresStudentClassAccess;

    public $classId;
    public $takingExamId = null;
    public array $answers = [];
    public array $answerFiles = [];

    public function mount($classId)
    {
        $this->classId = $this->enrolledClass($classId)->id;
    }

    public function exams()
    {
        return Exam::where('class_id', $this->classId)
            ->with(['results' => function ($q) {
                $q->where('student_id', auth()->id());
            }, 'questions'])
            ->latest('scheduled_at')
            ->get();
    }

    public function myResult(Exam $exam)
    {
        return $exam->results->first();
    }

    public function isOpen(Exam $exam): bool
    {
        if (! $exam->scheduled_at) {
            return true;
        }

        $end = $exam->scheduled_at->copy()->addMinutes($exam->duration_minutes);

        return now()->between($exam->scheduled_at, $end);
    }

    public function startExam($examId)
    {
        $exam = Exam::where('class_id', $this->classId)->with('questions')->findOrFail($examId);

        abort_unless($this->isOpen($exam), 403, 'زمان این آزمون نیست.');

        $existing = ExamResult::where('exam_id', $examId)->where('student_id', auth()->id())->first();
        abort_if($existing, 403, 'شما قبلاً این آزمون را داده‌اید.');

        $this->takingExamId = $examId;
        $this->answers = [];
    }

    public function currentExam()
    {
        return Exam::where('class_id', $this->classId)
            ->with('questions')
            ->find($this->takingExamId);
    }

    public function remainingSeconds(): int
    {
        $exam = $this->currentExam();

        if (! $exam) {
            return 0;
        }

        if (! $exam->scheduled_at) {
            return $exam->duration_minutes * 60;
        }

        $end = $exam->scheduled_at->copy()->addMinutes($exam->duration_minutes);

        return max(0, now()->diffInSeconds($end, false));
    }

    public function submitExam()
    {
        $exam = $this->currentExam();

        if (! $exam) {
            return;
        }

        abort_unless($this->isOpen($exam), 403, 'زمان این آزمون نیست.');
        abort_if(
            ExamResult::where('exam_id', $exam->id)->where('student_id', auth()->id())->exists(),
            403,
            'شما قبلاً این آزمون را داده‌اید.'
        );

        $result = ExamResult::create([
            'exam_id' => $exam->id,
            'student_id' => auth()->id(),
            'score' => 0,
            'submitted_at' => now(),
        ]);

        $totalScore = 0;

        foreach ($exam->questions as $question) {
            $studentAnswer = $this->answers[$question->id] ?? null;
            $isCorrect = null;
            $points = 0;
            $filePath = null;

            if ($question->type === 'multiple_choice') {
                $isCorrect = $studentAnswer === $question->correct_answer;
                $points = $isCorrect ? $question->points : 0;
                $totalScore += $points;
            } else {
                if (isset($this->answerFiles[$question->id]) && $this->answerFiles[$question->id]) {
                    $filePath = $this->answerFiles[$question->id]->store('exam-answers', 'local');
                }
            }

            ExamAnswer::create([
                'exam_result_id' => $result->id,
                'exam_question_id' => $question->id,
                'answer_text' => $studentAnswer,
                'answer_file' => $filePath,
                'is_correct' => $isCorrect,
                'points_awarded' => $points,
            ]);
        }

        $result->update(['score' => $totalScore]);

        $this->takingExamId = null;
        $this->answers = [];
        $this->answerFiles = [];
    }

    /** وضعیت هر آزمون — badge و رنگ‌بندی هوشمند */
    public function examStatus($exam): array
    {
        $result = $this->myResult($exam);

        // اولویت ۱: داده شده
        if ($result) {
            $maxScore = $exam->questions->sum('points') ?: 1;
            $percent = ($result->score / $maxScore) * 100;

            return [
                'label' => 'پاسخ داده شد',
                'class' => 'bg-[#e2f6e7] text-[#16a34a]',
                'dot'   => 'bg-[#16a34a]',
                'icon'  => 'check-circle',
                'score' => $result->score,
                'max'   => $maxScore,
                'percent' => $percent,
            ];
        }

        // اولویت ۲: در حال برگزاری
        if ($this->isOpen($exam)) {
            return [
                'label' => 'در حال برگزاری',
                'class' => 'bg-[#faf0d2] text-[#b98a10]',
                'dot'   => 'bg-[#b98a10]',
                'icon'  => 'sparkles',
                'cta'   => 'شروع آزمون',
            ];
        }

        // اولویت ۳: آینده
        if ($exam->scheduled_at && $exam->scheduled_at->isFuture()) {
            return [
                'label' => 'در انتظار',
                'class' => 'bg-[#e8eef7] text-[#33507a]',
                'dot'   => 'bg-[#33507a]',
                'icon'  => 'clock',
            ];
        }

        // اولویت ۴: گذشته و از دست رفته
        return [
            'label' => 'پایان یافته',
            'class' => 'bg-[#fdeaea] text-[#dc2626]',
            'dot'   => 'bg-[#dc2626]',
            'icon'  => 'x-mark',
        ];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(
            range('0', '9'),
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            (string) $number
        );
    }
}; ?>

<div class="space-y-6">

        <div class="flex items-center gap-3">
        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
            <x-icon name="exam" class="h-6 w-6" />
        </span>
        <div>
            <h2 class="text-xl font-extrabold">آزمون‌های کلاس</h2>
            <p class="text-xs text-slate-500">
                {{ $this->fa($this->exams()->count()) }} آزمون ثبت شده
            </p>
        </div>
    </div>

    @if ($this->takingExamId)
                @php $exam = $this->currentExam(); @endphp

        <div
            x-data="{
                remaining: {{ $this->remainingSeconds() }},
                answered: {{ count($this->answers) }},
                total: {{ $exam->questions->count() }},
                init() {
                    const tick = () => {
                        if (this.remaining > 0) {
                            this.remaining--;
                        } else {
                            $wire.submitExam();
                        }
                    };
                    setInterval(tick, 1000);

                    window.addEventListener('beforeunload', (e) => {
                        if (this.remaining > 0) {
                            e.preventDefault();
                            e.returnValue = 'آزمون در حال برگزاری است. آیا مطمئنی می‌خوای صفحه رو ببندی؟';
                        }
                    });
                },
                formatTime(s) {
                    const m = Math.floor(s / 60);
                    const sec = s % 60;
                    return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
                }
            }"
            class="space-y-5"
        >

                        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
                            <x-icon name="exam" class="h-6 w-6" />
                        </span>
                        <div>
                            <h2 class="text-lg font-extrabold">{{ $exam->title }}</h2>
                            <p class="text-xs text-slate-500">
                                <span x-text="answered"></span> از {{ $this->fa($exam->questions->count()) }} سؤال پاسخ داده شد
                            </p>
                        </div>
                    </div>

                                        <div
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 shadow-md"
                        :class="remaining < 60 ? 'animate-pulse from-red-500 to-red-600 text-white' : 'text-[#0b1826]'"
                    >
                        <x-icon name="clock" class="h-4 w-4" />
                        <span
                            class="font-mono text-base font-extrabold"
                            dir="ltr"
                            x-text="formatTime(remaining)"
                        ></span>
                    </div>
                </div>

                                <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-[#eee4d9]">
                    <div
                        class="h-full rounded-full bg-gradient-to-br from-[#f0d060] to-[#d7a92b] transition-all duration-500"
                        :style="`width: ${(answered / total) * 100}%`"
                    ></div>
                </div>
            </div>

                        <form wire:submit="submitExam" class="space-y-4">
                @foreach ($exam->questions as $i => $q)
                    @php
                        $isAnswered = isset($this->answers[$q->id]) && $this->answers[$q->id] !== null;
                        $ext = $q->file_path ? strtolower(pathinfo($q->file_path, PATHINFO_EXTENSION)) : null;
                    @endphp

                    <div class="rounded-2xl border {{ $isAnswered ? 'border-[#d7a92b]/40 bg-[#faf0d2]/30' : 'border-[#eee4d9] bg-[#fdfbf8]' }} p-5 shadow-sm transition sm:p-6">
                                                <div class="flex items-start gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-[#faf0d2] text-sm font-extrabold text-[#b98a10]">
                                {{ $this->fa($i + 1) }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md px-2 py-0.5 text-[10px] font-bold {{ $q->type === 'multiple_choice' ? 'bg-[#f3e8fa] text-[#9333ea]' : 'bg-[#dff4f0] text-[#0d9488]' }}">
                                        {{ $q->type === 'multiple_choice' ? 'چندگزینه‌ای' : 'تشریحی' }}
                                    </span>
                                    <span class="rounded-md bg-[#faf0d2] px-2 py-0.5 text-[10px] font-bold text-[#b98a10]">
                                        {{ $this->fa($q->points) }} امتیاز
                                    </span>
                                </div>

                                <p class="mt-2 text-sm font-bold leading-relaxed">{{ $q->question }}</p>
                            </div>
                        </div>

                                                @if ($q->file_path && $ext)
                            <div class="mt-4 overflow-hidden rounded-xl border border-[#eee4d9] bg-white" oncontextmenu="return false;">
                                @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                                    <img
                                        src="{{ route('exam-question.file', $q->id) }}"
                                        alt="فایل ضمیمه سؤال"
                                        class="max-h-96 w-full object-contain"
                                    >
                                @elseif ($ext === 'pdf')
                                    <iframe
                                        src="{{ route('exam-question.file', $q->id) }}#toolbar=0&navpanes=0&scrollbar=0"
                                        class="h-96 w-full"
                                        title="فایل ضمیمه سؤال"
                                    ></iframe>
                                @endif
                            </div>
                        @endif

                                                <div class="mt-4">
                            @if ($q->type === 'multiple_choice')
                                <div class="space-y-2">
                                    @foreach ($q->options as $optIndex => $opt)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                wire:model="answers.{{ $q->id }}"
                                                value="{{ $opt }}"
                                                class="peer sr-only"
                                            >
                                            <span class="flex items-center gap-3 rounded-xl border border-[#eee4d9] bg-white px-4 py-3 text-sm transition hover:border-[#d7a92b]/40 hover:bg-[#faf5ec] peer-checked:border-[#d7a92b] peer-checked:bg-[#faf0d2] peer-checked:ring-2 peer-checked:ring-[#d7a92b]/20">
                                                                                                <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full border-2 transition {{ (isset($this->answers[$q->id]) && $this->answers[$q->id] === $opt) ? 'border-[#d7a92b] bg-[#d7a92b]' : 'border-[#d8cbb4] bg-white' }}">
                                                    <span class="h-2 w-2 rounded-full bg-white {{ (isset($this->answers[$q->id]) && $this->answers[$q->id] === $opt) ? '' : 'hidden' }}"></span>
                                                </span>

                                                                                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-[#faf0d2] text-xs font-extrabold text-[#b98a10]">
                                                    {{ ['الف', 'ب', 'ج', 'د', 'ه', 'و'][$optIndex] ?? '?' }}
                                                </span>

                                                                                                <span class="flex-1 font-bold text-slate-700">{{ $opt }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                                                <div class="space-y-3">
                                    <textarea
                                        wire:model="answers.{{ $q->id }}"
                                        rows="4"
                                        placeholder="پاسخ خود را اینجا بنویسید..."
                                        class="w-full rounded-xl border border-[#eee4d9] bg-white px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                    ></textarea>

                                    <div class="rounded-xl bg-[#faf5ec]/50 p-3">
                                        <label class="mb-1.5 block text-xs font-bold text-slate-600">
                                            یا عکس برگه‌ات رو آپلود کن
                                            <span class="text-[10px] font-normal text-slate-400">(PDF یا تصویر)</span>
                                        </label>

                                        @if (isset($this->answerFiles[$q->id]) && $this->answerFiles[$q->id])
                                            <div class="flex items-center gap-3 rounded-lg border border-[#eee4d9] bg-white px-3 py-2">
                                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-[#e2f6e7] text-[#16a34a]">
                                                    <x-icon name="check-circle" class="h-4 w-4" />
                                                </span>
                                                <span class="min-w-0 flex-1 truncate text-xs font-bold">
                                                    {{ $this->answerFiles[$q->id]->getClientOriginalName() }}
                                                </span>
                                                <button
                                                    type="button"
                                                    wire:click="$set('answerFiles.{{ $q->id }}', null)"
                                                    class="rounded p-1 text-slate-400 hover:bg-red-50 hover:text-red-600"
                                                >
                                                    <x-icon name="x-mark" class="h-4 w-4" />
                                                </button>
                                            </div>
                                        @else
                                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-[#e0d4bf] bg-white px-3 py-3 text-center transition hover:border-[#d7a92b]/60 hover:bg-[#fdfbf8]">
                                                <input
                                                    type="file"
                                                    wire:model="answerFiles.{{ $q->id }}"
                                                    accept="image/*,.pdf"
                                                    class="sr-only"
                                                >
                                                <x-icon name="arrow-down-tray" class="h-4 w-4 text-[#b98a10]" />
                                                <span class="text-xs font-bold text-slate-600">انتخاب فایل</span>
                                            </label>
                                        @endif

                                        <div wire:loading wire:target="answerFiles.{{ $q->id }}" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                                            <svg class="h-3.5 w-3.5 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                            </svg>
                                            در حال آپلود...
                                        </div>
                                        @error('answerFiles.' . $q->id) <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submitExam"
                    onclick="return confirm('آیا مطمئنی می‌خوای آزمون رو ارسال کنی؟ بعد از ارسال امکان تغییر پاسخ نیست.')"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] py-3.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                >
                    <span wire:loading.remove wire:target="submitExam" class="inline-flex items-center gap-2">
                        <x-icon name="check-circle" class="h-5 w-5" />
                        پایان و ارسال آزمون
                    </span>
                    <span wire:loading wire:target="submitExam" class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        در حال ثبت پاسخ‌ها...
                    </span>
                </button>
            </form>
        </div>

    @else
                <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
            @forelse ($this->exams() as $exam)
                @php
                    $status = $this->examStatus($exam);
                @endphp

                <div
                    wire:key="exam-{{ $exam->id }}"
                    class="flex flex-col gap-4 border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-6"
                >
                                        <div class="flex min-w-0 flex-1 items-start gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $status['class'] }}">
                            <x-icon name="{{ $status['icon'] }}" class="h-5 w-5" />
                        </span>

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
                                        <b class="font-bold text-slate-700">{{ \Morilog\Jalali\Jalalian::fromCarbon($exam->scheduled_at)->format('Y/m/d H:i') }}</b>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="clock" class="h-3.5 w-3.5 text-red-500" />
                                        {{ $this->fa($exam->duration_minutes) }} دقیقه
                                    </span>
                                @else
                                    <span class="italic">زمان‌بندی نشده</span>
                                @endif
                                <span class="inline-flex items-center gap-1 rounded-md bg-[#f3e8fa] px-2 py-0.5 font-bold text-[#9333ea]">
                                    <x-icon name="check-list" class="h-3 w-3" />
                                    {{ $this->fa($exam->questions->count()) }} سؤال
                                </span>
                            </div>
                        </div>
                    </div>

                                        <div class="shrink-0">
                        @if ($result = $this->myResult($exam))
                                                        <div class="flex items-center gap-3 rounded-xl bg-gradient-to-l from-[#faf0d2]/60 to-[#faf0d2]/20 border border-[#e8c465]/30 px-4 py-2.5">
                                <span class="grid h-10 w-10 place-items-center rounded-lg bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-sm font-extrabold text-[#0b1826] shadow-sm">
                                    {{ $this->fa($result->score) }}
                                </span>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-[#b98a10]">نمره شما</div>
                                    <div class="text-xs text-slate-500">از {{ $this->fa($exam->questions->sum('points')) }}</div>
                                </div>
                            </div>
                        @elseif (isset($status['cta']))
                            <button
                                wire:click="startExam({{ $exam->id }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-4 py-2.5 text-sm font-extrabold text-[#0b1826] shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <x-icon name="arrow-left" class="h-4 w-4" />
                                {{ $status['cta'] }}
                            </button>
                        @else
                            <span class="text-xs italic text-slate-400">
                                {{ $exam->scheduled_at && $exam->scheduled_at->isFuture() ? 'منتظر شروع بمانید' : 'امکان ورود نیست' }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                                <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#fdeaea] text-[#dc2626]">
                        <x-icon name="exam" class="h-8 w-8" />
                    </span>
                    <div>
                        <p class="font-bold">آزمونی ثبت نشده</p>
                        <p class="mt-1 text-sm text-slate-500">
                            هنوز آزمونی برای این کلاس تعریف نشده است.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

</div>
