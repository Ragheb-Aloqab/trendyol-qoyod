<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة مزامنة ترينديول وقيود | Trendyol ⟷ Qoyod</title>
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #f59e0b;
            --primary-hover: #d97706;
            --primary-gradient: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            --secondary-gradient: linear-gradient(135deg, #0d9488 0%, #059669 100%);
            --dark-bg: #090d16;
            --card-bg: rgba(17, 24, 39, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --font-arabic: 'Cairo', system-ui, -apple-system, sans-serif;
            --font-numbers: 'Plus Jakarta Sans', var(--font-arabic);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-arabic);
            background-color: var(--dark-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(245, 158, 11, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 85% 30%, rgba(13, 148, 136, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 50% 85%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            background-attachment: fixed;
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Navbar */
        header {
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(9, 13, 22, 0.8);
        }

        .nav-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        }

        .brand-icon svg {
            width: 24px;
            height: 24px;
            color: #ffffff;
        }

        .brand-text h1 {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            display: block;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.4rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #ffffff;
            box-shadow: 0 4px 18px rgba(245, 158, 11, 0.28);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
            filter: brightness(1.08);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            color: #f1f5f9;
            border-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: #ffffff;
        }

        .btn-lg {
            padding: 0.9rem 2.2rem;
            font-size: 1.05rem;
            border-radius: 14px;
        }

        /* Hero Section */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 4.5rem 0 3.5rem;
        }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(13, 148, 136, 0.12);
            border: 1px solid rgba(13, 148, 136, 0.35);
            color: #2dd4bf;
            padding: 0.4rem 1.1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1.6rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #2dd4bf;
            border-radius: 50%;
            position: relative;
        }

        .status-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background-color: rgba(45, 212, 191, 0.45);
            animation: pulse-ring 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.6); opacity: 0; }
        }

        .hero-title {
            font-size: clamp(2.1rem, 5vw, 3.4rem);
            font-weight: 900;
            line-height: 1.25;
            margin-bottom: 1.2rem;
            max-width: 860px;
            background: linear-gradient(180deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-title span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: var(--text-muted);
            max-width: 680px;
            line-height: 1.7;
            margin-bottom: 2.5rem;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.2rem;
            margin-bottom: 4rem;
        }

        /* Features Cards Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 1.5rem;
            width: 100%;
            margin-top: 1rem;
            text-align: right;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 1.8rem;
            backdrop-filter: blur(16px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 3px;
            background: transparent;
            transition: background 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.45);
        }

        .feature-card:hover::before {
            background: var(--primary-gradient);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
            font-size: 1.4rem;
        }

        .feature-icon.icon-orange {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }

        .feature-icon.icon-teal {
            background: rgba(13, 148, 136, 0.12);
            color: #2dd4bf;
            border: 1px solid rgba(13, 148, 136, 0.25);
        }

        .feature-icon.icon-blue {
            background: rgba(59, 130, 246, 0.12);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }

        .feature-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #f8fafc;
        }

        .feature-card p {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Footer */
        footer {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 1.8rem 0;
            background: rgba(9, 13, 22, 0.6);
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        @media (max-width: 640px) {
            .nav-actions .btn-outline {
                display: none;
            }
            .hero-actions {
                flex-direction: column;
                width: 100%;
            }
            .hero-actions .btn {
                width: 100%;
            }
            .footer-content {
                flex-direction: column;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <header>
        <div class="container">
            <div class="nav-content">
                <a href="{{ url('/') }}" class="brand">
                    <div class="brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1>مزامنة ترينديول وقيود</h1>
                        <span>Trendyol ⟷ Qoyod Platform</span>
                    </div>
                </a>

                <div class="nav-actions">
                    @auth
                        <a href="{{ url('/admin') }}" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span>لوحة التحكم</span>
                        </a>
                    @else
                        <a href="{{ url('/admin/login') }}" class="btn btn-outline">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            <span>تسجيل الدخول</span>
                        </a>

                        <a href="{{ url('/admin') }}" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span>لوحة التحكم</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Hero -->
    <main class="hero">
        <div class="container">
            <div class="badge-live">
                <span class="status-dot"></span>
                <span>نظام الربط السحابي متصل ونشط</span>
            </div>

            <h2 class="hero-title">
                منصة الربط المتكامل بين <span>ترينديول</span> ونظام <span>قيود</span>
            </h2>

            <p class="hero-subtitle">
                حل احترافي ذكي لمزامنة طلبات المتجر تلقائياً وإصدار فواتير ضريبية معتمدة عبر نظام قيود المحاسبي، مع معالجة دقيقة لضريبة القيمة المضافة والخصومات وتحميل فوري لملفات PDF.
            </p>

            <div class="hero-actions">
                <a href="{{ url('/admin') }}" class="btn btn-primary btn-lg">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>الدخول إلى لوحة التحكم</span>
                </a>

                <a href="{{ url('/admin/login') }}" class="btn btn-outline btn-lg">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    <span>تسجيل الدخول للمسؤولين</span>
                </a>
            </div>

            <!-- 3 Highlights Cards -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon icon-orange">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <h3>مزامنة طلبات Trendyol</h3>
                    <p>سحب الطلبات الجديدة وحالات الشحن والتوصيل دورياً، وتحديث بيانات العملاء والمنتجات بشكل لحظي ومنظم.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-teal">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <h3>فواتير قيود المعتمدة</h3>
                    <p>إنشاء الفواتير المحاسبية تلقائياً في قيود بحالة معتمدة (Approved)، وتوفير روابط مباشرة لعرض وطباعة ملف PDF الرسمي.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-blue">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h3>حسابات الضرائب والخصومات</h3>
                    <p>احتساب شامل لضريبة القيمة المضافة 15% ومعالجة خصومات ترينديول بدقة متوافقة تماماً مع معايير هيئة الزكاة والضريبة.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    جميع الحقوق محفوظة &copy; {{ date('Y') }} — منصة مزامنة ترينديول وقيود.
                </div>
                <div class="footer-links">
                    <a href="{{ url('/admin') }}">لوحة التحكم</a>
                    <span>•</span>
                    <a href="{{ url('/admin/login') }}">تسجيل الدخول</a>
                    <span>•</span>
                    <a href="{{ url('/admin/qoyod-invoices') }}">فواتير قيود</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
