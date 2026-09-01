<?php

use Illuminate\Http\Request;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('/', 'auth.login')->name('login');
});

Route::middleware('auth')->post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Volt::route('/admin/users/create', 'admin.create-user')->name('admin.users.create');
    Volt::route('/admin/classes/create', 'admin.create-class')->name('admin.classes.create');
    Volt::route('/admin/classes/{classId}/students', 'admin.manage-class-students')->name('admin.classes.students');
    Volt::route('/admin/users', 'admin.manage-users')->name('admin.users.index');
});

Route::middleware(['auth', 'role:therapist'])->group(function () {
    Volt::route('/therapist/dashboard', 'therapist.dashboard')->name('therapist.dashboard');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Volt::route('/student/dashboard', 'student.dashboard')->name('student.dashboard');
});

