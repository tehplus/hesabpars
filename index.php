<?php
// تعریف مسیر اصلی برنامه
define('BASE_PATH', __DIR__);

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// لود کردن فایل‌های اصلی
require_once 'includes/database.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی مسیر درخواستی
$request = $_SERVER['REQUEST_URI'];
$path = str_replace(BASE_URL, '', $request);
$path = strtok($path, '?'); // حذف query string

// مسیریابی
switch ($path) {
    case '':
    case '/':
    case '/index.php':
    case '/login':
    case '/login.php':
        require_once 'pages/login.php';
        break;
        
    case '/register':
    case '/register.php':
        require_once 'pages/register.php';
        break;
        
    case '/dashboard':
    case '/dashboard.php':
        if (!isLoggedIn()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        require_once 'pages/dashboard.php';
        break;
        
    default:
        header("Location: " . BASE_URL . "/login");
        exit;
}