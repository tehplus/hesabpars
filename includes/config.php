<?php
/**
 * تنظیمات اصلی برنامه
 * 
 * این فایل شامل تمام تنظیمات اصلی برنامه است:
 * - تنظیمات برنامه
 * - تنظیمات دیتابیس
 * - تنظیمات امنیتی
 * - تنظیمات کش و سشن
 * - تنظیمات آپلود و لاگ
 * - پیکربندی‌های عمومی
 * 
 * Current Date: 2025-05-01 16:32:06
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0 
 */

// جلوگیری از دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

/**
 * تنظیمات اصلی برنامه
 */
define('APP_NAME', 'حساب‌پرس');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'تِه پلاس');
define('APP_EMAIL', 'info@hesabpars.ir');
define('APP_DESCRIPTION', 'سیستم حسابداری و مدیریت مالی آنلاین');
define('APP_KEYWORDS', 'حسابداری,مالی,فروشگاه,انبارداری,حساب‌پرس');
define('APP_SUPPORT_PHONE', '09123456789');

/**
 * تنظیمات محیط برنامه
 */
define('APP_ENV', 'development'); // development, production, testing
define('DEBUG_MODE', APP_ENV === 'development');
define('MIN_PHP_VERSION', '8.0.0');
define('DEFAULT_TIMEZONE', 'Asia/Tehran');
define('DEFAULT_LOCALE', 'fa_IR.UTF-8');
define('DEFAULT_CHARSET', 'UTF-8');

/**
 * تنظیمات مسیرها
 */
define('BASE_URL', 'http://localhost/hesabpars');
define('SITE_URL', rtrim(BASE_URL, '/'));
define('ADMIN_URL', SITE_URL . '/admin');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');

define('IS_HTTPS', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
define('CURRENT_URL', (IS_HTTPS ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

/**
 * تنظیمات دیتابیس
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'hesabpars');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_persian_ci');
define('DB_PREFIX', 'hp_');
define('DB_PORT', 3306);

/**
 * تنظیمات امنیتی
 */
define('HASH_ALGO', PASSWORD_ARGON2ID);
define('HASH_OPTIONS', [
    'memory_cost' => 65536,    // 64MB
    'time_cost'   => 4,        // 4 iterations
    'threads'     => 3         // 3 threads
]);

define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 72);
define('PASSWORD_EXPIRE_DAYS', 90);
define('PASSWORD_HISTORY_LIMIT', 5);

define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); // minutes
define('LOGIN_REMEMBER_TIME', 30); // days

define('SESSION_NAME', 'HESABPARS');
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_EXPIRE_ON_CLOSE', true);
define('SESSION_ENCRYPT', true);
define('SESSION_SSL', IS_HTTPS);
define('SESSION_HTTP_ONLY', true);
define('SESSION_SECURE', IS_HTTPS);
define('SESSION_SAME_SITE', 'Lax');
define('SESSION_REGENERATE_TIME', 300); // 5 minutes

define('TOKEN_LENGTH', 64);
define('TOKEN_EXPIRE', 3600); // 1 hour
define('API_KEY_LENGTH', 64);
define('API_KEY_PREFIX', 'hp_');
define('JWT_SECRET', 'your-super-secret-key-here');
define('JWT_ALGO', 'HS256');
define('JWT_EXPIRE', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LENGTH', 64);
define('CSRF_EXPIRE', 7200); // 2 hours

/**
 * تنظیمات کش
 */
define('CACHE_ENABLE', true);
define('CACHE_DRIVER', 'file'); // file, redis, memcached
define('CACHE_PREFIX', 'hp_cache_');
define('CACHE_TTL', 3600); // 1 hour
define('CACHE_COMPRESS', true);
define('CACHE_PATH', BASE_PATH . '/cache');

// Redis settings
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_AUTH', null);
define('REDIS_DB', 0);

// Memcached settings
define('MEMCACHED_HOST', '127.0.0.1');
define('MEMCACHED_PORT', 11211);

/**
 * تنظیمات آپلود
 */
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_CHUNK_SIZE', 1024 * 1024); // 1MB
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_TEMP_PATH', UPLOAD_PATH . '/temp');

define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif'
]);

define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

define('ALLOWED_SPREADSHEET_TYPES', [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]);

define('IMAGE_MAX_WIDTH', 2048);
define('IMAGE_MAX_HEIGHT', 2048);
define('IMAGE_QUALITY', 80);
define('IMAGE_SIZES', [
    'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
    'small'     => ['width' => 300, 'height' => 300, 'crop' => false],
    'medium'    => ['width' => 600, 'height' => 600, 'crop' => false],
    'large'     => ['width' => 1200, 'height' => 1200, 'crop' => false]
]);

/**
 * تنظیمات لاگ
 */
