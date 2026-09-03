<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;

new class extends Component
{
    public $classId;
    public string $activeTab = 'videos';

    public function mount($classId)
    {
        $this->classId = $classId;
    }

    public function class()
    {
        return SchoolClass::with(['lessons', 'assignments', 'exams'])->findOrFail($this->classId);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }
}; ?>

<div>
    <h1 class="mb-4 text-xl font-bold">{{ $this->class()->name }}</h1>

    {{-- تب‌ها --}}
    <div class="flex gap-2 mb-6 border-b">
        <button wire:click="setTab('videos')"
                class="px-4 py-2 {{ $activeTab === 'videos' ? 'border-b-2 border-blue-600 font-bold' : 'text-gray-500' }}">
            فیلم‌ها
        </button>
        <button wire:click="setTab('games')"
                class="px-4 py-2 {{ $activeTab === 'games' ? 'border-b-2 border-blue-600 font-bold' : 'text-gray-500' }}">
            بازی‌ها
        </button>
        <button wire:click="setTab('books')"
                class="px-4 py-2 {{ $activeTab === 'books' ? 'border-b-2 border-blue-600 font-bold' : 'text-gray-500' }}">
            جزوه و کتاب
        </button>
        <button wire:click="setTab('assignments')"
                class="px-4 py-2 {{ $activeTab === 'assignments' ? 'border-b-2 border-blue-600 font-bold' : 'text-gray-500' }}">
            تکالیف
        </button>
        <button wire:click="setTab('exams')"
                class="px-4 py-2 {{ $activeTab === 'exams' ? 'border-b-2 border-blue-600 font-bold' : 'text-gray-500' }}">
            آزمون‌ها
        </button>
    </div>

    {{-- محتوای هر تب --}}
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