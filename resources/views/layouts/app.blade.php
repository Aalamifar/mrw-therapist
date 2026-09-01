<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRW Therapist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">

    @auth
    <div class="flex min-h-screen">
        {{-- منو (سایدبار) --}}
        <aside class="w-64 bg-slate-900 text-white p-4">
            <div class="mb-6 text-xl font-bold text-center">MRW</div>

            <nav class="space-y-2">
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-700">داشبورد</a>
                    <a href="{{ route('admin.users.create') }}" class="block px-3 py-2 rounded hover:bg-slate-700">ساخت کاربر</a>
                    <a href="{{ route('admin.classes.create') }}" class="block px-3 py-2 rounded hover:bg-slate-700">ساخت کلاس جدید</a>
                    <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">مدیریت کاربران</a>
                @elseif(auth()->user()->role === 'therapist')
                    <a href="{{ route('therapist.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-700">داشبورد</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-slate-700">کلاس‌ها</a>
                @elseif(auth()->user()->role === 'student')
                    <a href="{{ route('student.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-700">داشبورد</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-slate-700">کلاس‌های من</a>
                @endif
            </nav>
        </aside>

        {{-- بخش اصلی --}}
        <div class="flex-1">
            {{-- هدر --}}
            <header class="flex items-center justify-between px-6 py-4 bg-white shadow">
                        <div class="font-bold">{{ auth()->user()->name }}</div>

                        <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-500">
                            {{ \Morilog\Jalali\Jalalian::now()->format('l j F Y') }} - {{ now()->format('H:i') }}
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-white bg-red-600 px-4 py-1.5 rounded font-bold hover:underline">خروج</button>
                    </form>
                </div>
            </header>

            {{-- محتوای هر صفحه اینجا میاد --}}
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @else
        {{ $slot }}
    @endauth

    @livewireScripts
</body>
</html>