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

<div class="min-h-screen">

    {{-- ========================================= --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ========================================= --}}

    <aside
        class="
            fixed
            right-0
            top-0
            z-50
            hidden
            h-screen
            w-[240px]
            flex-col
            bg-[#061C32]
            text-white
            lg:flex
        "
    >

        {{-- Logo --}}
        <div class="flex items-center justify-center">

            <img src="{{ asset('img/logo.png') }}" alt="لوگوی MRW" class="rounded-md">

        </div>

        {{-- Navigation --}}
        <nav class="mt-7 flex-1 px-4">

            @if(auth()->user()->role === 'admin')

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="sidebar-link"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l9-9 9 9M5 10v10h14V10"/>
                    </svg>

                    <span>داشبورد</span>
                </a>


                <a
                    href="{{ route('admin.users.create') }}"
                    class="sidebar-link"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>

                    <span>ساخت کاربر</span>
                </a>


                <a
                    href="{{ route('admin.classes.create') }}"
                    class="sidebar-link"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 10L12 5 2 10l10 5 10-5Z"/>
                        <path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>
                        <path d="M22 10v6"/>
                    </svg>

                    <span>ساخت کلاس جدید</span>
                </a>


                <a
                    href="{{ route('admin.users.index') }}"
                    class="sidebar-link"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 1a3 3 0 10-6 0"/>
                    </svg>

                    <span>مدیریت کاربران</span>
                </a>

            @elseif(auth()->user()->role === 'therapist')

                <a
                    href="{{ route('therapist.dashboard') }}"
                    class="sidebar-link"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l9-9 9 9M5 10v10h14V10"/>
                    </svg>

                    <span>داشبورد</span>
                </a>

                <a href="#" class="sidebar-link">
                    <span>کلاس‌ها</span>
                </a>

            @elseif(auth()->user()->role === 'student')

                {{-- Dashboard --}}
                <a
                    href="{{ route('student.dashboard') }}"
                    class="sidebar-link"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l9-9 9 9M5 10v10h14V10"/>
                    </svg>

                    <span>داشبورد</span>
                </a>


                {{-- Classes --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 12v5c2 2 5 3 7 3s5-1 7-3v-5"/>
                    </svg>

                    <span>کلاس‌ها</span>

                </a>


                {{-- Online Sessions --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 10l4.5-2.5v9L15 14m-9 3h7a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>

                    <span>جلسه‌های آنلاین</span>

                </a>


                {{-- Exams --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 4h6m-7 4h8m-9 4h10m-9 4h6"/>
                    </svg>

                    <span>آزمون‌ها</span>

                </a>


                {{-- Tasks --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-6m3 6V9m-9 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>

                    <span>تکالیف</span>

                </a>


                {{-- Evaluations --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 11l3 3L22 4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 20h14"/>
                    </svg>

                    <span>ارزیابی‌ها</span>

                </a>


                {{-- Calendar --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="17" rx="2"
                              stroke-width="2"/>
                        <path stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>

                    <span>تقویم</span>

                </a>


                {{-- Documents --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 2h9l5 5v15H6a2 2 0 01-2-2V4a2 2 0 012-2z"/>
                    </svg>

                    <span>مدارک</span>

                </a>


                {{-- Courses --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 12v5c2 2 5 3 7 3s5-1 7-3v-5"/>
                    </svg>

                    <span>ثبت‌نام در دوره‌ها</span>

                </a>


                {{-- Notifications --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.5-2V10a6.5 6.5 0 00-13 0v5L4 17h5m6 0a3 3 0 01-6 0"/>
                    </svg>

                    <span>تابلوی اعلانات</span>

                </a>


                {{-- Messages --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 11.5a8.4 8.4 0 01-9 8.5 9.3 9.3 0 01-4.3-1.1L3 20l1.5-4.5A8.5 8.5 0 1112 20"/>
                    </svg>

                    <span>پیام‌رسان</span>

                </a>


                {{-- Tickets --}}
                <a href="#" class="sidebar-link">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 5h16v14H4z"/>
                        <path stroke-width="2" d="M8 9h8M8 13h5"/>
                    </svg>

                    <span>تیکت‌های پشتیبانی</span>

                </a>

            @endif

        </nav>


        {{-- Bottom sidebar --}}
        <div class="border-t border-slate-700 p-4">

            <a href="#" class="sidebar-link">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10.5 3h3l.4 2.1a7.5 7.5 0 0 1 1.8.8l1.8-1.1 2.1 2.1-1.1 1.8c.3.6.6 1.2.8 1.8L21 11v3l-2.1.4a7.5 7.5 0 0 1-.8 1.8l1.1 1.8-2.1 2.1-1.8-1.1a7.5 7.5 0 0 1-1.8.8L13.5 22h-3l-.4-2.1a7.5 7.5 0 0 1-1.8-.8l-1.8 1.1-2.1-2.1 1.1-1.8a7.5 7.5 0 0 1-.8-1.8L2.5 14v-3l2.1-.4a7.5 7.5 0 0 1 .8-1.8L4.3 7l2.1-2.1 1.8 1.1a7.5 7.5 0 0 1 1.8-.8L10.5 3Z"
                    />
                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                </svg>

                <span>راهنما</span>

            </a>


            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-1"
            >
                @csrf

                <button
                    type="submit"
                    class="sidebar-link w-full"
                >

                <svg class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12H3m0 0 4-4m-4 4 4 4"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 5V4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1"
                        />
                    </svg>


                    <span>خروج</span>

                </button>

            </form>

        </div>

    </aside>


    {{-- ========================================= --}}
    {{-- MAIN --}}
    {{-- ========================================= --}}

    <div class="min-h-screen lg:mr-[240px]">


        {{-- ========================================= --}}
        {{-- HEADER --}}
        {{-- ========================================= --}}

        <header
            class="
                sticky
                top-0
                z-40
                border-b
                border-gray-200
                bg-[#F7F6F3]/95
                px-5
                py-4
                backdrop-blur
                lg:px-8
            "
        >

            <div class="flex items-center justify-between">


                {{-- User --}}
                <div class="flex items-center gap-3">

                    <div
                        class="
                            flex
                            h-11
                            w-11
                            items-center
                            justify-center
                            rounded-full
                            bg-[#E9C72A]
                            font-bold
                            text-[#061C32]
                        "
                    >
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>

                    <div>

                        <div class="text-sm font-bold">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ \Morilog\Jalali\Jalalian::now()->format('l j F Y') }}
                            -
                            {{-- Time --}}
                            {{ now()->format('H:i') }}
                        </div>
                    </div>

                </div>

            </div>

        </header>


        {{-- ========================================= --}}
        {{-- PAGE CONTENT --}}
        {{-- ========================================= --}}

        <main class="px-4 py-6 sm:px-6 lg:px-8">

            {{ $slot }}

        </main>

    </div>


    {{-- ========================================= --}}
    {{-- MOBILE BOTTOM NAVIGATION --}}
    {{-- ========================================= --}}

    <nav
        class="
            fixed
            bottom-0
            right-0
            left-0
            z-50
            border-t
            border-gray-200
            bg-[#061C32]
            lg:hidden
        "
    >

        <div class="grid h-[72px] grid-cols-5">
            <a
                href="{{ auth()->user()->role === 'student'
                    ? route('student.dashboard')
                    : (auth()->user()->role === 'admin'
                        ? route('admin.dashboard')
                        : route('therapist.dashboard')) }}"
                class="mobile-nav-item"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l9-9 9 9M5 10v10h14V10"/>
                </svg>

                <span>خانه</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="w-full mt-4.5" >
                @csrf
                
                <button type="submit" class="mobile-nav-item w-full" >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                    
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12H3m0 0 4-4m-4 4 4 4"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 5V4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1"
                        />
                    </svg>
                    
                    <span>خروج</span>
                </button>
            </form>

        @if(auth()->user()->role === 'student')

            <a href="#" class="mobile-nav-item">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 12v5c2 2 5 3 7 3s5-1 7-3v-5"/>
                </svg>

                <span>کلاس‌ها</span>

            </a>


            <a href="#" class="mobile-nav-item">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 11l3 3L22 4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 20h14"/>
                </svg>

                <span>آزمون‌ها</span>

            </a>

        @endif

        @if(auth()->user()->role === 'admin')

            <a href="{{ route('admin.users.create') }}" class="mobile-nav-item" >
                
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>

                <span>ساخت کاربر</span>
            </a>

            <a href="{{ route('admin.classes.create') }}" class="mobile-nav-item">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M22 10L12 5 2 10l10 5 10-5Z"/>
                    <path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>
                    <path d="M22 10v6"/>
                </svg>

                <span>ساخت کلاس </span>
            </a>

        @endif

            <button
                type="button"
                class="mobile-nav-item fixed left-5 mt-4.5"
                onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
            >

                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-width="2"
                          d="M4 7h16M4 12h16M4 17h16"/>
                </svg>

                <span>بیشتر</span>

            </button>

        </div>

    </nav>


    {{-- Mobile menu --}}
    <div
        id="mobile-menu"
        class="
            fixed
            bottom-[72px]
            right-0
            left-0
            z-40
            hidden
            rounded-t-3xl
            bg-[#061C32]
            p-5
            text-white
            shadow-2xl
            lg:hidden
        "
    >

        <div class="grid grid-cols-2 gap-3">

            @if(auth()->user()->role === 'student')

            <a href="#" class="mobile-menu-item">
                تکالیف
            </a>

            <a href="#" class="mobile-menu-item">
                ارزیابی‌ها
            </a>

            <a href="#" class="mobile-menu-item">
                تقویم
            </a>

            <a href="#" class="mobile-menu-item">
                مدارک
            </a>

            <a href="#" class="mobile-menu-item">
                پیام‌رسان
            </a>
            
            <a href="#" class="mobile-menu-item">
                پشتیبانی
            </a>

            <a href="#" class="mobile-menu-item">
                راهنما
            </a>

            @endif

            @if(auth()->user()->role === 'admin')
            
                <a href="{{ route('admin.users.index') }}" class="mobile-menu-item">
                    مدیریت کاربران
                </a>

            @endif

        </div>

    </div>

@else

    {{ $slot }}

@endauth


@livewireScripts

</body>
</html>