define('LOG_ENABLE', true);
define('LOG_PATH', BASE_PATH . '/logs');
define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');
define('LOG_FORMAT', "[%datetime%] %level%: %message% %context%\n");
define('LOG_MAX_FILES', 30);
define('LOG_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('LOG_COMPRESS', true);
define('LOG_PERMISSIONS', 0644);
define('LOG_TYPES', [
    'error'     => LOG_PATH . '/errors',
    'access'    => LOG_PATH . '/access',
    'security'  => LOG_PATH . '/security',
    'debug'     => LOG_PATH . '/debug',
    'sql'       => LOG_PATH . '/sql'
]);

/**
 * تنظیمات ایمیل
 */
define('MAIL_DRIVER', 'smtp'); // smtp, sendmail, mail
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-specific-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@hesabpars.ir');
define('MAIL_FROM_NAME', APP_NAME);
define('MAIL_REPLY_TO', 'support@hesabpars.ir');
define('MAIL_DEBUG', DEBUG_MODE);

/**
 * تنظیمات مربوط به زبان و تاریخ
 */
define('DEFAULT_LANGUAGE', 'fa');
define('AVAILABLE_LANGUAGES', [
    'fa' => 'فارسی',
    'en' => 'English'
]);

define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y/m/d H:i:s');
define('TIMEZONE_FORMAT', 'P');
define('FIRST_DAY_OF_WEEK', 6); // شنبه

/**
 * تنظیمات پیام‌ها
 */
define('MESSAGE_TYPES', [
    'success' => 'success',
    'error'   => 'error',
    'info'    => 'info',
    'warning' => 'warning'
]);

/**
 * تنظیمات صفحه‌بندی
 */
define('PAGINATION_PER_PAGE', 20);
define('PAGINATION_NUM_LINKS', 5);
define('MAX_PAGINATION_LINKS', 10);

/**
 * تنظیمات مربوط به محصولات
 */
define('PRODUCT_TYPES', [
    'physical' => 'کالای فیزیکی',
    'digital'  => 'کالای دیجیتال',
    'service'  => 'خدمات'
]);

define('PRODUCT_STATUSES', [
    'active'       => 'فعال',
    'inactive'     => 'غیرفعال',
    'out_of_stock' => 'ناموجود',
    'discontinued' => 'متوقف شده'
]);

/**
 * تنظیمات مربوط به سفارش‌ها
 */
define('ORDER_STATUSES', [
    'pending'    => 'در انتظار پرداخت',
    'processing' => 'در حال پردازش',
    'shipped'    => 'ارسال شده',
    'delivered'  => 'تحویل شده',
    'cancelled'  => 'لغو شده',
    'refunded'   => 'مسترد شده'
]);

/**
 * تنظیمات مربوط به پرداخت‌ها
 */
define('PAYMENT_METHODS', [
    'cash'     => 'نقدی',
    'card'     => 'کارت به کارت',
    'check'    => 'چک',
    'deposit'  => 'واریز به حساب',
    'online'   => 'درگاه آنلاین'
]);

define('PAYMENT_GATEWAYS', [
    'zarinpal'  => [
        'active'      => true,
        'merchant_id' => 'your-merchant-id',
        'callback'    => SITE_URL . '/payment/verify/zarinpal'
    ],
    'pay'       => [
        'active'      => false,
        'api_key'     => 'your-api-key',
        'callback'    => SITE_URL . '/payment/verify/pay'
    ]
]);

/**
 * تنظیمات مربوط به واحد پول
 */
define('DEFAULT_CURRENCY', 'IRR');
define('DEFAULT_CURRENCY_SYMBOL', 'ریال');
define('CURRENCIES', [
    'IRR' => [
        'name'    => 'ریال',
        'symbol'  => 'ریال',
        'decimal' => 0
    ],
    'IRT' => [
        'name'    => 'تومان',
        'symbol'  => 'تومان',
        'decimal' => 0
    ]
]);

/**
 * تنظیمات مربوط به کاربران
 */
define('USER_TYPES', [
    'admin'      => 'مدیر',
    'accountant' => 'حسابدار',
    'staff'      => 'کارمند',
    'customer'   => 'مشتری'
]);

define('USER_STATUSES', [
    'active'    => 'فعال',
    'inactive'  => 'غیرفعال',
    'banned'    => 'مسدود',
    'pending'   => 'در انتظار تایید'
]);

/**
 * تنظیمات مربوط به جستجو
 */
define('SEARCH_MIN_LENGTH', 2);
define('SEARCH_MAX_LENGTH', 50);
define('SEARCH_RESULTS_LIMIT', 100);
define('SEARCH_HIGHLIGHT_TAG', 'mark');

/**
 * تنظیمات مربوط به نسخه پشتیبان
 */
define('BACKUP_PATH', BASE_PATH . '/backups');
define('BACKUP_FILES', true);
define('BACKUP_DB', true);
define('BACKUP_COMPRESS', true);
define('BACKUP_ENCRYPT', true);
define('BACKUP_FILENAME', 'backup_%s.zip');
define('BACKUP_MAX_FILES', 10);
define('BACKUP_MAX_SIZE', 500 * 1024 * 1024); // 500MB

/**
 * تنظیمات مربوط به API
 */
define('API_VERSION', 'v1');
define('API_DEBUG', DEBUG_MODE);
define('API_RATE_LIMIT', 60); // تعداد درخواست در دقیقه
define('API_TIMEOUT', 30); // ثانیه
define('API_RESPONSE_FORMAT', 'json'); // json, xml

/**
 * تنظیمات عمومی برنامه
 */
define('DEVELOPMENT_MODE', [
    'show_errors'        => true,
    'debug_bar'         => true,
    'query_log'         => true,
    'maintenance_mode'  => false,
    'demo_mode'         => false
]);

define('MAINTENANCE_MODE', [
    'enabled'     => false,
    'message'     => 'سایت در حال بروزرسانی است. لطفاً بعداً مراجعه کنید.',
    'retry_after' => 3600, // 1 hour
    'allowed_ips' => ['127.0.0.1']
]);

/**
 * تنظیمات سئو
 */
define('SEO_SETTINGS', [
    'title_separator'     => ' | ',
    'title_lowercase'     => true,
    'meta_description'    => APP_DESCRIPTION,
    'meta_keywords'       => APP_KEYWORDS,
    'robots'             => 'index, follow',
    'canonical'          => true,
    'pagination_rel'     => true
]);