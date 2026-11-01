<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        
        {{-- كرت حالة المزامنة التلقائية الرئيسي --}}
        <div class="p-6 rounded-2xl border transition-all duration-300 {{ $auto_sync_enabled ? 'bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/20 dark:to-teal-950/20 border-emerald-200 dark:border-emerald-800/50' : 'bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border-amber-200 dark:border-amber-800/50' }}">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $auto_sync_enabled ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' }}">
                        @if($auto_sync_enabled)
                            <x-heroicon-o-check-badge class="w-7 h-7" />
                        @else
                            <x-heroicon-o-pause-circle class="w-7 h-7" />
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            حالة المزامنة التلقائية: 
                            <span class="{{ $auto_sync_enabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $auto_sync_enabled ? 'مفعلة وتعمل تلقائياً' : 'متوقفة مؤقتاً (يدوي فقط)' }}
                            </span>
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $auto_sync_enabled ? 'المهام المجدولة تعمل كل 10-15 دقيقة لسحب الطلبات وإرسال الفواتير.' : 'المهام التلقائية معطلة حالياً. يمكنك المزامنة يدوياً من صفحات الطلبات.' }}
                        </p>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="auto_sync_enabled" class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                </label>
            </div>
        </div>

        {{-- قسم تاريخ بدء المزامنة لمنع الطلبات القديمة --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="p-2 bg-primary-50 dark:bg-primary-950/30 rounded-lg text-primary-600 dark:text-primary-400">
                    <x-heroicon-o-calendar-days class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">تاريخ بدء المزامنة (منع الطلبات القديمة)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">أي طلب مسجل في ترينديول قبل هذا التاريخ سيتم استبعاده وتجاهله تماماً ولن يُرسل إلى قيود.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        تاريخ بداية سحب وفوترة الطلبات:
                    </label>
                    <input 
                        type="date" 
                        wire:model="sync_start_date" 
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm font-mono"
                    >
                    <p class="text-xs text-gray-500 mt-2">
                        القيمة المعتمدة: <strong>2026-10-01</strong> (أول شهر أكتوبر). لمنع الطلبات الأقدم من شهر 10.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <x-heroicon-m-shield-check class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                        حماية منع الطلبات القديمة:
                    </div>
                    <p>
                        عند تعيين هذا التاريخ، يرفض النظام آلياً إصدار أي فاتورة في قيود للطلبات التي أُنشئت قبل هذا اليوم، حتى لو قام المستخدم بمحاولة إرسالها يدوياً.
                    </p>
                </div>
            </div>
        </div>

        {{-- قسم الحالات المعتمدة للإرسال لقيود --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="p-2 bg-blue-50 dark:bg-blue-950/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <x-heroicon-o-truck class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">حالات الطلب المعتمدة للإرسال إلى قيود</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">حدد حالات الطلبات في ترينديول المؤهلة للمزامنة التلقائية وإصدار الفواتير في قيود.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Delivered --}}
                <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ in_array('Delivered', $approved_statuses) ? 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-500 shadow-sm' : 'border-gray-200 dark:border-gray-800' }}">
                    <input type="checkbox" value="Delivered" wire:model="approved_statuses" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                    <div class="ms-3">
                        <span class="block text-sm font-bold text-gray-900 dark:text-white">تم التوصيل (Delivered)</span>
                        <span class="block text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">الموصى به افتراضياً</span>
                    </div>
                </label>

                {{-- Shipped --}}
                <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ in_array('Shipped', $approved_statuses) ? 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-500 shadow-sm' : 'border-gray-200 dark:border-gray-800' }}">
                    <input type="checkbox" value="Shipped" wire:model="approved_statuses" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                    <div class="ms-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">تم الشحن (Shipped)</span>
                        <span class="block text-xs text-gray-500 mt-0.5">عند خروج الشحنة</span>
                    </div>
                </label>

                {{-- Invoiced --}}
                <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ in_array('Invoiced', $approved_statuses) ? 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-500 shadow-sm' : 'border-gray-200 dark:border-gray-800' }}">
                    <input type="checkbox" value="Invoiced" wire:model="approved_statuses" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                    <div class="ms-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">تمت الفوترة (Invoiced)</span>
                        <span class="block text-xs text-gray-500 mt-0.5">في ترينديول</span>
                    </div>
                </label>

                {{-- Picking --}}
                <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ in_array('Picking', $approved_statuses) ? 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-500 shadow-sm' : 'border-gray-200 dark:border-gray-800' }}">
                    <input type="checkbox" value="Picking" wire:model="approved_statuses" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                    <div class="ms-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">جاري التجهيز (Picking)</span>
                        <span class="block text-xs text-gray-500 mt-0.5">قبل الشحن</span>
                    </div>
                </label>
            </div>

            {{-- خيار الإرسال التلقائي لقيود --}}
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">الإرسال التلقائي الفوري إلى قيود</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">إرسال الفاتورة تلقائياً لقيود عبر المهام المجدولة بمجرد وصول الطلب لحالة التوصيل.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="auto_send_to_qoyod" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>
        </div>

        {{-- أزرار الحفظ والإجراءات --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <button 
                type="submit" 
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/25 transition-all text-sm cursor-pointer"
            >
                <x-heroicon-m-check class="w-5 h-5" />
                حفظ وتطبيق الإعدادات الآن
            </button>
        </div>
    </form>
</x-filament-panels::page>
