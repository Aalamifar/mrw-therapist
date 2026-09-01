<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;
use App\Models\User;

new class extends Component
{
    public $classId;
    public $selectedStudents = [];
    public string $successMessage = '';
    public string $search = '';
    
    public function allStudents()
    {
        return User::where('role', 'student')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->get();
    }
    
    public function mount($classId)
    {
        $this->classId = $classId;
        $class = SchoolClass::findOrFail($classId);
        $this->selectedStudents = $class->students->pluck('id')->toArray();
    }

    public function saveStudents()
    {
        $class = SchoolClass::findOrFail($this->classId);
        $class->students()->sync($this->selectedStudents);

        $this->successMessage = 'دانش‌آموزهای کلاس به‌روزرسانی شد.';
    }

    public function class()
    {
        return SchoolClass::findOrFail($this->classId);
    }
}; ?>

<div class="max-w-md p-6 bg-white rounded-lg shadow">
    <h2 class="mb-4 text-lg font-bold">
        دانش‌آموزهای کلاس: {{ $this->class()->name }}
    </h2>
    
    <input type="text" wire:model.live="search" placeholder="جستجوی دانش‌آموز..." class="w-full px-3 py-2 mb-3 border rounded">
    
    @if ($successMessage)
        <div class="mb-4 text-sm text-green-600">{{ $successMessage }}</div>
    @endif

    <div class="mb-4 space-y-2">
        @foreach($this->allStudents() as $student)
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="selectedStudents" value="{{ $student->id }}">
                {{ $student->name }} ({{ $student->phone }})
            </label>
        @endforeach
    </div>

    <button wire:click="saveStudents" class="w-full py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
        ذخیره
    </button>
</div>