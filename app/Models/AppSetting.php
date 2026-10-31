<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Retrieve a setting value by key with cache and default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting !== null ? $setting->value : $default;
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Store or update a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("app_setting_{$key}");
    }
}
