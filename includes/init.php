<?php
/**
 * فایل راه‌اندازی اولیه برنامه
 * 
 * این فایل وظیفه راه‌اندازی اولیه برنامه را بر عهده دارد:
 * - تنظیم ثابت‌های اصلی
 * - تنظیم error reporting
 * - راه‌اندازی session 
 * - تنظیم timezone و charset
 * - لود کردن فایل‌های اصلی
 * 
 * Current Date: 2025-05-01 16:29:37
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// تنظیم error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('display_startup_errors', APP_ENV === 'development' ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/errors/php_errors.log');

// تنظیم timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// تنظیم charset
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: text/html; charset=UTF-8');

// تنظیمات امنیتی
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', IS_HTTPS);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.name', SESSION_NAME);

// بررسی و ایجاد دایرکتوری‌های مورد نیاز
$required_directories = [
    UPLOADS_PATH,
    UPLOADS_PATH . '/products',
    UPLOADS_PATH . '/users',
    UPLOADS_PATH . '/temp',
    LOGS_PATH,
    LOGS_PATH . '/errors',
    LOGS_PATH . '/sql',
    LOGS_PATH . '/access',
    CACHE_PATH
];

foreach ($required_directories as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            error_log("خطا در ایجاد دایرکتوری: $dir");
            die("خطا در ایجاد دایرکتوری‌های مورد نیاز");
        }
    }
}

// بررسی دسترسی‌های دایرکتوری‌ها
$writable_directories = [
    UPLOADS_PATH,
    LOGS_PATH,
    CACHE_PATH
];

foreach ($writable_directories as $dir) {
    if (!is_writable($dir)) {
        error_log("خطا در دسترسی به دایرکتوری: $dir");
        die("خطا در دسترسی به دایرکتوری‌ها");
    }
}

// راه‌اندازی session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => IS_HTTPS,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

// تنظیم CSRF token اگر وجود نداشته باشد
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// تنظیم زمان آخرین فعالیت
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// بررسی timeout session
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// لود کردن توابع عمومی
require_once INCLUDES_PATH . '/functions.php';

// تنظیم error handler سفارشی
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error_message = sprintf(
        "[%s] Error [%d]: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );

    error_log($error_message . PHP_EOL, 3, LOGS_PATH . '/errors/php_errors.log');

    if (APP_ENV === 'development') {
        echo "<div style='color:red;'>" . htmlspecialchars($error_message) . "</div>";
    }

    return true;
});

// تنظیم exception handler سفارشی
set_exception_handler(function($exception) {
    $error_message = sprintf(
        "[%s] Exception: %s in %s on line %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log($error_message . PHP_EOL, 3, LOGS_PATH . '/errors/php_errors.log');

    if (APP_ENV === 'development') {
        echo "<div style='color:red;'>" . nl2br(htmlspecialchars($error_message)) . "</div>";
    } else {
        require_once PAGES_PATH . '/500.php';
    }
});

// تنظیم shutdown handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = sprintf(
            "[%s] Fatal Error: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );

        error_log($error_message . PHP_EOL, 3, LOGS_PATH . '/errors/php_errors.log');

        if (APP_ENV === 'development') {
            echo "<div style='color:red;'>" . htmlspecialchars($error_message) . "</div>";
        } else {
            require_once PAGES_PATH . '/500.php';
        }
    }
});

// بررسی نسخه PHP
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    die(sprintf(
        'نسخه PHP شما (%s) از حداقل نسخه مورد نیاز (%s) کمتر است.',
        PHP_VERSION,
        MIN_PHP_VERSION
    ));
}

// تنظیم headers امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (IS_HTTPS) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// لود کردن فایل‌های اصلی
require_once INCLUDES_PATH . '/database.php';
require_once INCLUDES_PATH . '/auth.php';

// راه‌اندازی اتصال به دیتابیس
try {
    $db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
} catch (Exception $e) {
    error_log($e->getMessage(), 3, LOGS_PATH . '/errors/database.log');
    die('خطا در اتصال به دیتابیس');
}

// راه‌اندازی کلاس احراز هویت
$auth = new Auth($db);

// تنظیم متغیرهای عمومی
$errors = [];
$messages = [];

// افزودن اطلاعات درخواست به متغیرهای عمومی
$request = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'query' => $_GET,
    'post' => $_POST,
    'files' => $_FILES,
    'headers' => getallheaders(),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT']
];

// لاگ کردن درخواست در حالت توسعه
if (APP_ENV === 'development' && LOG_REQUESTS) {
    error_log(
        sprintf(
            "[%s] %s %s\nData: %s\n",
            date('Y-m-d H:i:s'),
            $request['method'],
            $request['uri'],
            json_encode([
                'get' => $request['query'],
                'post' => $request['post'],
                'headers' => $request['headers']
            ], JSON_UNESCAPED_UNICODE)
        ),
        3,
        LOGS_PATH . '/access/requests.log'
    );
}