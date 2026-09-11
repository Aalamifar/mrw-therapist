<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;

new class extends Component
{
    public $classId;
    public string $activeTab = 'videos';

    public function mount($classId)
    {
        $class = SchoolClass::findOrFail($classId);

        abort_unless(
            $class->students()->where('student_id', auth()->id())->exists(),
            403,
            'شما عضو این کلاس نیستید.'
        );

        $this->classId = $classId;
    }

    public function class()
    {
        return SchoolClass::with('therapist')->findOrFail($this->classId);
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
            'books' => [
                'label' => 'جزوه و کتاب',
                'icon' => 'book-open',
                'count' => $class->lessons()->where('type', 'note')->count(),
            ],
            'games' => [
                'label' => 'بازی‌ها',
                'icon' => 'sparkles',
                'count' => $class->lessons()->where('type', 'html_game')->count(),
            ],
            'assignments' => [
                'label' => 'تکالیف',
                'icon' => 'clipboard-list',
                'count' => $class->assignments()
                    ->where(function ($q) {
                        $q->whereNull('visible_at')->orWhere('visible_at', '<=', now());
                    })
                    ->count(),
            ],
            'exams' => [
                'label' => 'آزمون‌ها',
                'icon' => 'exam',
                'count' => $class->exams()->count(),
            ],
        ];
    }

    /** رنگ آواتار — ثابت و هماهنگ با پالت طرح */
    public function avatarTone($id): string
    {
        $tones = [
            'bg-[#f3e8fa] text-[#9333ea]',
            'bg-[#dff4f0] text-[#0d9488]',
            'bg-[#fdeaea] text-[#dc2626]',
            'bg-[#e2f6e7] text-[#16a34a]',
            'bg-[#e8eef7] text-[#33507a]',
            'bg-[#fae4fb] text-[#c026d3]',
        ];

        return $tones[abs(crc32((string) $id)) % count($tones)];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="space-y-6">

    {{-- ═══════════ بازگشت ═══════════ --}}
    <a
        href="{{ route('student.dashboard') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm text-slate-500 transition hover:bg-[#faf5ec] hover:text-slate-700"
    >
        <x-icon name="arrow-right" class="h-4 w-4" />
        بازگشت به داشبورد
    </a>

    @php $class = $this->class(); @endphp

    {{-- ═══════════ هدر کلاس ═══════════ --}}
    <div class="rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-6 shadow-sm sm:p-8">
        <div class="flex items-start gap-4">
            {{-- آواتار کلاس --}}
            <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="academic-cap" class="h-8 w-8" />
            </span>

            {{-- اطلاعات --}}
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-extrabold">{{ $class->name }}</h1>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-[#faf0d2] px-2.5 py-1 text-xs font-bold text-[#b98a10]">
                        {{ $class->subject }}
                    </span>

                    @if ($class->therapist)
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                            <span class="grid h-5 w-5 place-items-center rounded-full text-[10px] font-extrabold {{ $this->avatarTone($class->therapist->id) }}">
                                {{ mb_substr($class->therapist->name, 0, 1) }}
                            </span>
                            مربی: <b class="font-bold text-slate-700">{{ $class->therapist->name }}</b>
                        </span>

                        <a
                            href="{{ route('student.messenger', ['user_id' => $class->therapist->id]) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#f3e8fa] px-3 py-1.5 text-xs font-bold text-[#9333ea] transition hover:bg-[#e9d5f7]"
                        >
                            <x-icon name="chat" class="h-3.5 w-3.5" />
                            پیام به مربی
                        </a>
                    @endif
                </div>

                {{-- آمار سریع --}}
                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#fdeaea] px-2.5 py-1.5 font-bold text-[#dc2626]">
                        <x-icon name="video" class="h-3.5 w-3.5" />
                        {{ $this->fa($class->lessons()->where('type', 'video')->count()) }} فیلم
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#dff4f0] px-2.5 py-1.5 font-bold text-[#0d9488]">
                        <x-icon name="book-open" class="h-3.5 w-3.5" />
                        {{ $this->fa($class->lessons()->where('type', 'note')->count()) }} جزوه
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#f3e8fa] px-2.5 py-1.5 font-bold text-[#9333ea]">
                        <x-icon name="sparkles" class="h-3.5 w-3.5" />
                        {{ $this->fa($class->lessons()->where('type', 'html_game')->count()) }} بازی
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#e2f6e7] px-2.5 py-1.5 font-bold text-[#16a34a]">
                        <x-icon name="clipboard-list" class="h-3.5 w-3.5" />
                        {{ $this->fa($class->assignments()->count()) }} تکلیف
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-[#e8eef7] px-2.5 py-1.5 font-bold text-[#33507a]">
                        <x-icon name="exam" class="h-3.5 w-3.5" />
                        {{ $this->fa($class->exams()->count()) }} آزمون
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ تب‌ها ═══════════ --}}
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
                        <span class="rounded-full px-2 py-0.5 text-xs font-extrabold {{ $activeTab === $tabKey ? 'bg-white/30 text-[#0b1826]' : 'bg-[#eee4d9] text-slate-500' }}">
                            {{ $this->fa($tab['count']) }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- ═══════════ محتوای تب ═══════════ --}}
    <div>
        @if ($activeTab === 'videos')
            <livewire:student.lessons-view :classId="$classId" type="video" :key="'videos-'.$classId" />
        @elseif ($activeTab === 'books')
            <livewire:student.lessons-view :classId="$classId" type="note" :key="'books-'.$classId" />
        @elseif ($activeTab === 'games')
            <livewire:student.lessons-view :classId="$classId" type="html_game" :key="'games-'.$classId" />
        @elseif ($activeTab === 'assignments')
            <livewire:student.assignments-view :classId="$classId" :key="'assignments-'.$classId" />
        @elseif ($activeTab === 'exams')
            <livewire:student.exams-view :classId="$classId" :key="'exams-'.$classId" />
        @endif
    </div>

</div>
