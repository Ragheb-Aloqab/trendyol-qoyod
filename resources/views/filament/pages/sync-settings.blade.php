<x-filament-panels::page>
    <style>
        /* تقييد حجم كافة أيقونات SVG داخل هذه الصفحة لمنع تمددها */
        .tq-settings-box svg {
            width: 24px !important;
            height: 24px !important;
            max-width: 24px !important;
            max-height: 24px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            display: inline-block !important;
            flex-shrink: 0 !important;
        }

        .tq-settings-hero-icon svg {
            width: 28px !important;
            height: 28px !important;
            max-width: 28px !important;
            max-height: 28px !important;
        }

        .tq-settings-sm-icon svg {
            width: 18px !important;
            height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
        }

        .tq-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .dark .tq-card {
            background-color: #111827;
            border-color: #1f2937;
        }

        .tq-toggle-switch {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            width: 3.5rem;
            height: 2rem;
            flex-shrink: 0;
        }
        .tq-toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }
        .tq-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #d1d5db;
            transition: 0.3s ease;
            border-radius: 2rem;
        }
        .dark .tq-toggle-slider {
            background-color: #374151;
        }
        .tq-toggle-slider:before {
            position: absolute;
            content: "";
            height: 1.5rem;
            width: 1.5rem;
            left: 0.25rem;
            bottom: 0.25rem;
            background-color: white;
            transition: 0.3s ease;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .tq-toggle-switch input:checked + .tq-toggle-slider {
            background-color: #10b981;
        }
        .tq-toggle-switch input:checked + .tq-toggle-slider:before {
            transform: translateX(1.5rem);
        }

        .tq-sm-toggle {
            width: 2.75rem;
            height: 1.5rem;
        }
        .tq-sm-toggle .tq-toggle-slider:before {
            height: 1.125rem;
            width: 1.125rem;
            left: 0.1875rem;
            bottom: 0.1875rem;
        }
        .tq-sm-toggle input:checked + .tq-toggle-slider:before {
            transform: translateX(1.25rem);
        }

        .tq-checkbox-card {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
        }
        .dark .tq-checkbox-card {
            background: #111827;
            border-color: #1f2937;
        }
        .tq-checkbox-card.is-active {
            border-color: #f59e0b;
            background-color: rgba(245, 158, 11, 0.05);
            box-shadow: 0 0 0 1px #f59e0b;
        }
    </style>

    <div class="tq-settings-box space-y-6" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
        <form wire:submit.prevent="save" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">

            {{-- 1. كرت حالة المزامنة التلقائية الرئيسي --}}
            <div class="tq-card" style="{{ $auto_sync_enabled ? 'border-color: #10b981; background: linear-gradient(to left, rgba(16, 185, 129, 0.06), rgba(20, 184, 166, 0.04));' : 'border-color: #f59e0b; background: linear-gradient(to left, rgba(245, 158, 11, 0.06), rgba(249, 115, 22, 0.04));' }}">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="tq-settings-hero-icon" style="width: 48px; height: 48px; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; {{ $auto_sync_enabled ? 'background-color: #10b981; color: white;' : 'background-color: #f59e0b; color: white;' }}">
                            @if($auto_sync_enabled)
                                <x-heroicon-o-check-badge style="width: 28px; height: 28px; max-width: 28px; max-height: 28px;" />
                            @else
                                <x-heroicon-o-pause-circle style="width: 28px; height: 28px; max-width: 28px; max-height: 28px;" />
                            @endif
                        </div>
                        <div>
                            <h2 style="font-size: 1.125rem; font-weight: 800; margin: 0;">
                                المزامنة التلقائية: 
                                <span style="{{ $auto_sync_enabled ? 'color: #059669;' : 'color: #d97706;' }}">
                                    {{ $auto_sync_enabled ? 'مفعلة وتعمل بالخلفية' : 'معطلة مؤقتاً (يدوي فقط)' }}
                                </span>
                            </h2>
                            <p style="font-size: 0.8125rem; color: #6b7280; margin: 0.25rem 0 0 0;">
                                {{ $auto_sync_enabled ? 'يقوم النظام تلقائياً بجدولة فحص ترينديول وإرسال الفواتير لقيود كل 10-15 دقيقة.' : 'المهام التلقائية معطلة تماماً. لن يتم سحب أو إرسال أي طلب إلا بضغطة زر يدوية منك.' }}
                            </p>
                        </div>
                    </div>

                    {{-- سويتش التشغيل والإيقاف --}}
                    <label class="tq-toggle-switch">
                        <input type="checkbox" wire:model.live="auto_sync_enabled">
                        <span class="tq-toggle-slider"></span>
                    </label>
                </div>
            </div>

            {{-- 2. كرت تاريخ بدء المزامنة (منع الطلبات القديمة) --}}
            <div class="tq-card">
                <div style="display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; margin-bottom: 1.25rem;">
                    <div style="padding: 0.5rem; background-color: rgba(245, 158, 11, 0.1); border-radius: 0.5rem; color: #d97706; display: flex;">
                        <x-heroicon-o-calendar-days style="width: 24px; height: 24px; max-width: 24px; max-height: 24px;" />
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 800; margin: 0; color: inherit;">تاريخ بدء المزامنة (منع الطلبات القديمة)</h3>
                        <p style="font-size: 0.75rem; color: #6b7280; margin: 0.2rem 0 0 0;">أي طلب تم إنشاؤه في ترينديول قبل هذا التاريخ سيتم استبعاده وتجاهله تماماً ولن يُرسل إلى قيود.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem;">
                            تاريخ بداية سحب وفوترة الطلبات:
                        </label>
                        <input 
                            type="date" 
                            wire:model="sync_start_date" 
                            style="width: 100%; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 1px solid #d1d5db; font-size: 0.875rem; font-family: ui-monospace, monospace; background: transparent; color: inherit;"
                        >
                        <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                            القيمة الحالية المعتمدة: <strong style="color: #d97706;">2026-10-01</strong> (أول شهر أكتوبر). لمنع الطلبات الأقدم من شهر 10.
                        </p>
                    </div>

                    <div style="padding: 1rem 1.25rem; border-radius: 0.75rem; background-color: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.8125rem; color: #b45309;">
                        <div style="font-weight: 800; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="tq-settings-sm-icon" style="display: flex;">
                                <x-heroicon-m-shield-check style="width: 18px; height: 18px; max-width: 18px; max-height: 18px;" />
                            </span>
                            نظام الحماية من الطلبات القديمة:
                        </div>
                        <p style="margin: 0; line-height: 1.45;">
                            أي طلب تاريخه قبل <strong>2026-10-01</strong> لن تتم مزامنته تلقائياً، وإن حاول أي شخص مزامنته يدوياً سيرفض النظام ذلك لحماية حسابك المحاسبي.
                        </p>
                    </div>
                </div>
            </div>

            {{-- 3. كرت الحالات المعتمدة للإرسال لقيود --}}
            <div class="tq-card">
                <div style="display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; margin-bottom: 1.25rem;">
                    <div style="padding: 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-radius: 0.5rem; color: #2563eb; display: flex;">
                        <x-heroicon-o-truck style="width: 24px; height: 24px; max-width: 24px; max-height: 24px;" />
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 800; margin: 0; color: inherit;">حالات الطلب المعتمدة للإرسال إلى قيود</h3>
                        <p style="font-size: 0.75rem; color: #6b7280; margin: 0.2rem 0 0 0;">حدد حالات طلبات ترينديول المؤهلة لإصدار فواتير المبيعات في قيود.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                    {{-- Delivered --}}
                    <label class="tq-checkbox-card {{ in_array('Delivered', $approved_statuses) ? 'is-active' : '' }}">
                        <input type="checkbox" value="Delivered" wire:model="approved_statuses" style="width: 1.125rem; height: 1.125rem; accent-color: #f59e0b; cursor: pointer;">
                        <div style="margin-right: 0.75rem;">
                            <span style="display: block; font-size: 0.875rem; font-weight: 800; color: inherit;">تم التوصيل (Delivered)</span>
                            <span style="display: block; font-size: 0.7rem; color: #059669; font-weight: 700; margin-top: 0.1rem;">الموصى به افتراضياً</span>
                        </div>
                    </label>

                    {{-- Shipped --}}
                    <label class="tq-checkbox-card {{ in_array('Shipped', $approved_statuses) ? 'is-active' : '' }}">
                        <input type="checkbox" value="Shipped" wire:model="approved_statuses" style="width: 1.125rem; height: 1.125rem; accent-color: #f59e0b; cursor: pointer;">
                        <div style="margin-right: 0.75rem;">
                            <span style="display: block; font-size: 0.875rem; font-weight: 700; color: inherit;">تم الشحن (Shipped)</span>
                            <span style="display: block; font-size: 0.7rem; color: #6b7280; margin-top: 0.1rem;">عند خروج الشحنة</span>
                        </div>
                    </label>

                    {{-- Invoiced --}}
                    <label class="tq-checkbox-card {{ in_array('Invoiced', $approved_statuses) ? 'is-active' : '' }}">
                        <input type="checkbox" value="Invoiced" wire:model="approved_statuses" style="width: 1.125rem; height: 1.125rem; accent-color: #f59e0b; cursor: pointer;">
                        <div style="margin-right: 0.75rem;">
                            <span style="display: block; font-size: 0.875rem; font-weight: 700; color: inherit;">تمت الفوترة (Invoiced)</span>
                            <span style="display: block; font-size: 0.7rem; color: #6b7280; margin-top: 0.1rem;">في ترينديول</span>
                        </div>
                    </label>

                    {{-- Picking --}}
                    <label class="tq-checkbox-card {{ in_array('Picking', $approved_statuses) ? 'is-active' : '' }}">
                        <input type="checkbox" value="Picking" wire:model="approved_statuses" style="width: 1.125rem; height: 1.125rem; accent-color: #f59e0b; cursor: pointer;">
                        <div style="margin-right: 0.75rem;">
                            <span style="display: block; font-size: 0.875rem; font-weight: 700; color: inherit;">جاري التجهيز (Picking)</span>
                            <span style="display: block; font-size: 0.7rem; color: #6b7280; margin-top: 0.1rem;">قبل الشحن</span>
                        </div>
                    </label>
                </div>

                {{-- خيار الإرسال التلقائي الفوري لقيود --}}
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h4 style="font-size: 0.875rem; font-weight: 800; margin: 0; color: inherit;">الإرسال التلقائي الفوري إلى قيود</h4>
                        <p style="font-size: 0.75rem; color: #6b7280; margin: 0.2rem 0 0 0;">إرسال الفاتورة تلقائياً لقيود عبر المهام المجدولة بمجرد وصول الطلب لحالة التوصيل المعتمدة.</p>
                    </div>

                    <label class="tq-toggle-switch tq-sm-toggle">
                        <input type="checkbox" wire:model="auto_send_to_qoyod">
                        <span class="tq-toggle-slider"></span>
                    </label>
                </div>
            </div>

            {{-- 4. زر الحفظ الرئيسي --}}
            <div style="display: flex; justify-content: flex-end; padding-top: 0.5rem;">
                <button 
                    type="submit" 
                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 2rem; background-color: #d97706; color: white; font-weight: 800; font-size: 0.9375rem; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.3); transition: all 0.2s ease;"
                    onmouseover="this.style.backgroundColor='#b45309'" 
                    onmouseout="this.style.backgroundColor='#d97706'"
                >
                    <span class="tq-settings-sm-icon" style="display: flex;">
                        <x-heroicon-m-check style="width: 20px; height: 20px; max-width: 20px; max-height: 20px;" />
                    </span>
                    حفظ وتطبيق الإعدادات الآن
                </button>
            </div>

        </form>
    </div>
</x-filament-panels::page>
