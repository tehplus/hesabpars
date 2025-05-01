<?php
/**
 * فایل راه‌اندازی اولیه برنامه
 * 
 * Current Date: 2025-05-01 16:08:45
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
defined('BASE_PATH') or die('دسترسی مستقیم به این فایل مجاز نیست.');

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);
ini_set('display_startup_errors', DEBUG_MODE ? 1 : 0);

// تنظیم default timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// تنظیم encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// تنظیم مسیرهای اصلی
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('PAGES_PATH', BASE_PATH . '/pages');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');
define('CACHE_PATH', BASE_PATH . '/cache');

// بررسی و ایجاد دایرکتوری‌های مورد نیاز
$required_directories = [
    UPLOADS_PATH,
    UPLOADS_PATH . '/products',
    UPLOADS_PATH . '/users',
    UPLOADS_PATH . '/temp',
    LOGS_PATH,
    LOGS_PATH . '/errors',
    LOGS_PATH . '/access',
    LOGS_PATH . '/debug',
    CACHE_PATH
];

foreach ($required_directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// تنظیمات session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', IS_HTTPS);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.name', SESSION_NAME);

// شروع session اگر شروع نشده باشد
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
    
    error_log($error_message, 3, LOGS_PATH . '/errors/php_errors.log');
    
    if (DEBUG_MODE) {
        printf("<div style='color:red;'>Error: %s</div>", $error_message);
    }
    
    return true;
});

// تنظیم exception handler
set_exception_handler(function($exception) {
    $error_message = sprintf(
        "[%s] Exception: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($error_message, 3, LOGS_PATH . '/errors/exceptions.log');
    
    if (DEBUG_MODE) {
        printf("<div style='color:red;'>Exception: %s</div>", $error_message);
    } else {
        // در حالت production پیام خطای عمومی نمایش داده شود
        die('خطایی در سیستم رخ داده است. لطفاً بعداً تلاش کنید.');
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
        
        error_log($error_message, 3, LOGS_PATH . '/errors/fatal_errors.log');
        
        if (DEBUG_MODE) {
            printf("<div style='color:red;'>Fatal Error: %s</div>", $error_message);
        } else {
            // در حالت production پیام خطای عمومی نمایش داده شود
            die('خطایی در سیستم رخ داده است. لطفاً بعداً تلاش کنید.');
        }
    }
});

// تنظیم headers امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (IS_HTTPS) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Content Security Policy
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
    "style-src 'self' 'unsafe-inline'",
    "img-src 'self' data: https:",
    "font-src 'self'",
    "connect-src 'self'",
    "media-src 'self'",
    "object-src 'none'",
    "frame-src 'self'",
    "worker-src 'self'",
    "manifest-src 'self'"
];
header("Content-Security-Policy: " . implode('; ', $csp));

// لود کردن فایل‌های اصلی به ترتیب
$core_files = [
    INCLUDES_PATH . '/config.php',     // تنظیمات اصلی
    INCLUDES_PATH . '/functions.php',   // توابع عمومی
    INCLUDES_PATH . '/database.php',    // کلاس دیتابیس
    INCLUDES_PATH . '/auth.php',        // کلاس احراز هویت
    INCLUDES_PATH . '/jdf.php'          // تبدیل تاریخ شمسی
];

foreach ($core_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log(sprintf(
            "[%s] Core file not found: %s\n",
            date('Y-m-d H:i:s'),
            $file
        ), 3, LOGS_PATH . '/errors/init_errors.log');
        
        if (DEBUG_MODE) {
            die(sprintf('فایل هسته یافت نشد: %s', $file));
        } else {
            die('خطا در بارگذاری فایل‌های سیستم');
        }
    }
}

// ایجاد نمونه از کلاس‌های اصلی
try {
    $db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    $auth = new Auth($db);
} catch (Exception $e) {
    error_log(sprintf(
        "[%s] Failed to initialize core classes: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    ), 3, LOGS_PATH . '/errors/init_errors.log');
    
    if (DEBUG_MODE) {
        die(sprintf('خطا در راه‌اندازی کلاس‌های اصلی: %s', $e->getMessage()));
    } else {
        die('خطا در راه‌اندازی سیستم');
    }
}

// بررسی نسخه PHP
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    die(sprintf(
        'نسخه PHP شما (%s) از حداقل نسخه مورد نیاز (%s) کمتر است.',
        PHP_VERSION,
        MIN_PHP_VERSION
    ));
}

// تنظیم محیط برنامه
if (APP_ENV === 'production') {
    // تنظیمات مخصوص محیط production
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    // تنظیمات مخصوص محیط development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// بررسی دسترسی‌ها
$required_permissions = [
    UPLOADS_PATH => 0777,
    LOGS_PATH => 0777,
    CACHE_PATH => 0777
];

foreach ($required_permissions as $path => $permission) {
    if (!is_writable($path)) {
        error_log(sprintf(
            "[%s] Directory not writable: %s\n",
            date('Y-m-d H:i:s'),
            $path
        ), 3, LOGS_PATH . '/errors/init_errors.log');
        
        if (DEBUG_MODE) {
            die(sprintf('دایرکتوری %s قابل نوشتن نیست.', $path));
        }
    }
}

// تنظیم کش
if (extension_loaded('apcu') && APP_ENV === 'production') {
    ini_set('apc.enabled', 1);
    ini_set('apc.ttl', CACHE_TTL);
    ini_set('apc.gc_ttl', CACHE_TTL);
}

// افزودن autoloader برای کلاس‌های سفارشی
spl_autoload_register(function($class) {
    $file = BASE_PATH . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// تنظیم مقادیر پیش‌فرض برای متغیرهای POST
$_POST = array_map('trim', $_POST);
$_POST = array_map('stripslashes', $_POST);

// ذخیره اطلاعات درخواست برای لاگ
if (LOG_REQUESTS) {
    $request_data = [
        'url' => $_SERVER['REQUEST_URI'],
        'method' => $_SERVER['REQUEST_METHOD'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'referer' => $_SERVER['HTTP_REFERER'] ?? '',
        'post' => $_POST,
        'get' => $_GET,
        'session' => isset($_SESSION) ? array_keys($_SESSION) : [],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    error_log(
        json_encode($request_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
        3,
        LOGS_PATH . '/access/requests.log'
    );
}

// اطمینان از وجود متغیرهای session مورد نیاز
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// لود کردن زبان
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANGUAGE;
}

// تنظیم locale بر اساس زبان
setlocale(LC_ALL, LOCALES[$_SESSION['lang']]);

// آماده‌سازی متغیرهای سراسری مورد نیاز
$GLOBALS['errors'] = [];
$GLOBALS['messages'] = [];