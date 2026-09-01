<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MRW Therapist</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-[#F7F6F3] text-slate-800">

@auth

@php
    $role = auth()->user()->role;
    $dashboardRoute = route("{$role}.dashboard");

    // Role-specific nav items (dashboard + logout are handled separately, always the same shape).
    $navItems = match ($role) {
        'admin' => [
            ['href' => route('admin.users.create'), 'icon' => 'plus', 'label' => 'ساخت کاربر'],
            ['href' => route('admin.classes.create'), 'icon' => 'academic-cap', 'label' => 'ساخت کلاس'],
            ['href' => route('admin.users.index'), 'icon' => 'users', 'label' => 'مدیریت کاربران'],
        ],
        'therapist' => [
            ['href' => '#', 'icon' => 'book', 'label' => 'کلاس‌ها'],
        ],
        'student' => [
            ['href' => '#', 'icon' => 'book', 'label' => 'کلاس‌ها'],
            ['href' => '#', 'icon' => 'video', 'label' => 'جلسه‌های آنلاین'],
            ['href' => '#', 'icon' => 'clipboard-list', 'label' => 'آزمون‌ها'],
            ['href' => '#', 'icon' => 'check-list', 'label' => 'تکالیف'],
            ['href' => '#', 'icon' => 'check-circle', 'label' => 'ارزیابی‌ها'],
            ['href' => '#', 'icon' => 'calendar', 'label' => 'تقویم'],
            ['href' => '#', 'icon' => 'document', 'label' => 'مدارک'],
            ['href' => '#', 'icon' => 'bell', 'label' => 'تابلوی اعلانات'],
            ['href' => '#', 'icon' => 'chat', 'label' => 'پیام‌رسان'],
        ],
        default => [],
    };

    // On mobile, only the first couple of role items fit in the bottom bar;
    // everything else (for every role, including therapist) goes in the "بیشتر" drawer.
    $primaryMobileItems = array_slice($navItems, 0, 2);
    $secondaryMobileItems = array_slice($navItems, 2);
    $mobileColumnCount = 3 + count($primaryMobileItems); // home + logout + more + primary items
@endphp

<div class="min-h-screen">

    {{-- ========================================= --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ========================================= --}}

    <aside class="fixed right-0 top-0 z-50 hidden h-screen w-[240px] flex-col bg-[#061C32] text-white lg:flex">

        {{-- Logo --}}
        <div class="flex items-center justify-center">
            <img src="{{ asset('img/logo.png') }}" alt="لوگوی MRW" class="rounded-md">
        </div>

        {{-- Navigation --}}
        <nav class="mt-7 flex-1 space-y-1 px-4">
            <x-nav-link :href="$dashboardRoute" icon="home">داشبورد</x-nav-link>

            @foreach ($navItems as $item)
                <x-nav-link :href="$item['href']" :icon="$item['icon']">{{ $item['label'] }}</x-nav-link>
            @endforeach
        </nav>

        {{-- Bottom sidebar --}}
        <div class="space-y-1 border-t border-slate-700 p-4">
            <x-nav-link href="#" icon="settings">راهنما</x-nav-link>
            <x-nav-link :href="route('logout')" icon="logout" form>خروج</x-nav-link>
        </div>

    </aside>


    {{-- ========================================= --}}
    {{-- MAIN --}}
    {{-- ========================================= --}}

    <div class="min-h-screen lg:mr-[240px]">

        <header class="sticky top-0 z-40 border-b border-gray-200 bg-[#F7F6F3]/95 px-5 py-4 backdrop-blur lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#E9C72A] font-bold text-[#061C32]">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>

                    <div>
                        <div class="text-sm font-bold">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ \Morilog\Jalali\Jalalian::now()->format('l j F Y') }} - {{ now()->format('H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

    </div>


    {{-- ========================================= --}}
    {{-- MOBILE BOTTOM NAVIGATION --}}
    {{-- ========================================= --}}

    <nav class="fixed bottom-0 right-0 left-0 z-50 border-t border-gray-200 bg-[#061C32] lg:hidden">

        <div class="grid h-[72px]" style="grid-template-columns: repeat({{ $mobileColumnCount }}, minmax(0, 1fr));">

            <x-nav-link :href="$dashboardRoute" icon="home" variant="mobile">خانه</x-nav-link>

            @foreach ($primaryMobileItems as $item)
                <x-nav-link :href="$item['href']" :icon="$item['icon']" variant="mobile">{{ $item['label'] }}</x-nav-link>
            @endforeach

            <x-nav-link :href="route('logout')" icon="logout" variant="mobile" form>خروج</x-nav-link>

            <button
                type="button"
                class="mobile-nav-item"
                onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
            >
                <x-icon name="menu" class="h-6 w-6" />
                <span>بیشتر</span>
            </button>

        </div>

    </nav>


    {{-- Mobile menu (everything that didn't fit in the bottom bar, for every role) --}}
    <div id="mobile-menu" class="fixed bottom-[72px] right-0 left-0 z-40 hidden rounded-t-3xl bg-[#061C32] p-5 text-white shadow-2xl lg:hidden">

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
