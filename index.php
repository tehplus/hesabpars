<?php
// تعریف مسیر اصلی برنامه
define('BASE_PATH', __DIR__);

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// لود کردن فایل‌های اصلی
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

// بررسی مسیر درخواستی
$request = $_SERVER['REQUEST_URI'];
$path = str_replace(BASE_URL, '', $request);

// مسیریابی
if (empty($path) || $path == '/') {
    require_once 'pages/login.php';
} else {
    header("Location: " . BASE_URL);
    exit;
}