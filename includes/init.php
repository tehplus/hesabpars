<?php
/**
 * فایل راه‌اندازی اولیه برنامه
 * 
 * Current Date: 2025-05-01 16:41:17
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// بررسی و ایجاد دایرکتوری‌های مورد نیاز
$required_directories = [
    UPLOADS_PATH,
    UPLOADS_PATH . '/products',
    UPLOADS_PATH . '/users',
    LOGS_PATH,
    LOGS_PATH . '/errors',
    LOGS_PATH . '/sql',
    LOGS_PATH . '/auth',
    LOGS_PATH . '/access',
    CACHE_PATH
];

foreach ($required_directories as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            die("خطا در ایجاد دایرکتوری: $dir");
        }
    }
}

// لود کردن فایل تنظیمات
require_once INCLUDES_PATH . '/config.php';

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/errors/php_errors.log');

// تنظیم timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// تنظیم charset
mb_internal_encoding(DEFAULT_CHARSET);
mb_http_output(DEFAULT_CHARSET);
header('Content-Type: text/html; charset=' . DEFAULT_CHARSET);

// تنظیمات امنیتی session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', IS_HTTPS);
ini_set('session.cookie_samesite', SESSION_SAME_SITE);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.name', SESSION_NAME);

// راه‌اندازی session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیم CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

// لود کردن توابع عمومی
require_once INCLUDES_PATH . '/functions.php';

// لود کردن کلاس‌های اصلی
require_once INCLUDES_PATH . '/database.php';
require_once INCLUDES_PATH . '/classes/Auth.php';  // مسیر جدید

// ایجاد نمونه از کلاس‌ها
$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$auth = new Auth($db);

// تنظیم headers امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (IS_HTTPS) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}