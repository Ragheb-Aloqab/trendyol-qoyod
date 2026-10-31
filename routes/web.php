<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// توجيه المسار الرئيسي فوراً إلى لوحة التحكم /admin بمسار نسبي متوافق مع أي منفذ (مثل :9000 أو :80 أو أي نطاق)
Route::get('/', function (Request $request) {
    $queryString = $request->getQueryString();
    $target = '/admin' . ($queryString ? '?' . $queryString : '');

    return response('', 302, ['Location' => $target]);
});

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

