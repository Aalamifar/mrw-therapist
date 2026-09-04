<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;
use App\Models\Message; // 👈 اضافه شد برای شمارش پیام‌ها

new class extends Component
{
    public int $classesCount = 0;
    public int $studentsCount = 0;
    public int $unreadMessages = 0; // 👈 مقدار پیش‌فرض صفر شد

    public function mount()
    {
        $therapistId = auth()->id();
        
        // 1. شمارش کلاس‌ها
        $this->classesCount = SchoolClass::where('therapist_id', $therapistId)->count();
        
        // 2. شمارش دانش‌آموزان
        $this->studentsCount = SchoolClass::where('therapist_id', $therapistId)
            ->withCount('students')
            ->get()
            ->sum('students_count');

        // 3. شمارش واقعی پیام‌های خوانده‌نشده
        $this->unreadMessages = Message::whereHas('conversation.users', function ($q) use ($therapistId) {
                $q->where('users.id', $therapistId);
            })
            ->where('sender_id', '!=', $therapistId)
            ->whereNull('read_at')
            ->count();
    }

    public function goToClasses()
    {
        return $this->redirect(route('therapist.classes'), navigate: true);
    }
}; ?>

<div class="space-y-6 max-w-5xl mx-auto">
    
    {{-- 1. Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#061C32]">سلام، {{ auth()->user()->name }} 👋</h1>
            <p class="mt-1 text-sm text-slate-500">
                روز خوبی برای آموزش داشته باشید.
            </p>
        </div>
    </div>

    {{-- 2. Quick Stats (ظریف و مینیمال) --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">کلاس‌های فعال</div>
            <div class="mt-2 text-2xl font-bold text-[#061C32]">{{ $classesCount }}</div>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">دانش‌آموزان</div>
            <div class="mt-2 text-2xl font-bold text-[#061C32]">{{ $studentsCount }}</div>
        </div>
        <div class="rounded-2xl border border-[#E9C72A]/30 bg-[#E9C72A]/5 p-4 shadow-sm relative overflow-hidden">
            @if($unreadMessages > 0)
                <div class="absolute top-0 left-0 h-full w-1 bg-[#E9C72A]"></div>
            @endif
            <div class="text-xs font-medium text-[#061C32]/70">پیام‌های جدید</div>
            <div class="mt-2 text-2xl font-bold text-[#061C32]">{{ $unreadMessages }}</div>
        </div>
    </div>

    {{-- 3. Main Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('therapist.classes') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition-all hover:border-[#061C32]/30 hover:shadow-md">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#061C32] text-white transition group-hover:scale-105">
                <x-icon name="book" class="h-6 w-6" />
            </div>
            <div>
                <h3 class="font-bold text-[#061C32]">مدیریت کلاس‌ها</h3>
                <p class="mt-0.5 text-xs text-slate-500">مشاهده لیست و ورود به کلاس</p>
            </div>
        </a>

        {{-- 👈 لینک به روت واقعی پیام‌رسان تغییر کرد --}}
        <a href="{{ route('therapist.messenger') }}" class="group relative flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition-all hover:border-[#E9C72A]/50 hover:shadow-md">
            @if($unreadMessages > 0)
                <span class="absolute top-4 left-4 flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#E9C72A] opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#E9C72A]"></span>
                </span>
            @endif
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E9C72A] text-[#061C32] transition group-hover:scale-105">
                <x-icon name="chat" class="h-6 w-6" />
            </div>
            <div>
                <h3 class="font-bold text-[#061C32]">پیام‌رسان</h3>
                <p class="mt-0.5 text-xs text-slate-500">
                    @if($unreadMessages > 0) 
                        <span class="font-medium text-[#E9C72A]">{{ $unreadMessages }} پیام خوانده‌نشده</span> 
                    @else 
                        پاسخ به پیام‌های دانش‌آموزان 
                    @endif
                </p>
            </div>
        </a>
    </div>

    {{-- 4. Upcoming --}}
    @if($classesCount > 0)
        <div class="rounded-2xl border border-slate-100 bg-white p-5">
            <h3 class="mb-3 text-sm font-bold text-[#061C32]">کلاس‌های اخیر شما</h3>
            <div class="space-y-2">
                @foreach(SchoolClass::where('therapist_id', auth()->id())->latest()->take(2)->get() as $class)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 transition hover:bg-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#061C32]/10 text-[#061C32]">
                                <x-icon name="academic-cap" class="h-4 w-4" />
                            </div>
                            <div>
                                <div class="text-sm font-medium text-[#061C32]">{{ $class->name }}</div>
                                <div class="text-xs text-slate-500">{{ $class->subject }}</div>
                            </div>
                        </div>
                        <a href="{{ route('therapist.classes.show', $class->id) }}" class="text-xs font-medium text-[#125ccf] hover:underline">ورود</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>