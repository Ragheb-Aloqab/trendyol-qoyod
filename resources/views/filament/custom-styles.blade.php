<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap');

    :root {
        --font-family-arabic: 'Cairo', system-ui, -apple-system, sans-serif;
    }

    /* تطبيق خط Cairo الاحترافي مع دعم الأرقام الانجليزية الواضحة */
    html, body, .fi-body, .fi-page, .fi-ta, .fi-sidebar {
        font-family: var(--font-family-arabic) !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* أرقام الطلبات والمبالغ بتنسيق أرقام أنيق ومقروء */
    .fi-ta-text-item, .fi-ta-col, td, th {
        font-variant-numeric: tabular-nums;
    }

    /* تحسين تصميم بطاقة الجدول الرئيسية */
    .fi-ta-ctn {
        border-radius: 18px !important;
        box-shadow: 0 4px 25px -4px rgba(15, 23, 42, 0.05), 0 2px 10px -2px rgba(15, 23, 42, 0.02) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        overflow: hidden !important;
        background: #ffffff !important;
        transition: all 0.3s ease !important;
    }

    .dark .fi-ta-ctn {
        background: #0f172a !important;
        border-color: rgba(51, 65, 85, 0.5) !important;
        box-shadow: 0 4px 25px -4px rgba(0, 0, 0, 0.3) !important;
    }

    /* تحسين ترويسة الجدول (Header) */
    .fi-ta-header-cell {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
        padding-top: 14px !important;
        padding-bottom: 14px !important;
        font-weight: 700 !important;
        color: #334155 !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    .dark .fi-ta-header-cell {
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
        color: #cbd5e1 !important;
        border-bottom-color: #334155 !important;
    }

    /* تأثير تفاعلي فاخر على صفوف الجدول عند مرور الفأرة (Hover Effect) */
    .fi-ta-record {
        transition: all 0.2s ease-in-out !important;
        border-bottom: 1px solid rgba(241, 245, 249, 0.9) !important;
    }

    .fi-ta-record:hover {
        background: rgba(248, 250, 252, 0.95) !important;
        transform: scale(1.001);
    }

    .dark .fi-ta-record {
        border-bottom-color: rgba(30, 41, 59, 0.7) !important;
    }

    .dark .fi-ta-record:hover {
        background: rgba(30, 41, 59, 0.6) !important;
    }

    /* تحسين الشارات (Badges) */
    .fi-badge {
        padding: 4px 12px !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        border-radius: 9999px !important;
        letter-spacing: 0.01em !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    /* تحسين أزرار شريط الإجراءات العلوي (Header Action Buttons) */
    .fi-page-header .fi-btn {
        border-radius: 12px !important;
        font-weight: 700 !important;
        padding: 9px 18px !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.06), 0 1px 3px -1px rgba(0, 0, 0, 0.04) !important;
    }

    .fi-page-header .fi-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 18px -2px rgba(0, 0, 0, 0.12), 0 3px 6px -1px rgba(0, 0, 0, 0.06) !important;
    }

    .fi-page-header .fi-btn:active {
        transform: translateY(0px) !important;
    }

    /* زر التحديث الأخضر بتدرج عصري */
    .fi-page-header .fi-btn-color-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }

    .fi-page-header .fi-btn-color-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    }

    /* زر الفترة البرتقالي */
    .fi-page-header .fi-btn-color-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }

    /* زر السحب الكامل */
    .fi-page-header .fi-btn-color-primary {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }

    /* تحسين أزرار الأيقونات في كل صف (View & Sync Icons) */
    .fi-ta-actions .fi-icon-btn {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background: rgba(241, 245, 249, 0.6) !important;
    }

    .fi-ta-actions .fi-icon-btn:hover {
        transform: scale(1.15) !important;
        background: rgba(226, 232, 240, 0.9) !important;
        box-shadow: 0 4px 10px -1px rgba(0, 0, 0, 0.08) !important;
    }

    .dark .fi-ta-actions .fi-icon-btn {
        background: rgba(30, 41, 59, 0.6) !important;
    }

    .dark .fi-ta-actions .fi-icon-btn:hover {
        background: rgba(51, 65, 85, 0.8) !important;
    }

    /* تحسين حقل البحث (Search Input) */
    .fi-ta-search-field input {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
        background: #f8fafc !important;
        transition: all 0.2s ease !important;
    }

    .fi-ta-search-field input:focus {
        background: #ffffff !important;
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
    }

    .dark .fi-ta-search-field input {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    .dark .fi-ta-search-field input:focus {
        background: #0f172a !important;
    }

    /* تحسين شريط التنقل الجانبي (Sidebar) */
    .fi-sidebar-item-active .fi-sidebar-item-btn {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.06) 100%) !important;
        border-right: 3px solid #f59e0b !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
    }

    /* عنوان الصفحة مع تفاصيل جمالية */
    .fi-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        color: #0f172a !important;
    }

    .dark .fi-header-heading {
        color: #f8fafc !important;
    }

    .fi-header-subheading {
        color: #64748b !important;
        font-size: 0.95rem !important;
        margin-top: 4px !important;
    }
</style>
