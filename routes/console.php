<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// سحب ومزامنة أحدث طلبات وتحديثات ترينديول تلقائياً كل 15 دقيقة
Schedule::command('trendyol:sync-orders --pages=2')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// مزامنة فواتير قيود المعلقة تلقائياً كل 10 دقائق
Schedule::command('qoyod:sync-invoices --limit=50')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

