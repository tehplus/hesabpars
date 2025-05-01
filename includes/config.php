<?php
/**
 * تنظیمات اصلی برنامه
 * 
 * Current Date: 2025-05-01 16:10:31
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// اطلاعات برنامه
define('APP_NAME', 'حساب‌پرس');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'تِه پلاس');
define('APP_EMAIL', 'info@hesabpars.ir');
define('APP_DESCRIPTION', 'سیستم حسابداری آنلاین');
define('APP_KEYWORDS', 'حسابداری,مالی,فروشگاه,انبارداری');

// محیط برنامه (production, development, testing)
define('APP_ENV', 'development');
define('DEBUG_MODE', APP_ENV !== 'production');
define('MIN_PHP_VERSION', '8.0.0');

// مسیرهای اصلی
define('BASE_URL', 'http://localhost/hesabpars');
define('IS_HTTPS', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'hesabpars');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_persian_ci');
define('DB_PREFIX', 'hp_');

// تنظیمات امنیتی
define('HASH_ALGO', 'argon2id');
define('HASH_OPTIONS', [
    'memory_cost' => 65536,    // 64MB
    'time_cost' => 4,          // 4 iterations
    'threads' => 3             // 3 threads
]);
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 72);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); // دقیقه
define('API_KEY_LENGTH', 64);
define('JWT_SECRET', 'your-secret-key-here');
define('JWT_EXPIRE', 3600); // یک ساعت
define('CSRF_EXPIRE', 7200); // دو ساعت

// تنظیمات Session
define('SESSION_NAME', 'HESABPARS');
define('SESSION_LIFETIME', 7200); // دو ساعت
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', IS_HTTPS);
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// تنظیمات Cache
define('CACHE_ENABLE', true);
define('CACHE_TTL', 3600); // یک ساعت
define('CACHE_PREFIX', 'hp_cache_');
define('CACHE_DRIVER', 'file'); // file, redis, memcached

// تنظیمات زبان و تاریخ
define('DEFAULT_LANGUAGE', 'fa');
define('DEFAULT_TIMEZONE', 'Asia/Tehran');
define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y/m/d H:i:s');
define('LOCALES', [
    'fa' => 'fa_IR.UTF-8',
    'en' => 'en_US.UTF-8'
]);

// تنظیمات ایمیل
define('MAIL_DRIVER', 'smtp');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@hesabpars.ir');
define('MAIL_FROM_NAME', APP_NAME);

// تنظیمات آپلود
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_IMAGES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_ALLOWED_DOCS', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('UPLOAD_ALLOWED_TYPES', array_merge(
    UPLOAD_ALLOWED_IMAGES,
    UPLOAD_ALLOWED_DOCS
));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// تنظیمات لاگ
define('LOG_ENABLE', true);
define('LOG_PATH', BASE_PATH . '/logs');
define('LOG_MAX_FILES', 30);
define('LOG_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');
define('LOG_REQUESTS', true);

// تنظیمات پیام‌های خطا
define('ERROR_MESSAGES', [
    'db_connection' => 'خطا در اتصال به پایگاه داده',
    'db_query' => 'خطا در اجرای کوئری',
    'invalid_input' => 'داده‌های ورودی نامعتبر است',
    'access_denied' => 'دسترسی غیرمجاز',
    'not_found' => 'صفحه مورد نظر یافت نشد',
    'server_error' => 'خطای سرور',
    'invalid_token' => 'توکن نامعتبر است',
    'expired_token' => 'توکن منقضی شده است',
    'invalid_credentials' => 'نام کاربری یا رمز عبور اشتباه است',
    'account_locked' => 'حساب کاربری قفل شده است',
    'file_upload' => 'خطا در آپلود فایل',
    'file_size' => 'حجم فایل بیش از حد مجاز است',
    'file_type' => 'نوع فایل مجاز نیست'
]);

// تنظیمات پیام‌های موفقیت
define('SUCCESS_MESSAGES', [
    'login' => 'ورود با موفقیت انجام شد',
    'logout' => 'خروج با موفقیت انجام شد',
    'register' => 'ثبت نام با موفقیت انجام شد',
    'update' => 'بروزرسانی با موفقیت انجام شد',
    'delete' => 'حذف با موفقیت انجام شد',
    'create' => 'ایجاد با موفقیت انجام شد',
    'upload' => 'آپلود با موفقیت انجام شد'
]);

// تنظیمات واحدهای پولی
define('DEFAULT_CURRENCY', 'IRR');
define('DEFAULT_CURRENCY_SYMBOL', 'ریال');
define('CURRENCIES', [
    'IRR' => [
        'name' => 'ریال',
        'symbol' => 'ریال',
        'decimals' => 0
    ],
    'IRT' => [
        'name' => 'تومان',
        'symbol' => 'تومان',
        'decimals' => 0
    ],
    'USD' => [
        'name' => 'دلار',
        'symbol' => '$',
        'decimals' => 2
    ],
    'EUR' => [
        'name' => 'یورو',
        'symbol' => '€',
        'decimals' => 2
    ]
]);

// تنظیمات مربوط به محصولات
define('PRODUCT_IMAGE_SIZES', [
    'thumb' => ['width' => 100, 'height' => 100],
    'small' => ['width' => 300, 'height' => 300],
    'medium' => ['width' => 600, 'height' => 600],
    'large' => ['width' => 1200, 'height' => 1200]
]);

define('PRODUCT_STATUSES', [
    'active' => 'فعال',
    'inactive' => 'غیرفعال',
    'out_of_stock' => 'ناموجود',
    'discontinued' => 'متوقف شده'
]);

// تنظیمات مربوط به فاکتورها
define('INVOICE_TYPES', [
    'sale' => 'فروش',
    'purchase' => 'خرید',
    'return_sale' => 'برگشت از فروش',
    'return_purchase' => 'برگشت از خرید'
]);

define('INVOICE_STATUSES', [
    'draft' => 'پیش‌نویس',
    'pending' => 'در انتظار تایید',
    'approved' => 'تایید شده',
    'rejected' => 'رد شده',
    'paid' => 'پرداخت شده',
    'partial' => 'پرداخت جزئی',
    'canceled' => 'لغو شده'
]);

// تنظیمات مربوط به پرداخت‌ها
define('PAYMENT_METHODS', [
    'cash' => 'نقدی',
    'card' => 'کارت به کارت',
    'check' => 'چک',
    'deposit' => 'واریز به حساب',
    'online' => 'پرداخت آنلاین'
]);

define('PAYMENT_STATUSES', [
    'pending' => 'در انتظار پرداخت',
    'completed' => 'پرداخت شده',
    'failed' => 'ناموفق',
    'refunded' => 'برگشت داده شده',
    'canceled' => 'لغو شده'
]);

// تنظیمات نقش‌های کاربری
define('USER_ROLES', [
    'admin' => 'مدیر',
    'manager' => 'مدیر فروش',
    'accountant' => 'حسابدار',
    'warehouse' => 'انباردار',
    'seller' => 'فروشنده',
    'user' => 'کاربر عادی'
]);

// تنظیمات وضعیت‌های کاربر
define('USER_STATUSES', [
    'active' => 'فعال',
    'inactive' => 'غیرفعال',
    'suspended' => 'معلق',
    'banned' => 'مسدود'
]);

// تنظیمات API
define('API_VERSION', 'v1');
define('API_PREFIX', '/api/' . API_VERSION);
define('API_DEBUG', DEBUG_MODE);
define('API_RATE_LIMIT', 60); // تعداد درخواست در دقیقه
define('API_TIMEOUT', 30); // ثانیه

// تنظیمات مربوط به گزارش‌ها
define('REPORT_TYPES', [
    'daily' => 'روزانه',
    'weekly' => 'هفتگی',
    'monthly' => 'ماهانه',
    'yearly' => 'سالانه',
    'custom' => 'دلخواه'
]);

// تنظیمات مربوط به اعلان‌ها
define('NOTIFICATION_TYPES', [
    'success' => 'موفقیت',
    'info' => 'اطلاعات',
    'warning' => 'هشدار',
    'error' => 'خطا'
]);

// تنظیمات مربوط به پیام‌ها
define('MESSAGE_TYPES', [
    'private' => 'خصوصی',
    'system' => 'سیستمی',
    'broadcast' => 'عمومی'
]);

// تنظیمات صفحه‌بندی
define('PAGINATION_PER_PAGE', 20);
define('PAGINATION_ADJACENTS', 2);

// تنظیمات مربوط به جستجو
define('SEARCH_MIN_LENGTH', 3);
define('SEARCH_MAX_LENGTH', 50);
define('SEARCH_RESULTS_LIMIT', 100);

// تنظیمات مربوط به بک‌آپ
define('BACKUP_PATH', BASE_PATH . '/backups');
define('BACKUP_FILES', true);
define('BACKUP_DB', true);
define('BACKUP_COMPRESS', true);
define('BACKUP_ENCRYPT', true);
define('BACKUP_MAX_FILES', 10);
define('BACKUP_MAX_SIZE', 500 * 1024 * 1024); // 500MB

// ثابت‌های عمومی
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);
define('SORT_ASC', 'ASC');
define('SORT_DESC', 'DESC');
define('DEFAULT_SORT', 'id');
define('DEFAULT_SORT_DIRECTION', SORT_DESC);

// تنظیمات فایل‌های تمپلیت
define('TEMPLATE_PATH', BASE_PATH . '/templates');
define('TEMPLATE_CACHE', BASE_PATH . '/cache/templates');
define('TEMPLATE_EXTENSION', '.php');

// تنظیمات مربوط به سئو
define('SEO_TITLE_SEPARATOR', ' | ');
define('SEO_TITLE_APPEND', APP_NAME);
define('SEO_DESCRIPTION_LENGTH', 160);
define('SEO_KEYWORDS_LIMIT', 10);

// تنظیمات تصاویر پیش‌فرض
define('DEFAULT_AVATAR', 'assets/img/default-avatar.png');
define('DEFAULT_PRODUCT_IMAGE', 'assets/img/default-product.png');
define('DEFAULT_CATEGORY_IMAGE', 'assets/img/default-category.png');
define('DEFAULT_BRAND_IMAGE', 'assets/img/default-brand.png');

// تنظیمات مربوط به فایل‌های مجاز
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('ALLOWED_SPREADSHEET_TYPES', ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

// تنظیمات مربوط به محدودیت‌های فایل
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_IMAGE_WIDTH', 2048);
define('MAX_IMAGE_HEIGHT', 2048);
define('IMAGE_QUALITY', 80);

// تنظیمات مربوط به امنیت
define('SECURE_AUTH_KEY', 'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_KEY', 'put your unique phrase here');
define('LOGGED_IN_SALT', 'put your unique phrase here');
define('NONCE_KEY', 'put your unique phrase here');
define('NONCE_SALT', 'put your unique phrase here');