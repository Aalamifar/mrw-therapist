<?php

use Livewire\Volt\Component;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use EnsuresTherapistClassAccess;
    public Assignment $assignment;
    public $gradingId = null;
    public $grade = '';
    public $feedback = '';

    public function mount($assignmentId)
    {
        $this->assignment = Assignment::findOrFail($assignmentId);
        $this->ownedClass($this->assignment->class_id);
    }

    public function submissions()
    {
        return AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->with('student')
            ->latest('submitted_at')
            ->get();
    }

    public function startGrade($id)
    {
        $sub = $this->assignment->submissions()->findOrFail($id);
        $this->gradingId = $id;
        $this->grade = $sub->grade;
        $this->feedback = $sub->feedback;
    }

    public function saveGrade()
    {
        $this->validate([
            'grade' => 'nullable|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $this->assignment->submissions()->findOrFail($this->gradingId)->update([
            'grade' => $this->grade,
            'feedback' => $this->feedback,
        ]);

        $this->gradingId = null;
    }
}; ?>

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-[#061C32]">{{ $assignment->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">پاسخ‌های ارسال‌شده توسط دانش‌آموزان</p>
        </div>
        <a href="{{ route('therapist.classes.show', $assignment->class_id) }}"
           class="rounded-xl border border-[#eee4d9] bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-[#f0e7dc]">
            بازگشت
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">
        @forelse ($this->submissions() as $sub)
            <div wire:key="sub-{{ $sub->id }}" class="border-b border-[#f0e7dc] px-5 py-4 last:border-b-0">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-[#061C32]">{{ $sub->student->name }}</div>
                        <p class="text-xs text-slate-400">ارسال: {{ $sub->submitted_at?->format('Y/m/d H:i') }}</p>

                        @if($sub->answer_text)
                            <p class="mt-2 text-sm text-slate-600">{{ $sub->answer_text }}</p>
                        @endif

                        @if($sub->answer_file)
                            <a href="{{ route('submission.file', $sub->id) }}" target="_blank"
                               class="mt-2 inline-block text-sm text-blue-600 underline">مشاهده فایل ارسالی</a>
                        @endif

                        @if($sub->grade !== null)
                            <div class="mt-2 inline-flex items-center gap-1 rounded-md bg-[#dff4f0] px-2 py-0.5 text-xs font-bold text-[#0d9488]">
                                نمره: {{ $sub->grade }}
                            </div>
                        @endif
                        @if($sub->feedback)
                            <p class="mt-1 text-xs text-slate-500">بازخورد: {{ $sub->feedback }}</p>
                        @endif
                    </div>

                    <button wire:click="startGrade({{ $sub->id }})"
                            class="shrink-0 rounded-xl bg-[#061C32] px-3 py-1.5 text-sm font-bold text-white">
                        {{ $sub->grade !== null ? 'ویرایش نمره' : 'ثبت نمره' }}
                    </button>
                </div>

                @if($gradingId === $sub->id)
                    <form wire:submit="saveGrade" class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-[#eee4d9] bg-white p-4">
                        <div>
                            <label class="mb-1 block text-xs text-slate-600">نمره (از ۱۰۰)</label>
                            <input type="number" wire:model="grade" min="0" max="100"
                                   class="w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
                            @error('grade') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="mb-1 block text-xs text-slate-600">بازخورد</label>
                            <input type="text" wire:model="feedback"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
                        </div>
                        <button type="submit" class="rounded-lg bg-[#061C32] px-4 py-2 text-sm text-white">ذخیره</button>
                        <button type="button" wire:click="$set('gradingId', null)" class="rounded-lg px-4 py-2 text-sm text-slate-500">انصراف</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                <x-icon name="inbox" class="h-8 w-8 text-slate-300" />
                <p class="text-sm text-slate-500">هنوز کسی پاسخ نداده.</p>
            </div>
        @endforelse
    </div>
</div>
