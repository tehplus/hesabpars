<?php
/**
 * تنظیمات اصلی برنامه حساب پارسه
 * این فایل شامل تمام تنظیمات پایه و ثابت‌های برنامه است
 * 
 * Current Date: 2025-05-01 15:44:35
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Configuration
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

/**
 * تنظیمات محیط برنامه
 */
define('ENVIRONMENT', 'development'); // یا 'production'
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('MAINTENANCE_MODE', false);

/**
 * تنظیمات پایه سایت
 */
define('SITE_NAME', 'حساب پارسه');
define('SITE_VERSION', '1.0.0');
define('SITE_DESCRIPTION', 'سیستم حسابداری و مدیریت کسب و کار');
define('SITE_KEYWORDS', 'حسابداری، مدیریت، فروش، خرید، انبار، گزارش');
define('SITE_AUTHOR', 'TehPlus');

/**
 * تنظیمات URL
 */
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$folder = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . $host . str_replace('\\', '', $folder);
$baseUrl = rtrim(dirname($baseUrl), '/');

define('BASE_URL', $baseUrl);
define('ADMIN_URL', BASE_URL . '/admin');
define('API_URL', BASE_URL . '/api');

/**
 * تنظیمات مسیرها
 */
define('UPLOADS_URL', BASE_URL . '/uploads');
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/img');

/**
 * تنظیمات دیتابیس
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'hesabpars');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

/**
 * تنظیمات امنیتی
 */
define('HASH_COST', 10);
define('SESSION_LIFETIME', 7200); // 2 ساعت
define('REMEMBER_ME_LIFETIME', 2592000); // 30 روز
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 دقیقه
define('PASSWORD_MIN_LENGTH', 8);
define('TOKEN_LIFETIME', 3600); // 1 ساعت

/**
 * تنظیمات آپلود فایل
 */
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10 مگابایت
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('IMAGE_MAX_WIDTH', 2000);
define('IMAGE_MAX_HEIGHT', 2000);
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);

/**
 * تنظیمات ایمیل
 */
define('MAIL_DRIVER', 'smtp');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'no-reply@hesabpars.com');
define('MAIL_FROM_NAME', 'حساب پارسه');

/**
 * تنظیمات پیامک
 */
define('SMS_DRIVER', 'kavenegar');
define('SMS_API_KEY', 'your-api-key');
define('SMS_SENDER', '10008663');
define('SMS_TEMPLATE', 'verify');

/**
 * تنظیمات کش
 */
define('CACHE_DRIVER', 'file');
define('CACHE_PREFIX', 'hesabpars_');
define('CACHE_LIFETIME', 3600);

/**
 * تنظیمات لاگ
 */
define('LOG_CHANNEL', 'daily');
define('LOG_LEVEL', 'debug');
define('LOG_MAX_FILES', 30);

/**
 * تنظیمات زبان
 */
define('DEFAULT_LANGUAGE', 'fa');
define('AVAILABLE_LANGUAGES', ['fa', 'en']);
define('RTL_LANGUAGES', ['fa', 'ar']);

/**
 * تنظیمات زمان
 */
define('DEFAULT_TIMEZONE', 'Asia/Tehran');
define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y/m/d H:i:s');
define('TIMESTAMP_FORMAT', 'Y-m-d H:i:s');

/**
 * تنظیمات پیش‌فرض
 */
define('DEFAULT_CURRENCY', 'IRR');
define('DEFAULT_CURRENCY_SYMBOL', 'ریال');
define('DEFAULT_THEME', 'default');
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE', 100);

/**
 * اتصال به دیتابیس
 */
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . 
        ";dbname=" . DB_NAME . 
        ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . DB_CHARSET . "' COLLATE '" . DB_COLLATE . "'"
        ]
    );
} catch (PDOException $e) {
    // لاگ کردن خطا
    error_log(sprintf(
        "[%s] Database connection failed: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    ), 3, LOGS_PATH . '/errors/database.log');

    // نمایش خطای مناسب
    if (DEBUG_MODE) {
        die(sprintf('خطا در اتصال به پایگاه داده: %s', $e->getMessage()));
    } else {
        die('خطا در اتصال به پایگاه داده. لطفاً با پشتیبانی تماس بگیرید.');
    }
}

/**
 * تنظیمات PHP
 */
// تنظیم منطقه زمانی
date_default_timezone_set(DEFAULT_TIMEZONE);

// تنظیم encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// تنظیمات امنیتی session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// تنظیمات خطایابی
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// تنظیم مسیر لاگ خطاها
ini_set('error_log', LOGS_PATH . '/errors/php_error.log');
ini_set('log_errors', 1);

/**
 * ثابت‌های وضعیت
 */
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);
define('STATUS_DELETED', -1);
define('STATUS_PENDING', 2);
define('STATUS_APPROVED', 3);
define('STATUS_REJECTED', 4);

/**
 * ثابت‌های نقش‌ها
 */
define('ROLE_ADMIN', 1);
define('ROLE_MANAGER', 2);
define('ROLE_USER', 3);
define('ROLE_GUEST', 4);

/**
 * ثابت‌های دسترسی‌ها
 */
define('PERMISSION_VIEW', 1);
define('PERMISSION_CREATE', 2);
define('PERMISSION_EDIT', 3);
define('PERMISSION_DELETE', 4);

/**
 * ثابت‌های انواع فایل
 */
define('FILE_TYPE_IMAGE', 1);
define('FILE_TYPE_DOCUMENT', 2);
define('FILE_TYPE_VIDEO', 3);
define('FILE_TYPE_AUDIO', 4);

/**
 * ثابت‌های وضعیت پرداخت
 */
define('PAYMENT_PENDING', 1);
define('PAYMENT_COMPLETED', 2);
define('PAYMENT_FAILED', 3);
define('PAYMENT_REFUNDED', 4);

/**
 * ثابت‌های وضعیت سفارش
 */
define('ORDER_PENDING', 1);
define('ORDER_PROCESSING', 2);
define('ORDER_COMPLETED', 3);
define('ORDER_CANCELLED', 4);
define('ORDER_REFUNDED', 5);

/**
 * ثابت‌های نوع تراکنش
 */
define('TRANSACTION_DEPOSIT', 1);
define('TRANSACTION_WITHDRAW', 2);
define('TRANSACTION_TRANSFER', 3);

/**
 * ثابت‌های نوع اعلان
 */
define('NOTIFICATION_INFO', 1);
define('NOTIFICATION_SUCCESS', 2);
define('NOTIFICATION_WARNING', 3);
define('NOTIFICATION_ERROR', 4);