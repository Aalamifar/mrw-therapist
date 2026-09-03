<?php

use Livewire\Volt\Component;
use App\Models\SchoolClass;

new class extends Component
{
    public int $classesCount = 0;
    public int $studentsCount = 0;
    public int $unreadMessages = 0;

    public function mount()
    {
        $therapistId = auth()->id();
        
        // 1. تعداد کلاس‌ها
        $this->classesCount = SchoolClass::where('therapist_id', $therapistId)->count();

        // 2. تعداد دانش‌آموزان (فرض بر این است که رابطه students در مدل SchoolClass وجود دارد)
        // اگر هنوز رابطه را نساخته‌اید، این عدد فعلاً 0 یا یک عدد تستی خواهد بود
        $this->studentsCount = SchoolClass::where('therapist_id', $therapistId)
            ->withCount('students')
            ->get()
            ->sum('students_count');

        // 3. تعداد پیام‌های خوانده‌نشده (این بخش را بعداً به مدل پیام خود متصل کنید)
        // فعلاً به صورت تستی یک عدد قرار می‌دهیم تا طراحی را ببینید
        $this->unreadMessages = 3; // TODO: جایگزین شود با: Message::where('receiver_id', $therapistId)->where('is_read', false)->count();
    }

    public function goToClasses()
    {
        return $this->redirect(route('therapist.classes'), navigate: true);
    }

    public function goToMessenger()
    {
        // فعلاً لینک خالی است تا وقتی که روت پیام‌رسان را بسازید
        // return $this->redirect(route('therapist.messenger'), navigate: true);
    }
}; ?>

<div class="space-y-8 max-w-5xl mx-auto">

    {{-- 1. Header & Welcome --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#061C32]">
                سلام، {{ auth()->user()->name ?? 'مربی عزیز' }} 👋
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                امروز {{ now()->format('l j F Y') }} است. روز خوبی برای آموزش داشته باشید!
            </p>
        </div>
        
        {{-- پروفایل کوچک --}}
        <div class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 px-4 py-2 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-[#E9C72A]/20 flex items-center justify-center text-[#061C32] font-bold">
                {{ mb_substr(auth()->user()->name ?? 'M', 0, 1) }}
            </div>
            <div class="hidden sm:block">
                <div class="text-sm font-bold text-[#061C32]">{{ auth()->user()->name ?? 'مربی' }}</div>
                <div class="text-xs text-slate-500">در دسترس</div>
            </div>
        </div>
    </div>

    {{-- 2. Stats Cards (همان ایده مستطیل نوتیفیکیشن، اما حرفه‌ای‌تر) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- کارت کلاس‌ها --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">کلاس‌های فعال</p>
                    <p class="text-3xl font-bold text-[#061C32] mt-1">{{ $classesCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-[#061C32]/5 flex items-center justify-center text-[#061C32]">
                    <x-icon name="academic-cap" class="h-6 w-6" />
                </div>
            </div>
        </div>

        {{-- کارت دانش‌آموزان --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">کل دانش‌آموزان</p>
                    <p class="text-3xl font-bold text-[#061C32] mt-1">{{ $studentsCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <x-icon name="users" class="h-6 w-6" />
                </div>
            </div>
        </div>

        {{-- کارت پیام‌های خوانده‌نشده (نوتیفیکیشن) --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
            @if($unreadMessages > 0)
                <div class="absolute top-0 left-0 w-1 h-full bg-[#E9C72A]"></div>
            @endif
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">پیام‌های جدید</p>
                    <p class="text-3xl font-bold text-[#061C32] mt-1">{{ $unreadMessages }}</p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-[#E9C72A]/20 flex items-center justify-center text-[#061C32] relative">
                    <x-icon name="chat-bubble-left-right" class="h-6 w-6" />
                    @if($unreadMessages > 0)
                        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white animate-pulse">
                            {{ $unreadMessages > 9 ? '+9' : $unreadMessages }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Quick Actions (دکمه‌های اصلی) --}}
    <div>
        <h2 class="text-lg font-bold text-[#061C32] mb-4">دسترسی سریع</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            {{-- دکمه کلاس‌ها --}}
            <button 
                wire:click="goToClasses"
                class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-6 text-right transition hover:border-[#061C32]/30 hover:bg-[#061C32]/5 hover:shadow-md"
            >
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#061C32] text-white transition group-hover:scale-110">
                    <x-icon name="briefcase" class="h-7 w-7" />
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-[#061C32]">مدیریت کلاس‌ها</h3>
                    <p class="mt-1 text-sm text-slate-500">مشاهده لیست کلاس‌ها، دانش‌آموزان و ورود به کلاس</p>
                </div>
                <x-icon name="arrow-left" class="h-5 w-5 text-slate-400 transition group-hover:-translate-x-1 group-hover:text-[#061C32]" />
            </button>

            {{-- دکمه پیام‌رسان --}}
            <button 
                wire:click="goToMessenger"
                class="group relative flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-6 text-right transition hover:border-[#E9C72A]/50 hover:bg-[#E9C72A]/5 hover:shadow-md"
            >
                @if($unreadMessages > 0)
                    <span class="absolute top-4 left-4 flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#E9C72A] opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-[#E9C72A]"></span>
                    </span>
                @endif
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#E9C72A] text-[#061C32] transition group-hover:scale-110">
                    <x-icon name="chat" class="h-7 w-7" />
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-[#061C32]">پیام‌رسان</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        @if($unreadMessages > 0)
                            <span class="font-medium text-[#E9C72A]">{{ $unreadMessages }} پیام خوانده‌نشده دارید</span>
                        @else
                            پیام‌های خود را با دانش‌آموزان و والدین بررسی کنید
                        @endif
                    </p>
                </div>
                <x-icon name="arrow-left" class="h-5 w-5 text-slate-400 transition group-hover:-translate-x-1 group-hover:text-[#E9C72A]" />
            </button>

        </div>
    </div>

    {{-- 4. Recent Activity (اختیاری - برای زنده‌تر شدن داشبورد) --}}
    @if($classesCount > 0)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-[#061C32]">کلاس‌های اخیر شما</h2>
                <button wire:click="goToClasses" class="text-sm font-medium text-[#125ccf] hover:underline">مشاهده همه</button>
            </div>
            <div class="space-y-3">
                @foreach(\App\Models\SchoolClass::where('therapist_id', auth()->id())->latest()->take(3)->get() as $class)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 transition hover:bg-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-[#061C32]/10 flex items-center justify-center text-[#061C32]">
                                <x-icon name="book-open" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="font-medium text-[#061C32]">{{ $class->name }}</div>
                                <div class="text-xs text-slate-500">{{ $class->subject }}</div>
                            </div>
                        </div>
                        <button wire:click="goToClasses" class="text-xs font-medium text-[#125ccf] hover:underline">ورود</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>