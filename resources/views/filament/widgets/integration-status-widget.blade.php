<x-filament-widgets::widget>
    <style>
        .tq-dashboard-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
        }
        .tq-header-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .tq-header-card {
            background: #111827;
            border-color: #1f2937;
        }
        .tq-header-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .tq-header-title {
            color: #f9fafb;
        }
        .tq-header-subtitle {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .dark .tq-header-subtitle {
            color: #9ca3af;
        }
        .tq-header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .tq-two-col-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
        @media (min-width: 1024px) {
            .tq-two-col-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .tq-param-table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #f3f4f6;
            background: #f9fafb;
            font-size: 0.75rem;
        }
        .dark .tq-param-table {
            background: #1f2937;
            border-color: #374151;
        }
        .tq-param-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.875rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .tq-param-row {
            border-bottom-color: #374151;
        }
        .tq-param-row:last-child {
            border-bottom: none;
        }
        .tq-param-label {
            color: #6b7280;
            font-weight: 500;
        }
        .dark .tq-param-label {
            color: #9ca3af;
        }
        .tq-param-value {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 600;
            color: #111827;
        }
        .dark .tq-param-value {
            color: #f9fafb;
        }
        .tq-live-box {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .tq-live-box {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
        }
        .tq-live-box-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #166534;
        }
        .dark .tq-live-box-title {
            color: #6ee7b7;
        }
        .tq-live-box-count {
            font-size: 1.5rem;
            font-weight: 900;
            font-family: ui-monospace, monospace;
            color: #111827;
            margin-top: 0.25rem;
        }
        .dark .tq-live-box-count {
            color: #ffffff;
        }
        .tq-pipeline-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
        }
        @media (min-width: 640px) {
            .tq-pipeline-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .tq-pipeline-grid {
                grid-template-columns: repeat(7, minmax(0, 1fr));
            }
        }
        .tq-pipeline-card {
            padding: 0.75rem 0.5rem;
            border-radius: 0.5rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .dark .tq-pipeline-card {
            background: #1f2937;
            border-color: #374151;
        }
        .tq-pipeline-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: #6b7280;
            display: block;
            margin-bottom: 0.25rem;
        }
        .dark .tq-pipeline-label {
            color: #9ca3af;
        }
        .tq-pipeline-count {
            font-size: 1.15rem;
            font-weight: 800;
            font-family: ui-monospace, monospace;
            color: #111827;
        }
        .dark .tq-pipeline-count {
            color: #f9fafb;
        }
        .tq-system-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        @media (min-width: 768px) {
            .tq-system-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .tq-system-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 0.75rem;
        }
        .dark .tq-system-card {
            background: #1f2937;
            border-color: #374151;
        }
        .tq-card-footer-link {
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
        }
        .dark .tq-card-footer-link {
            border-top-color: #374151;
        }
    </style>

    <div class="tq-dashboard-wrapper">

        {{-- 1. Main Action Header Strip --}}
        <div class="tq-header-card">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="padding: 0.625rem; background: #f59e0b; color: white; border-radius: 0.625rem; display: flex;">
                    <x-filament::icon icon="heroicon-o-cpu-chip" style="width: 1.5rem; height: 1.5rem;" />
                </div>
                <div>
                    <h2 class="tq-header-title">
                        <span>مركز مراقبة التكامل والربط المباشر</span>
                        <x-filament::badge color="gray" icon="heroicon-m-clock">
                            آخر فحص: {{ $lastCheckedAt }}
                        </x-filament::badge>
                    </h2>
                    <p class="tq-header-subtitle">
                        متابعة حية لحالة الاتصال بخوادم ترينديول وقيود وقاعدة البيانات وطابور المزامنة
                    </p>
                </div>
            </div>

            <div class="tq-header-actions">
                @if (($orderStats['failed'] ?? 0) > 0)
                    <x-filament::button
                        color="danger"
                        size="sm"
                        icon="heroicon-m-arrow-path-rounded-square"
                        wire:click="retryFailedSyncs"
                    >
                        إعادة محاولة الفواتير المتعثرة ({{ $orderStats['failed'] }})
                    </x-filament::button>
                @endif

                <x-filament::button
                    color="primary"
                    size="sm"
                    icon="heroicon-m-arrow-path"
                    wire:click="runCheck"
                    wire:loading.attr="disabled"
                >
                    فحص الاتصال وتحديث البيانات
                </x-filament::button>
            </div>
        </div>

        {{-- 2. Primary Integration Cards (Trendyol & Qoyod) --}}
        <div class="tq-two-col-grid">

            {{-- 1. Trendyol Section --}}
            <x-filament::section
                icon="heroicon-o-shopping-bag"
                :heading="$trendyol['title']"
                :description="$trendyol['message']"
            >
                <x-slot name="afterHeader">
                    <x-filament::badge :color="$trendyol['status'] === 'success' ? 'success' : ($trendyol['status'] === 'warning' ? 'warning' : 'danger')">
                        @if ($trendyol['status'] === 'success')
                            متصل ومصرح
                        @elseif ($trendyol['status'] === 'warning')
                            إعدادات ناقصة
                        @else
                            خطأ في المصادقة ({{ $trendyol['httpCode'] ?? 401 }})
                        @endif
                    </x-filament::badge>
                </x-slot>

                {{-- Live Orders Display (When Connected) --}}
                @if (!empty($trendyol['connected']))
                    <div class="tq-live-box">
                        <div>
                            <span class="tq-live-box-title">إجمالي الطلبات في متجرك بـ ترينديول (Live API)</span>
                            <div class="tq-live-box-count">
                                {{ number_format($trendyol['totalOrders'] ?? 0) }}
                                <span style="font-size: 0.75rem; font-weight: normal; color: #6b7280;">طلب</span>
                            </div>
                        </div>
                        <div style="color: #10b981;">
                            <x-filament::icon icon="heroicon-o-check-circle" style="width: 2.25rem; height: 2.25rem;" />
                        </div>
                    </div>
                @endif

                {{-- Detailed Diagnosis Callout (When Error Occurs) --}}
                @if (!empty($trendyol['diagnosis']))
                    <div style="margin-top: 1rem;">
                        <x-filament::callout
                            color="danger"
                            icon="heroicon-o-exclamation-triangle"
                            :heading="$trendyol['diagnosis']"
                        >
                            @if (!empty($trendyol['actionSteps']))
                                <div style="margin-top: 0.5rem; font-size: 0.75rem;">
                                    <strong style="display: block; margin-bottom: 0.25rem;">خطوات الحل المقترحة في لوحة ترينديول:</strong>
                                    <ol style="list-style-type: decimal; padding-inline-start: 1.25rem; margin: 0; line-height: 1.6;">
                                        @foreach ($trendyol['actionSteps'] as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif

                            @if (!empty($trendyol['rawError']))
                                <details style="margin-top: 0.75rem; font-size: 0.7rem;">
                                    <summary style="cursor: pointer; font-weight: bold;">عرض الرد الخام من سيرفر ترينديول (Raw Response)</summary>
                                    <pre style="margin-top: 0.5rem; padding: 0.5rem; background: #111827; color: #f9fafb; border-radius: 0.375rem; overflow-x: auto; font-family: monospace; direction: ltr; text-align: left;">{{ $trendyol['rawError'] }}</pre>
                                </details>
                            @endif
                        </x-filament::callout>
                    </div>
                @endif

                {{-- Parameters Table --}}
                <div class="tq-param-table">
                    <div class="tq-param-row">
                        <span class="tq-param-label">معرف البائع (Supplier ID):</span>
                        <span class="tq-param-value" style="color: #d97706;">{{ $trendyol['supplierId'] ?? '-' }}</span>
                    </div>
                    <div class="tq-param-row">
                        <span class="tq-param-label">مفتاح API Key:</span>
                        <span class="tq-param-value">{{ $trendyol['apiKey'] ?? '-' }}</span>
                    </div>
                    <div class="tq-param-row">
                        <span class="tq-param-label">الحالات المعتمدة للفوترة:</span>
                        <span class="tq-param-value">{{ $trendyol['approvedStatuses'] ?? '-' }}</span>
                    </div>
                    <div class="tq-param-row">
                        <span class="tq-param-label">رابط الخدمة (Base URL):</span>
                        <span class="tq-param-value" style="font-size: 0.7rem;" dir="ltr">{{ $trendyol['baseUrl'] ?? '-' }}</span>
                    </div>
                    @if (!empty($trendyol['latency']))
                        <div class="tq-param-row">
                            <span class="tq-param-label">سرعة الاستجابة (Latency):</span>
                            <span class="tq-param-value" style="color: #10b981;">{{ $trendyol['latency'] }}</span>
                        </div>
                    @endif
                </div>

                {{-- Card Footer Link --}}
                <div class="tq-card-footer-link">
                    <span style="color: #6b7280;">شحنات ترينديول المسجلة محلياً: <strong>{{ $orderStats['totalOrders'] }}</strong></span>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <x-filament::button
                            size="xs"
                            color="warning"
                            icon="heroicon-m-arrow-path"
                            wire:click="syncTrendyolOrders"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="syncTrendyolOrders">سحب ومزامنة الطلبات</span>
                            <span wire:loading wire:target="syncTrendyolOrders">جاري السحب...</span>
                        </x-filament::button>

                        <x-filament::link :href="route('filament.admin.resources.trendyol-orders.index')" icon="heroicon-m-arrow-left">
                            استعراض الشحنات
                        </x-filament::link>
                    </div>
                </div>
            </x-filament::section>

            {{-- 2. Qoyod Section --}}
            <x-filament::section
                icon="heroicon-o-calculator"
                :heading="$qoyod['title']"
                :description="$qoyod['message']"
            >
                <x-slot name="afterHeader">
                    <x-filament::badge :color="$qoyod['status'] === 'success' ? 'success' : ($qoyod['status'] === 'warning' ? 'warning' : 'danger')">
                        @if ($qoyod['status'] === 'success')
                            متصل ومصرح
                        @elseif ($qoyod['status'] === 'warning')
                            إعدادات غير مكتملة
                        @else
                            تعذر الاتصال ({{ $qoyod['httpCode'] ?? 'Error' }})
                        @endif
                    </x-filament::badge>
                </x-slot>

                {{-- Live Inventories Display (When Connected) --}}
                @if (!empty($qoyod['connected']))
                    <div class="tq-live-box" style="background: #eff6ff; border-color: #bfdbfe;">
                        <div>
                            <span class="tq-live-box-title" style="color: #1e40af;">المستودعات المتاحة في قيود (Live)</span>
                            <div class="tq-live-box-count">
                                {{ number_format($qoyod['inventoriesCount'] ?? 0) }}
                                <span style="font-size: 0.75rem; font-weight: normal; color: #6b7280;">مستودع نشط</span>
                            </div>
                        </div>
                        <div style="color: #3b82f6;">
                            <x-filament::icon icon="heroicon-o-building-storefront" style="width: 2.25rem; height: 2.25rem;" />
                        </div>
                    </div>
                @endif

                {{-- Detailed Diagnosis Callout (When Warning/Error) --}}
                @if (!empty($qoyod['diagnosis']))
                    <div style="margin-top: 1rem;">
                        <x-filament::callout
                            :color="$qoyod['status'] === 'warning' ? 'warning' : 'danger'"
                            icon="heroicon-o-exclamation-triangle"
                            :heading="$qoyod['diagnosis']"
                        >
                            @if (!empty($qoyod['actionSteps']))
                                <div style="margin-top: 0.5rem; font-size: 0.75rem;">
                                    <strong style="display: block; margin-bottom: 0.25rem;">خطوات تفعيل ربط قيود:</strong>
                                    <ol style="list-style-type: decimal; padding-inline-start: 1.25rem; margin: 0; line-height: 1.6;">
                                        @foreach ($qoyod['actionSteps'] as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif

                            @if (!empty($qoyod['rawError']))
                                <details style="margin-top: 0.75rem; font-size: 0.7rem;">
                                    <summary style="cursor: pointer; font-weight: bold;">عرض الرد الخام من سيرفر قيود</summary>
                                    <pre style="margin-top: 0.5rem; padding: 0.5rem; background: #111827; color: #f9fafb; border-radius: 0.375rem; overflow-x: auto; font-family: monospace; direction: ltr; text-align: left;">{{ $qoyod['rawError'] }}</pre>
                                </details>
                            @endif
                        </x-filament::callout>
                    </div>
                @endif

                {{-- Parameters Table --}}
                <div class="tq-param-table">
                    <div class="tq-param-row">
                        <span class="tq-param-label">مفتاح API Key:</span>
                        <span class="tq-param-value">{{ $qoyod['apiKey'] ?? '-' }}</span>
                    </div>
                    <div class="tq-param-row">
                        <span class="tq-param-label">المستودع الافتراضي:</span>
                        <span class="tq-param-value" style="color: #3b82f6;">{{ $qoyod['inventoryId'] ?? '-' }}</span>
                    </div>
                    <div class="tq-param-row">
                        <span class="tq-param-label">ضريبة القيمة المضافة:</span>
                        <span class="tq-param-value">{{ $qoyod['taxId'] ?? 1 }} (15% VAT)</span>
                    </div>
                    <div class="tq-param-row">
                        <span class="tq-param-label">رابط الخدمة (Base URL):</span>
                        <span class="tq-param-value" style="font-size: 0.7rem;" dir="ltr">{{ $qoyod['baseUrl'] ?? '-' }}</span>
                    </div>
                    @if (!empty($qoyod['latency']))
                        <div class="tq-param-row">
                            <span class="tq-param-label">سرعة الاستجابة (Latency):</span>
                            <span class="tq-param-value" style="color: #10b981;">{{ $qoyod['latency'] }}</span>
                        </div>
                    @endif
                </div>

                {{-- Card Footer Link --}}
                <div class="tq-card-footer-link">
                    <span style="color: #6b7280;">فواتير قيود المكتملة: <strong>{{ $orderStats['syncedInvoices'] }}</strong></span>
                    <x-filament::link :href="route('filament.admin.resources.qoyod-syncs.index')" icon="heroicon-m-arrow-left">
                        استعراض سجل الفواتير
                    </x-filament::link>
                </div>
            </x-filament::section>

        </div>

        {{-- 3. Detailed Status Pipeline (حالات شحنات ترينديول بالتفصيل) --}}
        <x-filament::section
            icon="heroicon-o-funnel"
            heading="خط تدفق حالات الطلبات والشحنات (Order Pipeline)"
            description="تفصيل حالات الشحنات في النظام مع نسبة إنجاز الفوترة"
        >
            <x-slot name="afterHeader">
                <x-filament::badge color="success">
                    معدل نجاح الفوترة: {{ $orderStats['successRate'] }}%
                </x-filament::badge>
            </x-slot>

            <div class="tq-pipeline-grid">
                {{-- Created --}}
                <div class="tq-pipeline-card">
                    <span class="tq-pipeline-label">جديد (Created)</span>
                    <span class="tq-pipeline-count">{{ $orderStats['statuses']['Created'] ?? 0 }}</span>
                </div>

                {{-- Picking --}}
                <div class="tq-pipeline-card" style="border-color: #fde68a;">
                    <span class="tq-pipeline-label" style="color: #b45309;">تجهيز (Picking)</span>
                    <span class="tq-pipeline-count" style="color: #b45309;">{{ $orderStats['statuses']['Picking'] ?? 0 }}</span>
                </div>

                {{-- Invoiced --}}
                <div class="tq-pipeline-card" style="border-color: #bfdbfe;">
                    <span class="tq-pipeline-label" style="color: #1d4ed8;">مفوتر (Invoiced)</span>
                    <span class="tq-pipeline-count" style="color: #1d4ed8;">{{ $orderStats['statuses']['Invoiced'] ?? 0 }}</span>
                </div>

                {{-- Shipped --}}
                <div class="tq-pipeline-card" style="border-color: #99f6e4;">
                    <span class="tq-pipeline-label" style="color: #0f766e;">مشحون (Shipped)</span>
                    <span class="tq-pipeline-count" style="color: #0f766e;">{{ $orderStats['statuses']['Shipped'] ?? 0 }}</span>
                </div>

                {{-- Delivered --}}
                <div class="tq-pipeline-card" style="border-color: #bbf7d0;">
                    <span class="tq-pipeline-label" style="color: #15803d;">مستلم (Delivered)</span>
                    <span class="tq-pipeline-count" style="color: #15803d;">{{ $orderStats['statuses']['Delivered'] ?? 0 }}</span>
                </div>

                {{-- Cancelled --}}
                <div class="tq-pipeline-card">
                    <span class="tq-pipeline-label">ملغي (Cancelled)</span>
                    <span class="tq-pipeline-count" style="color: #6b7280;">{{ $orderStats['statuses']['Cancelled'] ?? 0 }}</span>
                </div>

                {{-- Returned --}}
                <div class="tq-pipeline-card" style="border-color: #fecdd3;">
                    <span class="tq-pipeline-label" style="color: #be123c;">مرتجع (Returned)</span>
                    <span class="tq-pipeline-count" style="color: #be123c;">{{ $orderStats['statuses']['Returned'] ?? 0 }}</span>
                </div>
            </div>

            {{-- Infrastructure quick strip --}}
            <div class="tq-system-grid">
                <div class="tq-system-card">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background: {{ !empty($system['db']['connected']) ? '#10b981' : '#ef4444' }};"></span>
                        <strong style="color: #111827;" class="dark:text-white">قاعدة بيانات MySQL</strong>
                        <span style="color: #6b7280; font-family: monospace;">({{ $system['db']['database'] ?? '-' }})</span>
                    </div>
                    <div>
                        <strong style="font-family: monospace; color: #10b981;">{{ $system['db']['latency'] ?? 'متصل' }}</strong>
                    </div>
                </div>

                <div class="tq-system-card">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background: {{ !empty($system['redis']['connected']) ? '#10b981' : '#ef4444' }};"></span>
                        <strong style="color: #111827;" class="dark:text-white">خدمة Redis وطابور المهام</strong>
                        <span style="color: #6b7280; font-family: monospace;">({{ $system['redis']['driver'] ?? '-' }})</span>
                    </div>
                    <div>
                        <strong style="font-family: monospace; color: #3b82f6;">{{ $system['redis']['queueSize'] ?? 0 }} مهام بالطابور</strong>
                    </div>
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-widgets::widget>
