<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية للموقع مع روابط الانتقال لتسجيل الدخول ولوحة التحكم
Route::get('/', function () {
    return view('welcome');
})->name('home');

// توجيه المسار العام /login إلى /admin/login بمسار نسبي متوافق مع كافة المنافذ
Route::get('/login', function (Request $request) {
    $queryString = $request->getQueryString();
    $target = '/admin/login' . ($queryString ? '?' . $queryString : '');

    return response('', 302, ['Location' => $target]);
})->name('login');

// توجيه المسار العام /profile إلى /admin/profile
Route::get('/profile', function () {
    return response('', 302, ['Location' => '/admin/profile']);
});

