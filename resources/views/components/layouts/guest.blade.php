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
        [x-cloak] { display: none; }
    </style>
    @livewireStyles
</head>

<body class="bg-[#f4eee7] text-slate-800">

@php
    $homeRoute = auth()->check()
        ? route(auth()->user()->role . '.dashboard')
        : route('login');

    $navLinks = [
        [
            'route' => 'login',
            'href'  => $homeRoute,
            'label' => 'صفحه اصلی',
            'icon'  => 'home',
            'active' => request()->routeIs('login') && ! auth()->check(),
        ],
        [
            'route' => 'courses.index',
            'href'  => route('courses.index'),
            'label' => 'دوره‌ها',
            'icon'  => 'academic-cap',
            'active' => request()->routeIs('courses.*', 'course.checkout*'),
        ],
        [
            'route' => 'course.access.form',
            'href'  => route('course.access.form'),
            'label' => 'ورود به دوره',
            'icon'  => 'lock',
            'active' => request()->routeIs('course.access.*'),
        ],
    ];
@endphp

<div x-data="{ mobileMenu: false }" class="flex min-h-screen flex-col">

    {{-- ═══════════ هدر ═══════════ --}}
    <header class="sticky top-0 z-50 border-b border-[#eee4d9] bg-[#fdfbf8]/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

            {{-- لوگو --}}
            <a href="{{ $homeRoute }}" class="flex items-center gap-3">
                <img
                    src="{{ asset('img/logo.png') }}"
                    alt="لوگوی MRW"
                    class="h-10 w-10 rounded-xl shadow-sm ring-1 ring-[#eee4d9]"
                >
                <span class="hidden sm:block">
                    <span class="block text-sm font-extrabold leading-4">MRW Therapist</span>
                    <span class="block text-[10px] text-slate-500">سامانه آموزش و درمان آنلاین</span>
                </span>
            </a>

            {{-- ناوبری دسکتاپ --}}
            <nav class="hidden items-center gap-1 md:flex">
                @foreach ($navLinks as $link)
                    <a
                        href="{{ $link['href'] }}"
                        class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold transition
                            {{ $link['active']
                                ? 'bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md'
                                : 'text-slate-600 hover:bg-[#faf5ec] hover:text-slate-800' }}"
                    >
                        <x-icon :name="$link['icon']" class="h-4 w-4" />
                        {{ $link['label'] }}
                    </a>
                @endforeach

                @auth
                    <a
                        href="{{ $homeRoute }}"
                        class="mr-2 flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-4 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                    >
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        داشبورد من
                    </a>
                @endauth
            </nav>

            {{-- دکمه منوی موبایل --}}
            <button
                type="button"
                @click="mobileMenu = !mobileMenu"
                class="grid h-10 w-10 place-items-center rounded-xl border border-[#eee4d9] bg-white text-slate-600 shadow-sm transition hover:bg-[#faf5ec] md:hidden"
            >
                <x-icon name="menu" class="h-5 w-5" x-show="!mobileMenu" />
                <x-icon name="x-mark" class="h-5 w-5" x-show="mobileMenu" x-cloak />
            </button>
        </div>

        {{-- منوی موبایل --}}
        <div
            x-show="mobileMenu"
            x-cloak
            x-transition.opacity.duration.200ms
            @click.outside="mobileMenu = false"
            class="border-t border-[#eee4d9] bg-[#fdfbf8] px-4 py-3 md:hidden"
        >
            <nav class="flex flex-col gap-1">
                @foreach ($navLinks as $link)
                    <a
                        href="{{ $link['href'] }}"
                        class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition
                            {{ $link['active']
                                ? 'bg-[#faf0d2] text-[#b98a10]'
                                : 'text-slate-600 hover:bg-[#faf5ec]' }}"
                    >
                        <x-icon :name="$link['icon']" class="h-4 w-4" />
                        {{ $link['label'] }}
                    </a>
                @endforeach

                @auth
                    <a
                        href="{{ $homeRoute }}"
                        class="mt-1 flex items-center gap-2 rounded-xl bg-[#0b1826] px-4 py-2.5 text-sm font-bold text-[#e8c465]"
                    >
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        داشبورد من
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- ═══════════ محتوا ═══════════ --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- ═══════════ فوتر ═══════════ --}}
    <footer class="mt-16 bg-[#0b1826] text-slate-300">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">

            {{-- درباره --}}
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="MRW" class="h-10 w-10 rounded-xl ring-1 ring-white/10">
                    <div>
                        <div class="text-sm font-extrabold text-white">MRW Therapist</div>
                        <div class="text-[10px] text-slate-400">سامانه آموزش و درمان آنلاین</div>
                    </div>
                </div>
                <p class="mt-4 text-xs leading-relaxed text-slate-400">
                    پلتفرمی برای آموزش آنلاین با کلاس‌های زنده، آزمون‌ها، تکالیف و بازی‌های آموزشی —
                    همراه با پیام‌رسان اختصاصی بین مربی و دانش‌آموز.
                </p>
            </div>

            {{-- لینک‌های سریع --}}
            <div>
                <h4 class="mb-3 text-xs font-extrabold text-[#e8c465]">دسترسی سریع</h4>
                <ul class="space-y-2 text-xs">
                    @foreach ($navLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="flex items-center gap-1.5 text-slate-400 transition hover:text-[#e8c465]">
                                <x-icon name="arrow-left" class="h-3 w-3" />
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- پشتیبانی --}}
            <div>
                <h4 class="mb-3 text-xs font-extrabold text-[#e8c465]">پشتیبانی</h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li class="flex items-center gap-2">
                        <x-icon name="phone" class="h-3.5 w-3.5 text-[#e8c465]" />
                        <span dir="ltr">021-XXXX-XXXX</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chat" class="h-3.5 w-3.5 text-[#e8c465]" />
                        پشتیبانی آنلاین: ۹ صبح تا ۹ شب
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="shield-check" class="h-3.5 w-3.5 text-[#e8c465]" />
                        پرداخت امن با تأیید دستی
                    </li>
                </ul>
            </div>
        </div>

        {{-- کپی‌رایت --}}
        <div class="border-t border-white/10 px-4 py-4 text-center text-[10px] text-slate-500">
            © {{ now()->format('Y') }} — تمامی حقوق برای MRW Therapist محفوظ است.
        </div>
    </footer>

</div>

@livewireScripts
</body>
</html>