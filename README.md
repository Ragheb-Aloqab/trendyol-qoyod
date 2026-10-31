# نظام مزامنة طلبات ترينديول مع نظام قيود (Trendyol to Qoyod Sync)

لوحة تحكم ونظام متكامل مبني باستخدام **Laravel 12** و **Filament 5** لمزامنة طلبات متجر ترينديول (Trendyol) وإنشاء الفواتير والمنتجات تلقائياً داخل نظام قيود المحاسبي (Qoyod).

---

## 📋 المتطلبات الأساسية للخادم (Hostinger VPS)

- **نظام التشغيل:** Ubuntu 22.04 أو 24.04 LTS (موصى به)
- **PHP:** إصدار 8.2 أو 8.3 أو 8.4 مع الامتدادات التالية:
  - `php-fpm`, `php-mysql`, `php-mbstring`, `php-xml`, `php-bcmath`, `php-curl`, `php-zip`, `php-intl`, `php-redis`
- **قاعدة بيانات:** MySQL 8.0+ أو MariaDB
- **خادم الويب:** Nginx
- **خادم الذاكرة المؤقتة:** Redis (موصى به للطوابير والمهام الخلفية)
- **Composer 2+**
- **Node.js (LTS v20+) & NPM**
- **Supervisor** (لتشغيل معالجة الطوابير في الخلفية)

---

## 🚀 أولاً: خطوات الرفع على GitHub لأول مرة

افتح الطرفية (Terminal) في مجلد المشروع ونفّذ الأوامر التالية:

```bash
# 1. تهيئة مستودع Git
git init

# 2. إضافة جميع الملفات إلى التتبع (الملفات الحساسة و .env محمية ومستثناة تلقائياً)
git add .

# 3. عمل أول Commit
git commit -m "feat: initial commit for Trendyol-Qoyod integration"

# 4. تغيير اسم الفرع إلى main
git branch -M main

# 5. ربط المشروع بمستودعك على GitHub (استبدل الرابط برابط مستودعك الجديد)
git remote add origin https://github.com/USERNAME/REPO_NAME.git

# 6. رفع الكود إلى GitHub
git push -u origin main
```

> **ملاحظة أمنية هامة:** تم ضبط ملف `.gitignore` بحيث **لا يتم إطلاقاً** رفع ملف `.env` أو مفاتيح الـ API أو ملفات السجلات أو قواعد البيانات المحلية، لضمان سرية وأمان بياناتك.

---

## 🌐 ثانياً: خطوات التثبيت والاستضافة على Hostinger VPS

### 1. الدخول إلى السيرفر وتحديث الحزم

اتصل بالخادم عبر SSH:
```bash
ssh root@YOUR_SERVER_IP
```

قم بتحديث النظام وتثبيت المتطلبات:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git curl unzip supervisor redis-server
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-intl php8.2-redis
```

تثبيت Composer:
```bash
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

تثبيت Node.js & NPM:
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

### 2. سحب المشروع من GitHub

```bash
cd /var/www
sudo git clone https://github.com/USERNAME/REPO_NAME.git trendyol-qoyod
cd trendyol-qoyod
```

---

### 3. إعداد البيئة (.env) وتثبيت الاعتماديات

```bash
# نسخ قالب الإعدادات
cp .env.example .env

# تعديل ملف .env وكتابة بيانات قاعدة البيانات ومفاتيح ترينديول وقيود
nano .env
```

البيانات الأساسية التي يلزم تعديلها داخل `.env`:
```env
APP_NAME="مزامنة ترينديول وقيود"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trendyol_qoyod
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

QUEUE_CONNECTION=redis
CACHE_STORE=redis

# مفاتيح ترينديول
TRENDYOL_BASE_URL=https://apigw.trendyol.com
TRENDYOL_SUPPLIER_ID=1336083
TRENDYOL_API_KEY=YOUR_KEY
TRENDYOL_API_SECRET=YOUR_SECRET
TRENDYOL_APPROVED_STATUSES=Shipped,Delivered

# مفاتيح قيود
QOYOD_BASE_URL=https://api.qoyod.com/2.0
QOYOD_API_KEY=YOUR_KEY
QOYOD_DEFAULT_INVENTORY_ID=YOUR_ID
QOYOD_DEFAULT_CATEGORY_ID=YOUR_ID
QOYOD_DEFAULT_UNIT_TYPE_ID=YOUR_ID
QOYOD_DEFAULT_TAX_ID=1
QOYOD_DEFAULT_SALES_ACCOUNT_ID=YOUR_ID
```

