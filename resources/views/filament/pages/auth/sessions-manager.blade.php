@php
    $sessions = $this->getSessions();
    $otherSessionsCount = $sessions->where('is_current', false)->count();
@endphp

<div style="margin-top: 2rem;">
    <style>
        .tq-session-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.25rem;
        }
        .tq-session-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            flex-wrap: wrap;
            gap: 1.25rem;
            transition: all 0.2s ease;
        }
        .dark .tq-session-card {
            background: #111827;
            border-color: #1f2937;
        }
        .tq-session-current {
            border-color: #86efac;
            background: #f0fdf4;
        }
        .dark .tq-session-current {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.3);
        }
        .tq-session-info-group {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex: 1;
            min-width: 280px;
        }
        .tq-session-icon-box {
            padding: 0.875rem;
            border-radius: 0.75rem;
            background: #f3f4f6;
            color: #4b5563;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .dark .tq-session-icon-box {
            background: #1f2937;
            color: #9ca3af;
        }
        .tq-session-current .tq-session-icon-box {
            background: #dcfce7;
            color: #15803d;
        }
        .dark .tq-session-current .tq-session-icon-box {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }
        .tq-session-title {
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex-wrap: wrap;
        }
        .tq-session-meta {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .dark .tq-session-meta {
            color: #9ca3af;
        }
        .tq-session-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }
    </style>

    <x-filament::section
        icon="heroicon-o-device-phone-mobile"
        heading="الأجهزة والجلسات النشطة (Active Browser Sessions)"
        description="قائمة بجميع الأجهزة والمتصفحات التي سجلت الدخول بحسابك في النظام. يمكنك مراجعة نوع الجهاز والمتصفح وعنوان IP وإنهاء أو حظر أي جلسة مشبوهة فوراً."
    >
        @if ($otherSessionsCount > 0)
            <x-slot name="afterHeader">
                <x-filament::button
                    color="danger"
                    size="sm"
                    icon="heroicon-m-arrow-left-on-rectangle"
                    wire:click="logoutOtherDevices"
                    wire:confirm="هل أنت متأكد من رغبتك في تسجيل الخروج من كافة الأجهزة والجلسات الأخرى؟"
                >
                    تسجيل الخروج من كافة الأجهزة الأخرى ({{ $otherSessionsCount }})
                </x-filament::button>
            </x-slot>
        @endif

        @if ($sessions->isEmpty())
            <div style="padding: 2.5rem; text-align: center; color: #6b7280;">
                <x-filament::icon icon="heroicon-o-shield-check" style="width: 3rem; height: 3rem; margin: 0 auto 0.75rem auto; color: #10b981;" />
                <p style="font-weight: 600;">لا توجد جلسات أخرى مسجلة بحسابك حالياً.</p>
            </div>
        @else
            <div class="tq-session-list">
                @foreach ($sessions as $session)
                    <div class="tq-session-card {{ $session['is_current'] ? 'tq-session-current' : '' }}">
                        <div class="tq-session-info-group">
                            <div class="tq-session-icon-box">
                                <x-filament::icon :icon="$session['icon']" style="width: 1.75rem; height: 1.75rem;" />
                            </div>

                            <div>
                                <div class="tq-session-title">
                                    <span style="font-weight: 800; color: #111827;" class="dark:text-white" dir="ltr">{{ $session['browser'] }}</span>
                                    <span style="color: #9ca3af;">•</span>
                                    <span style="color: #2563eb; font-weight: 700;" dir="ltr">{{ $session['platform'] }}</span>
                                    <span style="color: #9ca3af;">•</span>
                                    <span style="color: #4b5563; font-size: 0.85rem;" class="dark:text-gray-300">{{ $session['device'] }}</span>

                                    @if ($session['is_current'])
                                        <x-filament::badge color="success" icon="heroicon-m-check-circle" size="sm">
                                            هذا الجهاز (الجلسة الحالية)
                                        </x-filament::badge>
                                    @endif
                                </div>

                                <div class="tq-session-meta">
                                    <span><strong>عنوان الـ IP:</strong> <code style="font-family: monospace; background: #f3f4f6; padding: 0.15rem 0.4rem; border-radius: 0.25rem;" class="dark:bg-gray-800" dir="ltr">{{ $session['ip_address'] }}</code></span>
                                    <span>•</span>
                                    <span><strong>آخر نشاط:</strong> {{ $session['last_active'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="tq-session-actions">
                            @if (! $session['is_current'])
                                <x-filament::button
                                    color="danger"
                                    size="sm"
                                    icon="heroicon-m-trash"
                                    wire:click="revokeSession('{{ $session['id'] }}')"
                                    wire:confirm="هل أنت متأكد من رغبتك في إنهاء هذه الجلسة وتسجيل الخروج من هذا الجهاز؟"
                                >
                                    إنهاء وحظر الجلسة
                                </x-filament::button>
                            @else
                                <span style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #16a34a; font-weight: 700;">
                                    <span style="width: 0.5rem; height: 0.5rem; border-radius: 9999px; background: #10b981; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></span>
                                    <span>نشط الآن</span>
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</div>
