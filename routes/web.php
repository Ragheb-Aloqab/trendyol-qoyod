<?php

use Illuminate\Support\Facades\Route;

// توجيه المستخدم فور دخوله إلى صفحة تسجيل الدخول (أو لوحة التحكم إن كان مسجل الدخول بالفعل)
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('filament.admin.pages.dashboard')
        : redirect()->route('filament.admin.auth.login');
});

// توجيه المسار العام /login إلى /admin/login لضمان توافق مسارات المصادقة
Route::redirect('/login', '/admin/login')->name('login');

// توجيه المسار العام /profile إلى /admin/profile
Route::redirect('/profile', '/admin/profile');

