<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $phone = '';
    public string $password = '';
    public string $error = '';

    public function login()
    {
        $this->error = '';

        $this->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt([
            'phone' => $this->phone,
            'password' => $this->password,
        ])) {
            session()->regenerate();

            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'therapist' => redirect()->route('therapist.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                default => redirect('/'),
            };
        }

        $this->error = 'شماره تلفن یا رمز عبور اشتباه است.';
    }
}; ?>

<div dir="rtl" class="min-h-screen bg-[#020b1c] text-white overflow-x-hidden">
    <!-- Navigation Bar -->
   <nav class="fixed top-0 w-full bg-[#020b1c]/95 backdrop-blur-md border-b border-gray-800/50 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-6 text-sm">
                <a href="#" class="text-yellow-500 hover:text-yellow-400 transition">صفحه اصلی</a>
                <a href="#" class="text-gray-400 hover:text-white transition">سوالات متداول</a>
                <a href="#" class="text-gray-400 hover:text-white transition">تماس با ما</a>

                {{-- محصولات (dropdown) --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-1 text-gray-400 hover:text-white transition">
                        محصولات
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition"
                            :class="open ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition x-cloak
                        class="absolute right-0 mt-3 w-48 rounded-xl border border-gray-800 bg-[#0b1826] py-2 shadow-xl">
                        <a href="{{ route('courses.index') }}"
                        class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-yellow-500">
                            دوره‌ها
                        </a>
                        <a href="{{ route('course.access.form') }}"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-yellow-500">
                            ورود به دوره
                        </a>
                    </div>
                </div>
            </div>

            <button class="text-gray-400 hover:text-white text-2xl"></button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-24 min-h-screen">
        <!-- Hero Section -->
        <div class="relative min-h-[calc(100vh-100px)] overflow-hidden">
            
            <!-- Background Effects -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/20 via-[#020b1c] to-[#020b1c]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 rounded-full blur-3xl"></div>
            
            <!-- Floating Elements Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-20 left-20 w-32 h-40 bg-yellow-500/10 rounded-lg rotate-12 animate-pulse"></div>
                <div class="absolute top-40 left-40 w-24 h-32 bg-blue-500/10 rounded-lg -rotate-6"></div>
                <div class="absolute top-32 left-60 w-20 h-20 bg-yellow-500/20 rounded-full"></div>
                <div class="absolute top-60 left-32 text-6xl text-yellow-500/20">?</div>
                <div class="absolute top-20 left-1/2 text-5xl text-blue-500/20"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    
                    <!-- Left Side - Login Form & Illustration -->
                    <div class="space-y-8">
                        <!-- Boy Illustration Container -->
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/20 to-blue-500/20 blur-2xl rounded-full"></div>
                            <div class="relative flex items-center justify-center">
                                <!-- Replace with actual illustration -->
                                <div class="w-80 h-96 bg-gradient-to-br from-blue-600/30 to-yellow-600/20 rounded-3xl flex items-center justify-center border border-yellow-500/30">
                                    <img src="{{ asset('img/boy-illustration.png') }}" 
                                         alt="کودک در حال یادگیری" 
                                         class="w-full h-full object-contain p-3.5 rounded-full">
                                </div>
                                
                                <!-- Floating Books/Papers -->
                                <div class="absolute -top-4 -right-4 w-24 h-32 bg-yellow-400/90 rounded-lg shadow-2xl rotate-12 flex items-center justify-center text-[#020b1c] font-bold text-xs p-2">
                                    خواندن
                                </div>
                                <div class="absolute -bottom-4 -left-4 w-28 h-20 bg-blue-500/90 rounded-lg shadow-2xl -rotate-6 flex items-center justify-center text-white font-bold text-xs p-2">
                                    نوشتن
                                </div>
                                <div class="absolute top-1/2 -right-8 w-20 h-20 bg-yellow-500/80 rounded-lg shadow-xl rotate-45 flex items-center justify-center text-[#020b1c] text-xs font-bold">
                                    یادگیری
                                </div>
                            </div>
                        </div>

                        <!-- Login Form Card -->
                        <div class="bg-[#071326]/80 backdrop-blur-xl rounded-2xl border border-yellow-500/20 p-6 max-w-sm mx-auto">
                            <div class="flex items-center gap-2 mb-6 justify-center">
                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-bold text-white">ورود به حساب کاربری</h3>
                            </div>

                            @if ($error)
                                <div class="mb-4 rounded-lg border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                                    {{ $error }}
                                </div>
                            @endif

                            <form wire:submit="login" class="space-y-4">
                                <!-- Phone Input -->
                                <div>
                                    <div class="relative">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </span>
                                        <input type="text" 
                                               wire:model="phone" 
                                               dir="ltr" 
                                               placeholder="نام کاربری"
                                               class="w-full bg-[#020b1c] border border-gray-700 rounded-lg py-2.5 pr-10 pl-4 text-left text-white placeholder-gray-600 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 outline-none transition">
                                    </div>
                                </div>

                                <!-- Password Input -->
                                <div>
                                    <div class="relative">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </span>
                                        <input type="password" 
                                               wire:model="password" 
                                               placeholder="رمز عبور"
                                               class="w-full text-left bg-[#020b1c] border border-gray-700 rounded-lg py-2.5 pr-10 pl-4 text-white placeholder-gray-600 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 outline-none transition">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" 
                                        wire:loading.attr="disabled"
                                        class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-400 hover:to-yellow-500 text-[#020b1c] font-bold py-3 rounded-lg transition disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="login">ورود</span>
                                    <span wire:loading wire:target="login">در حال ورود...</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Side - MRW Branding -->
                    <div class="text-center lg:text-right space-y-8">
                        <!-- MRW Logo with Effects -->
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/30 via-yellow-500/30 to-blue-600/30 blur-3xl rounded-full animate-pulse"></div>
                            <div class="relative">
                                <h1 class="text-7xl lg:text-8xl font-black tracking-wider">
                                    <span class="bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">M</span>
                                    <span class="bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 bg-clip-text text-transparent">R</span>
                                    <span class="bg-gradient-to-r from-yellow-500 to-yellow-600 bg-clip-text text-transparent">W</span>
                                </h1>
                            </div>
                        </div>

                        <!-- Main Title -->
                        <div class="space-y-3">
                            <h2 class="text-3xl lg:text-4xl font-bold text-white">
                                درمانگر اختلالات یادگیری
                            </h2>
                            <p class="text-xl text-yellow-500 font-medium">
                                (ریاضی، خواندن و نوشتن)
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="bg-[#071326]/60 backdrop-blur rounded-xl p-6 border border-yellow-500/20">
                            <h3 class="text-yellow-500 font-bold text-lg mb-4">همراه کودکان در مسیر یادگیری</h3>
                            <p class="text-gray-300 leading-relaxed text-sm lg:text-base">
                                با تشخیص دقیق، برنامه‌های تخصصی و حمایت هدفمند به کودکان کمک می‌کنیم 
                                تا توانایی‌های خود را کشف کنند و با اعتماد به نفس پیشرفت کنند.
                            </p>
                        </div>

                        <!-- Features Grid -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-[#071326]/60 backdrop-blur rounded-xl p-4 border border-yellow-500/20 text-center hover:border-yellow-500/40 transition group">
                                <div class="text-4xl mb-2 group-hover:scale-110 transition">🧩</div>
                                <div class="text-xs text-gray-300">ارزیابی تخصصی و دقیق</div>
                            </div>
                            <div class="bg-[#071326]/60 backdrop-blur rounded-xl p-4 border border-yellow-500/20 text-center hover:border-yellow-500/40 transition group">
                                <div class="text-4xl mb-2 group-hover:scale-110 transition">📖</div>
                                <div class="text-xs text-gray-300">برنامه درمانی فردی</div>
                            </div>
                            <div class="bg-[#071326]/60 backdrop-blur rounded-xl p-4 border border-yellow-500/20 text-center hover:border-yellow-500/40 transition group">
                                <div class="text-4xl mb-2 group-hover:scale-110 transition">🧠</div>
                                <div class="text-xs text-gray-300">تقویت مهارت‌های شناختی</div>
                            </div>
                            <div class="bg-[#071326]/60 backdrop-blur rounded-xl p-4 border border-yellow-500/20 text-center hover:border-yellow-500/40 transition group">
                                <div class="text-4xl mb-2 group-hover:scale-110 transition">❤️</div>
                                <div class="text-xs text-gray-300">حمایت و همراهی مداوم</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Specialized Methods Section -->
        <section class="bg-white text-[#020b1c] py-16 px-6 relative">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-500 to-transparent"></div>
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold mb-3">روش‌های تخصصی</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-yellow-500 to-blue-500 mx-auto rounded-full"></div>
                    <p class="text-gray-600 mt-4">استفاده از روش‌های علمی و کاربردی به روز</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 hover:border-yellow-500/50 hover:shadow-xl transition text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:bg-yellow-100 transition">
                            <svg class="w-8 h-8 text-blue-600 group-hover:text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="font-bold mb-2 text-sm">درمان اختلال خواندن</h3>
                    </div>

                    <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 hover:border-yellow-500/50 hover:shadow-xl transition text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:bg-yellow-100 transition">
                            <svg class="w-8 h-8 text-blue-600 group-hover:text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold mb-2 text-sm">درمان اختلال نوشتن</h3>
                    </div>

                    <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 hover:border-yellow-500/50 hover:shadow-xl transition text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:bg-yellow-100 transition">
                            <svg class="w-8 h-8 text-blue-600 group-hover:text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold mb-2 text-sm">تقویت حافظه و توجه</h3>
                    </div>

                    <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 hover:border-yellow-500/50 hover:shadow-xl transition text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:bg-yellow-100 transition">
                            <div class="flex gap-1">
                                <span class="text-lg font-bold text-blue-600 group-hover:text-yellow-600">A</span>
                                <span class="text-lg font-bold text-blue-600 group-hover:text-yellow-600">B</span>
                            </div>
                        </div>
                        <h3 class="font-bold mb-2 text-sm">آموزش مهارت‌های پایه‌ای</h3>
                    </div>

                    <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 hover:border-yellow-500/50 hover:shadow-xl transition text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:bg-yellow-100 transition">
                            <svg class="w-8 h-8 text-blue-600 group-hover:text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold mb-2 text-sm">مشاوره والدین و معلمان</h3>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#020b1c] border-t border-gray-800 py-8 px-6">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center text-yellow-500 hover:bg-yellow-500 hover:text-[#020b1c] transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <span class="text-gray-400">@Therapist_RW</span>
                </div>
                <div class="text-gray-500 text-sm">
                    © 2026 مرکز اختلالات یادگیری MRW. تمامی حقوق محفوظ است.
                </div>
            </div>
        </footer>
    </div>
</div>