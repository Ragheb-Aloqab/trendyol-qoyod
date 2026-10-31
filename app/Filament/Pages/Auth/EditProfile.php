<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public static function getLabel(): string
    {
        return 'إعدادات الحساب';
    }

    public function getTitle(): string | Htmlable
    {
        return 'إعدادات الحساب والأمان';
    }

    public function getSubheading(): ?string
    {
        return 'تعديل البيانات الشخصية، تحديث كلمة المرور، وإدارة الأجهزة والجلسات النشطة';
    }

    public function getMaxWidth(): \Filament\Support\Enums\Width | string | null
    {
        return \Filament\Support\Enums\Width::SevenExtraLarge;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الشخصية')
                    ->description('تحديث اسم المستخدم والبريد الإلكتروني المسجل في النظام')
                    ->icon('heroicon-o-user')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),
                Section::make('أمان الحساب وكلمة المرور')
                    ->description('لتغيير كلمة المرور، أدخل كلمة المرور الحالية للتحقق ثم كلمة المرور الجديدة')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        $this->getCurrentPasswordFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
                View::make('filament.pages.auth.sessions-manager'),
            ]);
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('الاسم الكامل')
            ->required()
            ->maxLength(255)
            ->prefixIcon('heroicon-m-user')
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('البريد الإلكتروني')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->prefixIcon('heroicon-m-envelope');
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label('كلمة المرور الحالية')
            ->password()
            ->revealable()
            ->currentPassword(guard: Filament::getAuthGuard())
            ->prefixIcon('heroicon-m-lock-closed')
            ->required(fn ($get): bool => filled($get('password')) || ($get('email') !== $this->getUser()->getAttributeValue('email')))
            ->visible(fn ($get): bool => filled($get('password')) || ($get('email') !== $this->getUser()->getAttributeValue('email')))
            ->dehydrated(false);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('كلمة المرور الجديدة')
            ->password()
            ->revealable()
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation')
            ->prefixIcon('heroicon-m-key');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('تأكيد كلمة المرور الجديدة')
            ->password()
            ->revealable()
            ->autocomplete('new-password')
            ->required(fn ($get): bool => filled($get('password')))
            ->visible(fn ($get): bool => filled($get('password')))
            ->prefixIcon('heroicon-m-check-badge')
            ->dehydrated(false);
    }

    public function getSessions(): Collection
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            return collect();
        }

        $currentSessionId = session()->getId();

        return DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $agent = $this->parseUserAgent($session->user_agent);

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address ?: '127.0.0.1',
                    'is_current' => $session->id === $currentSessionId,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'platform' => $agent['platform'],
                    'browser' => $agent['browser'],
                    'device' => $agent['device'],
                    'icon' => $agent['icon'],
                ];
            });
    }

    public function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'device' => 'حاسوب مكتبي / محمول',
                'browser' => 'متصفح ويب',
                'platform' => 'نظام غير محدد',
                'icon' => 'heroicon-o-computer-desktop',
            ];
        }

        // Operating System
        $platform = 'نظام غير محدد';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Browser
        $browser = 'متصفح ويب';
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        // Device Type
        $device = 'حاسوب (Desktop / Laptop)';
        $icon = 'heroicon-o-computer-desktop';
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            $device = 'جهاز لوحي (Tablet)';
            $icon = 'heroicon-o-device-tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            $device = 'هاتف ذكي (Mobile)';
            $icon = 'heroicon-o-device-phone-mobile';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'device' => $device,
            'icon' => $icon,
        ];
    }

    public function revokeSession(string $sessionId): void
    {
        if ($sessionId === session()->getId()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirect(Filament::getLoginUrl());

            return;
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->delete();

        Notification::make()
            ->title('تم إنهاء الجلسة وتسجيل الخروج من هذا الجهاز بنجاح')
            ->success()
            ->send();
    }

    public function logoutOtherDevices(): void
    {
        $count = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', session()->getId())
            ->delete();

        Notification::make()
            ->title("تم تسجيل الخروج بنجاح من كافة الأجهزة والجلسات الأخرى ({$count} جهاز)")
            ->success()
            ->send();
    }
}
