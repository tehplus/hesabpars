<?php
/**
 * File: config.php
 * Description: تنظیمات اصلی برنامه
 * 
 * نکته مهم: این فایل باید قبل از همه فایل‌ها لود شود
 * برای اطمینان از تعریف شدن ثابت‌های مورد نیاز
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
}

// تنظیمات مسیرها
if (!defined('BASE_URL')) {
    // اگر در حال اجرا روی localhost هستیم
    $base_url = '/hesabpars';
    
    // اگر در حال اجرا روی سرور واقعی هستیم
    if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
        $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $base_url .= $_SERVER['HTTP_HOST'];
        $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        $base_url = rtrim($base_url, '/');
    }
    
    define('BASE_URL', $base_url);
}

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'hesabpars');
define('DB_USER', 'root');
define('DB_PASS', '');

// تنظیمات برنامه
define('SITE_NAME', 'حساب پارسه');
define('SITE_VERSION', '1.0.0');
define('SITE_LANG', 'fa');
define('SITE_CHARSET', 'UTF-8');

// تنظیمات منطقه‌ای
define('DEFAULT_TIMEZONE', 'Asia/Tehran');
date_default_timezone_set(DEFAULT_TIMEZONE);

// تنظیمات امنیتی
define('HASH_COST', 10);
define('SESSION_LIFETIME', 3600);
define('TOKEN_LIFETIME', 3600);

// مسیرهای مهم
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOG_PATH', BASE_PATH . '/logs');
define('CACHE_PATH', BASE_PATH . '/cache');

// تنظیمات نمایشی
define('ITEMS_PER_PAGE', 20);
define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', DATE_FORMAT . ' ' . TIME_FORMAT);

// ایجاد مسیرهای مورد نیاز
$required_directories = [
    UPLOAD_PATH,
    LOG_PATH,
    CACHE_PATH
];

foreach ($required_directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// تنظیمات محیط برنامه
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // یا 'production' در سرور اصلی
}

// تنظیمات خطایابی بر اساس محیط
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// تنظیم مسیر لاگ خطاها
ini_set('error_log', LOG_PATH . '/error.log');

// تنظیمات session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// تنظیمات زبان و کاراکتر
ini_set('default_charset', SITE_CHARSET);
mb_internal_encoding(SITE_CHARSET);

// توابع کمکی مورد نیاز در همه جای برنامه
if (!function_exists('asset')) {
    /**
     * تولید URL برای فایل‌های asset
     */
    function asset($path) {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * تولید URL کامل
     */
    function url($path) {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}