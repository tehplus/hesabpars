<?php
/**
 * فایل راه‌اندازی اولیه برنامه
 * 
 * Current Date: 2025-05-01 15:47:17
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

// تنظیمات PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// تنظیمات زمان و منطقه زمانی
date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// تنظیمات session
$session_options = [
    'cookie_httponly' => 1,
    'cookie_secure' => 0, // در محیط تولید باید 1 شود
    'cookie_samesite' => 'Lax',
    'use_only_cookies' => 1,
    'gc_maxlifetime' => 7200, // 2 ساعت
    'cookie_lifetime' => 0,
    'name' => 'HESABPARS_SESSION'
];

// اعمال تنظیمات session قبل از شروع session
foreach ($session_options as $key => $value) {
    ini_set("session.$key", $value);
}

// شروع session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیم error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_message = sprintf(
        "[%s] Error [%d] %s on line %d in file %s\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errline,
        $errfile
    );
    
    error_log($error_message, 3, LOGS_PATH . '/errors/custom_error.log');
    
    if (ini_get('display_errors')) {
        printf("<div style='color:red;'>Error: %s</div>", $error_message);
    }
    
    return true;
});

// تنظیم exception handler
set_exception_handler(function($exception) {
    $error_message = sprintf(
        "[%s] Exception: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    );
    
    error_log($error_message, 3, LOGS_PATH . '/errors/exception.log');
    
    if (ini_get('display_errors')) {
        printf("<div style='color:red;'>Exception: %s</div>", $error_message);
    }
});

// تنظیم shutdown handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = sprintf(
            "[%s] Fatal Error: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        error_log($error_message, 3, LOGS_PATH . '/errors/fatal_error.log');
        
        if (ini_get('display_errors')) {
            printf("<div style='color:red;'>Fatal Error: %s</div>", $error_message);
        }
    }
});

// تنظیم headers امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// لود کردن فایل‌های اصلی
require_once INCLUDES_PATH . '/config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';