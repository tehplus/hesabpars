<?php
/**
 * فایل اصلی پروژه
 * 
 * Current Date: 2025-05-01 16:23:00
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// تنظیم مسیرهای اصلی
define('BASE_PATH', __DIR__);
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
    LOGS_PATH,
    LOGS_PATH . '/errors',
    LOGS_PATH . '/sql',
    LOGS_PATH . '/access',
    CACHE_PATH
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
ini_set('error_log', LOGS_PATH . '/errors/php_errors.log');

// تنظیم timezone
date_default_timezone_set('Asia/Tehran');

// تنظیم encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// لود کردن فایل‌های اصلی
require_once INCLUDES_PATH . '/config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/database.php';
require_once INCLUDES_PATH . '/auth.php';

try {
    // ایجاد نمونه از کلاس دیتابیس
    $db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    
    // ایجاد نمونه از کلاس احراز هویت
    $auth = new Auth($db);
    
    // تنظیم متغیرهای عمومی
    $errors = [];
    $messages = [];
    
    // بررسی درخواست
    $request = $_SERVER['REQUEST_URI'];
    $basePath = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
    $path = str_replace($basePath, '', $request);
    $path = parse_url($path, PHP_URL_PATH);
    
    // حذف / از انتهای مسیر
    $path = rtrim($path, '/');
    
    // مسیریابی
    switch ($path) {
        case '':
        case '/':
            if (!$auth->isLoggedIn()) {
                require PAGES_PATH . '/login.php';
            } else {
                require PAGES_PATH . '/dashboard.php';
            }
            break;
            
        case '/login':
            if ($auth->isLoggedIn()) {
                header('Location: /');
                exit;
            }
            require PAGES_PATH . '/login.php';
            break;
            
        case '/register':
            if ($auth->isLoggedIn()) {
                header('Location: /');
                exit;
            }
            require PAGES_PATH . '/register.php';
            break;
            
        case '/dashboard':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/dashboard.php';
            break;
            
        case '/products':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/products/list.php';
            break;
            
        case '/products/add':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/products/add.php';
            break;
            
        case '/products/edit':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/products/edit.php';
            break;
            
        case '/customers':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/customers/list.php';
            break;
            
        case '/customers/add':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/customers/add.php';
            break;
            
        case '/customers/edit':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/customers/edit.php';
            break;
            
        case '/invoices':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/invoices/list.php';
            break;
            
        case '/invoices/add':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/invoices/add.php';
            break;
            
        case '/invoices/edit':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/invoices/edit.php';
            break;
            
        case '/reports':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/reports/index.php';
            break;
            
        case '/settings':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/settings/index.php';
            break;
            
        case '/profile':
            if (!$auth->isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            require PAGES_PATH . '/profile.php';
            break;
            
        case '/logout':
            $auth->logout();
            header('Location: /login');
            exit;
            break;
            
        default:
            http_response_code(404);
            require PAGES_PATH . '/404.php';
            break;
    }
    
} catch (Exception $e) {
    // لاگ کردن خطا
    error_log(sprintf(
        "[%s] Error: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ), 3, LOGS_PATH . '/errors/app.log');
    
    // نمایش صفحه خطا
    http_response_code(500);
    require PAGES_PATH . '/500.php';
}