ثم تثبيت حزم PHP وبناء ملفات الواجهة:
```bash
# تثبيت حزم PHP للإنتاج
composer install --no-dev --optimize-autoloader

# توليد مفتاح التطبيق
php artisan key:generate

# بناء ملفات الواجهة والـ Assets
npm install
npm run build

# تشغيل التهجير لإنشاء جداول قاعدة البيانات
php artisan migrate --force

# ترقية ونشر ملفات Filament
php artisan filament:upgrade

# ربط مجلد التخزين العام
php artisan storage:link

# إنشاء مستخدم الأدمن تلقائياً عبر الـ Seeder
# (البريد: hanayaline.sa@gmail.com | كلمة المرور: password)
php artisan db:seed --force

# أو إنشاء مستخدم مخصص آخر يدوياً إن أردت:
# php artisan make:filament-user
```

---

### 4. ضبط الصلاحيات (Permissions)

```bash
sudo chown -R www-data:www-data /var/www/trendyol-qoyod
sudo chmod -R 775 /var/www/trendyol-qoyod/storage /var/www/trendyol-qoyod/bootstrap/cache
```

---

### 5. إعداد خادم الويب Nginx وشهادة SSL

انسخ ملف الإعداد الجاهز:
```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/trendyol-qoyod
sudo nano /etc/nginx/sites-available/trendyol-qoyod
```
*(عدل `server_name` لاسم نطاقك أو عنوان IP الخاص بالسيرفر)*

تفعيل الموقع وإعادة تشغيل Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/trendyol-qoyod /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

تثبيت شهادة SSL مجانية (Let's Encrypt):
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

---

### 6. إعداد المزامنة التلقائية (Cron Job) ⏰

المشروع يحتوي على جدولة تلقائية لسحب ومزامنة أحدث طلبات ترينديول كل 15 دقيقة (`Schedule::command('trendyol:sync-orders --pages=2')->everyFifteenMinutes()`).

لتفعيل الجدولة على السيرفر، افتح جدول المهام:
```bash
crontab -e
```
وأضف السطر التالي في النهاية:
```cron
* * * * * cd /var/www/trendyol-qoyod && php artisan schedule:run >> /dev/null 2>&1
```

---

### 7. إعداد Supervisor لمعالجة الطوابير (Queue Worker)

لتشغيل الطوابير ومعالجة العمليات في الخلفية بشكل دائم:
```bash
sudo cp deploy/supervisord-worker.conf /etc/supervisor/conf.d/trendyol-qoyod-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start trendyol-qoyod-worker:*
```

---

## 🔄 تحديث المشروع لاحقاً (Deployment)

عندما تقوم بإجراء تعديلات جديدة على كود المشروع ورفعها على GitHub، يمكنك تحديث السيرفر بأمر واحد عبر تشغيل السكريبت المرفق:

```bash
cd /var/www/trendyol-qoyod
chmod +x deploy/deploy.sh
./deploy/deploy.sh
```

يقوم هذا السكريبت تلقائياً بـ:
1. تفعيل وضع الصيانة مؤقتاً.
2. سحب أحدث كود عبر `git pull`.
3. تثبيت أي حزم PHP و NPM جديدة وإعادة بناء الـ Assets.
4. تنفيذ أي Migrations جديدة.
5. إعادة بناء التخزين المؤقت للروابط والإعدادات (Cache).
6. إعادة تشغيل الطوابير وفتح الموقع للزوار فوراً.
