<?php
/**
 * فایل راه‌اندازی اولیه برنامه
 * 
 * Current Date: 2025-05-01 15:42:38
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// تنظیم مسیر اصلی پروژه
define('BASE_PATH', dirname(__DIR__));

// تنظیم مسیرهای اصلی
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('PAGES_PATH', BASE_PATH . '/pages');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// بررسی و ایجاد دایرکتوری‌های مورد نیاز
$required_directories = [
    UPLOADS_PATH,
    LOGS_PATH,
    UPLOADS_PATH . '/products',
    UPLOADS_PATH . '/users',
    LOGS_PATH . '/errors',
    LOGS_PATH . '/access'
];

foreach ($required_directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/errors/php_error.log');

// تنظیم زمان و منطقه زمانی
date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// شروع session اگر شروع نشده باشد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیم session handling
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // در محیط تولید باید 1 شود

// تنظیم error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_message = date('Y-m-d H:i:s') . " Error [$errno] $errstr on line $errline in file $errfile\n";
    error_log($error_message, 3, LOGS_PATH . '/errors/custom_error.log');
    
    if (ini_get('display_errors')) {
        printf("<div style='color:red;'>Error: %s</div>", $error_message);
    }
    
    return true;
});

// تنظیم exception handler
set_exception_handler(function($exception) {
    $error_message = date('Y-m-d H:i:s') . " Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    error_log($error_message, 3, LOGS_PATH . '/errors/custom_error.log');
    
    if (ini_get('display_errors')) {
        printf("<div style='color:red;'>Exception: %s</div>", $error_message);
    }
});

// تنظیم shutdown handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = date('Y-m-d H:i:s') . " Fatal Error: " . $error['message'] . 
                        " in " . $error['file'] . " on line " . $error['line'] . "\n";
        error_log($error_message, 3, LOGS_PATH . '/errors/fatal_error.log');
        
        if (ini_get('display_errors')) {
            printf("<div style='color:red;'>Fatal Error: %s</div>", $error_message);
        }
    }
});

// لود کردن فایل‌های اصلی
require_once INCLUDES_PATH . '/config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

// تنظیم headers امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');