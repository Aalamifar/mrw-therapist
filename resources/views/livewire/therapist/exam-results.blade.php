<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use EnsuresTherapistClassAccess;
    public Exam $exam;
    public $openResultId = null;
    public $gradingAnswerId = null;
    public $pointsInput = 0;

    public function mount($examId)
    {
        $this->exam = Exam::with('questions')->findOrFail($examId);
        $this->ownedClass($this->exam->class_id);
    }

    public function results()
    {
        return $this->exam->results()
            ->with(['student', 'answers.question'])
            ->latest('submitted_at')
            ->get();
    }

    public function toggle($resultId)
    {
        $this->openResultId = $this->openResultId === $resultId ? null : $resultId;
    }

    public function startGrade($answerId)
    {
        $answer = ExamAnswer::whereHas('examResult', fn ($query) => $query->where('exam_id', $this->exam->id))
            ->findOrFail($answerId);
        $this->gradingAnswerId = $answerId;
        $this->pointsInput = $answer->points_awarded;
    }

    public function saveGrade()
    {
        $answer = ExamAnswer::whereHas('examResult', fn ($query) => $query->where('exam_id', $this->exam->id))
            ->findOrFail($this->gradingAnswerId);
        $maxPoints = $answer->question->points;

        $this->validate([
            'pointsInput' => "required|integer|min:0|max:{$maxPoints}",
        ]);

        $answer->update([
            'points_awarded' => $this->pointsInput,
            'is_correct' => $this->pointsInput >= $maxPoints,
        ]);

        // مجموع امتیازها رو دوباره محاسبه و توی نمره‌ی کلی ذخیره کن
        $result = $answer->examResult;
        $result->update([
            'score' => $result->answers()->sum('points_awarded'),
        ]);

        $this->gradingAnswerId = null;
    }
}; ?>

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-[#061C32]">{{ $exam->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $this->results()->count() }} دانش‌آموز شرکت کرده‌اند</p>
        </div>
        <a href="{{ route('therapist.classes.show', $exam->class_id) }}"
           class="rounded-xl border border-[#eee4d9] bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-[#f0e7dc]">
            بازگشت
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->results() as $result)
            <div wire:key="result-{{ $result->id }}" class="border-b border-[#f0e7dc] last:border-b-0">
                <button wire:click="toggle({{ $result->id }})"
                        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-right hover:bg-[#faf5ec]">
                    <div>
                        <div class="font-bold text-[#061C32]">{{ $result->student->name }}</div>
                        <p class="text-xs text-slate-400">ارسال: {{ $result->submitted_at?->format('Y/m/d H:i') }}</p>
                    </div>
                    <span class="rounded-lg bg-[#dff4f0] px-3 py-1.5 text-sm font-bold text-[#0d9488]">
                        نمره فعلی: {{ $result->score }}
                    </span>
                </button>

                @if ($openResultId === $result->id)
                    <div class="space-y-4 border-t border-[#f0e7dc] bg-white px-5 py-4">
                        @foreach ($result->answers as $answer)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="font-medium text-[#061C32]">{{ $answer->question->question }}</p>
                                <p class="mt-2 text-sm text-slate-600">
                                    پاسخ دانش‌آموز: <span class="font-bold">{{ $answer->answer_text ?: '(بدون پاسخ)' }}</span>
                                </p>

                                @if ($answer->question->type === 'multiple_choice')
                                    <p class="mt-1 text-xs {{ $answer->is_correct ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $answer->is_correct ? 'صحیح' : 'نادرست' }} — {{ $answer->points_awarded }} از {{ $answer->question->points }} امتیاز (خودکار)
                                    </p>

                                @else
                                    <p class="mt-1 text-xs text-slate-500">
                                        امتیاز فعلی: {{ $answer->points_awarded }} از {{ $answer->question->points }}
                                    </p>

                                    @if ($answer->answer_file)
                                        @php $ext = strtolower(pathinfo($answer->answer_file, PATHINFO_EXTENSION)); @endphp
                                        <div class="mt-2">
                                            @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                                                <img src="{{ route('exam-answer.file', $answer->id) }}" alt="برگه پاسخ دانش‌آموز"
                                                    class="max-w-sm rounded-lg border border-slate-200">
                                            @elseif ($ext === 'pdf')
                                                <iframe src="{{ route('exam-answer.file', $answer->id) }}#toolbar=0"
                                                    class="h-64 w-full rounded-lg border border-slate-200"></iframe>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($gradingAnswerId === $answer->id)
                                        <form wire:submit="saveGrade" class="mt-2 flex items-end gap-2">
                                            <div>
                                                <label class="mb-1 block text-xs text-slate-600">امتیاز (حداکثر {{ $answer->question->points }})</label>
                                                <input type="number" wire:model="pointsInput" min="0" max="{{ $answer->question->points }}"
                                                       class="w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
                                                @error('pointsInput') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                                            </div>
                                            <button type="submit" class="rounded-lg bg-[#061C32] px-4 py-2 text-sm text-white">ذخیره</button>
                                            <button type="button" wire:click="$set('gradingAnswerId', null)" class="rounded-lg px-4 py-2 text-sm text-slate-500">انصراف</button>
                                        </form>
                                    @else
                                        <button wire:click="startGrade({{ $answer->id }})"
                                                class="mt-2 rounded-lg bg-[#E9C72A] px-3 py-1.5 text-xs font-bold text-[#061C32]">
                                            تصحیح و امتیازدهی
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">هنوز کسی در این آزمون شرکت نکرده.</p>
            </div>
        @endforelse
    </div>
</div>
