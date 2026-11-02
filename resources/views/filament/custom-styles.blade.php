<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap');

    :root {
        --font-family-arabic: 'Cairo', system-ui, -apple-system, sans-serif;
    }

    /* 1. الخطوط والتايبوغرافي في كامل لوحة التحكم ولكافة الصفحات */
    html, body, .fi-body, .fi-page, .fi-ta, .fi-sidebar, .fi-topbar, .fi-modal, .fi-section, input, select, textarea, button {
        font-family: var(--font-family-arabic) !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* الأرقام والمبالغ المادية بتنسيق هندسي فائق الوضوح */
    .fi-ta-text-item, .fi-ta-col, td, th, .fi-stat-value, .tabular-nums {
        font-variant-numeric: tabular-nums;
        font-family: 'Plus Jakarta Sans', var(--font-family-arabic) !important;
    }

    /* 2. شريط التنقل العلوي (Topbar) بتأثير الزجاج المصقول (Glassmorphism) */
    .fi-topbar {
        backdrop-filter: blur(14px) !important;
        background: rgba(255, 255, 255, 0.88) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02) !important;
    }

    .dark .fi-topbar {
        background: rgba(15, 23, 42, 0.88) !important;
        border-bottom-color: rgba(51, 65, 85, 0.4) !important;
    }

    /* 3. شريط القائمة الجانبي (Sidebar) */
    .fi-sidebar {
        border-left: 1px solid rgba(226, 232, 240, 0.8) !important;
    }

    .dark .fi-sidebar {
        border-left-color: rgba(51, 65, 85, 0.4) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-btn {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.05) 100%) !important;
        border-right: 3px solid #f59e0b !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        color: #d97706 !important;
    }

    .dark .fi-sidebar-item-active .fi-sidebar-item-btn {
        color: #fbbf24 !important;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.08) 100%) !important;
    }

    /* 4. كروت الإحصائيات في لوحة التحكم (Stats Overview Widgets) */
    .fi-wi-stats-overview-stat {
        border-radius: 18px !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        box-shadow: 0 4px 20px -3px rgba(15, 23, 42, 0.04) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        overflow: hidden !important;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.09) !important;
        border-color: rgba(203, 213, 225, 1) !important;
    }

    .dark .fi-wi-stats-overview-stat {
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
        border-color: rgba(51, 65, 85, 0.5) !important;
    }

    /* 5. تصميم بطاقة الجداول الرئيسية (Tables) لكافة الموارد */
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

    /* ترويسة الجدول الفاخرة */
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

    /* تأثير التمرير للصفوف في كافة الجداول */
    .fi-ta-record {
        transition: all 0.2s ease-in-out !important;
        border-bottom: 1px solid rgba(241, 245, 249, 0.9) !important;
    }

    .fi-ta-record:hover {
        background: rgba(248, 250, 252, 0.95) !important;
    }

    .dark .fi-ta-record {
        border-bottom-color: rgba(30, 41, 59, 0.7) !important;
    }

    .dark .fi-ta-record:hover {
        background: rgba(30, 41, 59, 0.6) !important;
    }

    /* 6. بطاقات التفاصيل والأقسام (Sections & Infolists) في كافة الصفحات */
    .fi-section {
        border-radius: 18px !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04) !important;
        background: #ffffff !important;
        overflow: hidden !important;
        transition: all 0.25s ease !important;
    }

    .dark .fi-section {
        background: #0f172a !important;
        border-color: rgba(51, 65, 85, 0.5) !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.2) !important;
    }

    .fi-section-header {
        border-bottom: 1px solid rgba(241, 245, 249, 0.9) !important;
        padding-bottom: 12px !important;
    }

    .dark .fi-section-header {
        border-bottom-color: rgba(30, 41, 59, 0.7) !important;
    }

    .fi-section-header-heading {
        font-weight: 800 !important;
        font-size: 1.05rem !important;
        color: #0f172a !important;
    }

    .dark .fi-section-header-heading {
        color: #f8fafc !important;
    }

    /* 7. النوافذ المنبثقة التفاعلية (Modals & Dialogs) */
    .fi-modal-window {
        border-radius: 22px !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.25) !important;
        overflow: hidden !important;
    }

    .dark .fi-modal-window {
        border-color: rgba(51, 65, 85, 0.6) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6) !important;
    }

    .fi-modal-close-overlay {
        backdrop-filter: blur(8px) !important;
        background: rgba(15, 23, 42, 0.4) !important;
    }

    /* 8. حقول الإدخال والاختيارات (Form Inputs & Selects) */
    .fi-input-wrp {
        border-radius: 12px !important;
        border-color: #d1d5db !important;
        transition: all 0.2s ease !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.16) !important;
    }

    /* 9. تحسين الشارات (Pill Badges) */
    .fi-badge {
        padding: 5px 14px !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        border-radius: 9999px !important;
        letter-spacing: 0.01em !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
    }

    /* 10. أزرار الهيدر العلوية (Page Header Action Buttons) */
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

    /* أزرار العمليات المصغرة (Icon Buttons) داخل الجداول */
    .fi-ta-actions .fi-icon-btn {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background: rgba(241, 245, 249, 0.7) !important;
    }

    .fi-ta-actions .fi-icon-btn:hover {
        transform: scale(1.15) !important;
        background: rgba(226, 232, 240, 0.95) !important;
        box-shadow: 0 4px 10px -1px rgba(0, 0, 0, 0.08) !important;
    }

    .dark .fi-ta-actions .fi-icon-btn {
        background: rgba(30, 41, 59, 0.6) !important;
    }

    .dark .fi-ta-actions .fi-icon-btn:hover {
        background: rgba(51, 65, 85, 0.8) !important;
    }

    /* 11. أرقام الترقيم السفلي للصفحات (Pagination) */
    .fi-pagination-item-btn {
        border-radius: 10px !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
    }

    /* 12. عناوين الصفحات العلوية */
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
        font-size: 0.92rem !important;
        margin-top: 4px !important;
    }
</style>
