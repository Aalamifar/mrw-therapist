<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MRW Therapist</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;800&display=swap');
        body { font-family: 'Vazirmatn', ui-sans-serif, system-ui, sans-serif; }
    </style>

    @livewireStyles
</head>

<body class="bg-[#f4eee7] text-slate-800">

@auth

@php
    $role = auth()->user()->role;
    $dashboardRoute = route("{$role}.dashboard");

    $navItems = match ($role) {
        'admin' => [
            ['href' => route('admin.users.create'), 'icon' => 'plus', 'label' => 'ساخت کاربر'],
            ['href' => route('admin.classes.create'), 'icon' => 'academic-cap', 'label' => 'ساخت کلاس'],
            ['href' => route('admin.users.index'), 'icon' => 'users', 'label' => 'مدیریت کاربران'],
            ['href' => route('admin.courses.index'), 'icon' => 'shopping-bag', 'label' => 'دوره‌ها'],
            ['href' => route('admin.orders.index'), 'icon' => 'clipboard-list', 'label' => 'سفارش‌ها'],
        ],
        'therapist' => [
            ['href' => route('therapist.classes'), 'icon' => 'book', 'label' => 'کلاس‌ها'],
            ['href' => route('therapist.messenger'), 'icon' => 'chat', 'label' => 'پیام‌رسان'],
        ],
        'student' => [
            ['href' => route('student.classes.index'), 'icon' => 'book', 'label' => 'کلاس‌ها'],
            ['href' => route('student.exams.index'), 'icon' => 'clipboard-list', 'label' => 'آزمون‌ها'],
            ['href' => route('student.assignments.index'), 'icon' => 'check-list', 'label' => 'تکالیف'],
            ['href' => route('student.calendar'), 'icon' => 'calendar', 'label' => 'تقویم'],
            ['href' => route('student.messenger'), 'icon' => 'chat', 'label' => 'پیام‌رسان'],
        ],
        default => [],
    };

    $primaryMobileItems = array_slice($navItems, 0, 2);
    $secondaryMobileItems = array_slice($navItems, 2);
    $mobileColumnCount = 3 + count($primaryMobileItems);
@endphp

<div class="min-h-screen">

    {{-- ═══════════ سایدبار دسکتاپ ═══════════ --}}
    <aside class="fixed right-0 top-0 z-50 hidden h-screen w-[240px] flex-col bg-[#0b1826] text-white lg:flex">

        {{-- لوگو --}}
        <div class="relative flex h-44 items-center justify-center overflow-hidden">
            <img src="{{ asset('img/logo.png') }}" alt="لوگوی MRW" class="relative rounded-3xl p-2.5">
        </div>

        {{-- منو --}}
        <nav class="mt-2 flex flex-1 flex-col gap-1 px-4">
            <x-nav-link :href="$dashboardRoute" icon="home">داشبورد</x-nav-link>

            @foreach ($navItems as $item)
                <x-nav-link :href="$item['href']" :icon="$item['icon']">{{ $item['label'] }}</x-nav-link>
            @endforeach
        </nav>

        {{-- پایین سایدبار --}}
        <div class="flex flex-col gap-1 border-t border-white/10 p-4">
            <x-nav-link href="#" icon="settings">راهنما</x-nav-link>
            <x-nav-link :href="route('logout')" icon="logout" form>خروج</x-nav-link>
        </div>
    </aside>


    {{-- ═══════════ محتوای اصلی ═══════════ --}}
    <div class="min-h-screen lg:mr-[240px]">

        {{-- هدر با تاریخ زنده --}}
        <header class="sticky top-0 z-40 border-b border-[#e9ddc9] bg-[#f4eee7]/95 px-5 py-4 backdrop-blur lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- آواتار --}}
                    <div class="grid h-11 w-11 place-items-center rounded-full bg-[#f6e7c6] text-2xl">🐻</div>

                    <div>
                        <div class="text-sm font-extrabold">{{ auth()->user()->name }}</div>
                        <div
                            class="text-xs text-slate-500"
                            x-data="{
                                date: '', time: '',
                                init() {
                                    const fd = new Intl.DateTimeFormat('fa-IR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                                    const ft = new Intl.DateTimeFormat('fa-IR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                                    this.date = fd.format(new Date());
                                    const tick = () => this.time = ft.format(new Date());
                                    tick(); setInterval(tick, 1000);
                                }
                            }"
                        >
                            <span x-text="date"></span> - <span x-text="time"></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 pb-28 sm:px-6 lg:px-8 lg:pb-6">
            {{ $slot }}
        </main>
    </div>


    {{-- ═══════════ نوار پایین موبایل (طلایی) ═══════════ --}}
    <nav class="fixed bottom-0 right-0 left-0 z-50 border-t border-[#c4a44b] bg-[#0b1826] lg:hidden">

        <div class="grid h-[72px]" style="grid-template-columns: repeat({{ $mobileColumnCount }}, minmax(0, 1fr));">

            <x-nav-link :href="$dashboardRoute" icon="home" variant="mobile">خانه</x-nav-link>

            @foreach ($primaryMobileItems as $item)
                <x-nav-link :href="$item['href']" :icon="$item['icon']" variant="mobile">{{ $item['label'] }}</x-nav-link>
            @endforeach

            <x-nav-link :href="route('logout')" icon="logout" variant="mobile" form>خروج</x-nav-link>

            <button
                type="button"
                class="mobile-nav-item text-[#e8c465]"
                onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
            >
                <x-icon name="menu" class="h-6 w-6" />
                <span>بیشتر</span>
            </button>
        </div>
    </nav>


    {{-- منوی بیشتر موبایل --}}
    <div id="mobile-menu" class="fixed bottom-[72px] right-0 left-0 z-40 hidden rounded-t-3xl bg-[#0b1826] p-5 text-white shadow-2xl lg:hidden">
        <div class="grid grid-cols-2 gap-3">
            @foreach ($secondaryMobileItems as $item)
                <x-nav-link :href="$item['href']" variant="menu">{{ $item['label'] }}</x-nav-link>
            @endforeach
            <x-nav-link href="#" variant="menu">راهنما</x-nav-link>
        </div>
    </div>

</div>

@else

    {{ $slot }}

@endauth

@livewireScripts

</body>
</html>