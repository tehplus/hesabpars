<?php
/**
 * File: init.php
 * Description: فایل راه‌اندازی اصلی برنامه
 */

// تنظیم مسیر اصلی پروژه
define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

// لود کردن فایل‌های اصلی به ترتیب اهمیت
require_once __DIR__ . '/config.php';  // اول از همه تنظیمات لود میشه
require_once __DIR__ . '/db.php';      // بعد اتصال دیتابیس
require_once __DIR__ . '/auth.php';    // و در نهایت سیستم احراز هویت

// شروع session اگر شروع نشده باشه
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیم header های امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
if (ENVIRONMENT === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// تنظیم zone time
date_default_timezone_set(DEFAULT_TIMEZONE);

// بررسی وجود متغیرهای ضروری
if (!defined('BASE_URL')) {
    die('ERROR: BASE_URL is not defined. Please check config.php');
}

// بررسی اتصال دیتابیس
if (!isset($db) || !($db instanceof PDO)) {
    die('ERROR: Database connection is not established. Please check db.php');
}

// تنظیم error handler سفارشی
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_message = date('Y-m-d H:i:s') . " Error [$errno] $errstr on line $errline in file $errfile\n";
    error_log($error_message, 3, LOG_PATH . '/error.log');
    
    if (ENVIRONMENT === 'development') {
        echo "<b>Error:</b> [$errno] $errstr<br>";
        echo "Line: $errline<br>";
        echo "File: $errfile<br>";
    }
    
    return true;
});

// تنظیم exception handler سفارشی
set_exception_handler(function($exception) {
    $error_message = date('Y-m-d H:i:s') . " Uncaught Exception: " . $exception->getMessage() . "\n";
    error_log($error_message, 3, LOG_PATH . '/error.log');
    
    if (ENVIRONMENT === 'development') {
        echo "<b>Fatal Error:</b> " . $exception->getMessage() . "<br>";
        echo "Line: " . $exception->getLine() . "<br>";
        echo "File: " . $exception->getFile() . "<br>";
    } else {
        // در محیط production کاربر را به صفحه خطا هدایت می‌کنیم
        header('Location: ' . BASE_URL . '/error.php');
    }
    
    exit(1);
});

// تنظیم shutdown handler برای گرفتن fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = date('Y-m-d H:i:s') . " Fatal Error: " . $error['message'] . "\n";
        error_log($error_message, 3, LOG_PATH . '/error.log');
        
        if (ENVIRONMENT === 'development') {
            echo "<b>Fatal Error:</b> " . $error['message'] . "<br>";
            echo "Line: " . $error['line'] . "<br>";
            echo "File: " . $error['file'] . "<br>";
        } else {
            header('Location: ' . BASE_URL . '/error.php');
        }
    }
});