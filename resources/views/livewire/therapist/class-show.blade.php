<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;
use App\Support\EnsuresTherapistClassAccess;

new class extends Component
{
    use EnsuresTherapistClassAccess;
    public $classId;
    public string $activeTab = 'videos';

    public function mount($classId)
    {
        $this->classId = $this->ownedClass($classId)->id;
    }

    public function class()
    {
        return SchoolClass::with(['lessons', 'assignments', 'exams'])
            ->withCount(['lessons', 'assignments', 'exams', 'students'])
            ->where('therapist_id', auth()->id())
            ->findOrFail($this->classId);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    /** آمار هر تب برای نمایش badge */
    public function tabStats(): array
    {
        $class = $this->class();

        return [
            'videos' => [
                'label' => 'فیلم‌ها',
                'icon' => 'video',
                'count' => $class->lessons()->where('type', 'video')->count(),
            ],
            'games' => [
                'label' => 'بازی‌ها',
                'icon' => 'sparkles',
                'count' => $class->lessons()->where('type', 'html_game')->count(),
            ],
            'books' => [
                'label' => 'جزوه و کتاب',
                'icon' => 'book-open',
                'count' => $class->lessons()->where('type', 'note')->count(),
            ],
            'assignments' => [
                'label' => 'تکالیف',
                'icon' => 'clipboard-list',
                'count' => $class->assignments_count,
            ],
            'exams' => [
                'label' => 'آزمون‌ها',
                'icon' => 'exam',
                'count' => $class->exams_count,
            ],
        ];
    }
}; ?>

<div class="space-y-6">

        <a
        href="{{ route('therapist.classes') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-right" class="h-4 w-4" />
        بازگشت به کلاس‌ها
    </a>

    @php $class = $this->class(); @endphp

        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">
        <div class="flex items-start gap-4">
                        <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="academic-cap" class="h-8 w-8" />
            </span>

                        <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-extrabold">{{ $class->name }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-[#faf0d2] px-2.5 py-1 text-xs font-bold text-[#b98a10]">
                        {{ $class->subject }}
                    </span>
                    <span class="text-xs text-slate-500">
                        {{ $class->students_count }} دانش‌آموز
                    </span>
                </div>

                                <div class="mt-4 flex flex-wrap gap-3 text-xs">
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#f3e8fa] px-2.5 py-1.5 text-[#9333ea]">
                        <x-icon name="video" class="h-3.5 w-3.5" />
                        <span class="font-bold">{{ $class->lessons_count }}</span> درس
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#dff4f0] px-2.5 py-1.5 text-[#0d9488]">
                        <x-icon name="clipboard-list" class="h-3.5 w-3.5" />
                        <span class="font-bold">{{ $class->assignments_count }}</span> تکلیف
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#fdeaea] px-2.5 py-1.5 text-[#dc2626]">
                        <x-icon name="exam" class="h-3.5 w-3.5" />
                        <span class="font-bold">{{ $class->exams_count }}</span> آزمون
                    </span>
                </div>
            </div>
        </div>
    </div>

        <div class="overflow-x-auto rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-2 shadow-sm">
        <div class="flex gap-2">
            @foreach ($this->tabStats() as $tabKey => $tab)
                <button
                    wire:click="setTab('{{ $tabKey }}')"
                    class="relative flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $activeTab === $tabKey ? 'bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md' : 'text-slate-600 hover:bg-[#faf5ec]' }}"
                >
                    <x-icon :name="$tab['icon']" class="h-4 w-4" />
                    <span>{{ $tab['label'] }}</span>
                    @if ($tab['count'] > 0)
                        <span class="rounded-full bg-white/30 px-2 py-0.5 text-xs font-extrabold {{ $activeTab === $tabKey ? 'text-[#0b1826]' : 'bg-[#eee4d9] text-slate-500' }}">
                            {{ $tab['count'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

        <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">
        @if ($activeTab === 'videos')
            <livewire:therapist.lessons-tab :classId="$classId" type="video" :key="'videos-'.$classId" />
        @elseif ($activeTab === 'games')
            <livewire:therapist.games-tab :classId="$classId" :key="'games-'.$classId" />
        @elseif ($activeTab === 'books')
            <livewire:therapist.lessons-tab :classId="$classId" type="note" :key="'books-'.$classId" />
        @elseif ($activeTab === 'assignments')
            <livewire:therapist.assignments-tab :classId="$classId" :key="'assignments-'.$classId" />
        @elseif ($activeTab === 'exams')
            <livewire:therapist.exams-tab :classId="$classId" :key="'exams-'.$classId" />
        @endif
    </div>

</div>
