<?php
/**
 * نقطه ورودی اصلی برنامه حساب پارسه
 * 
 * Current Date: 2025-05-01 15:40:47
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

// تنظیم مسیر اصلی پروژه
define('BASE_PATH', __DIR__);

// لود کردن فایل‌های مورد نیاز
require_once 'includes/init.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// شروع session
session_start();

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// دریافت URL درخواست شده
$request = $_GET['url'] ?? '';
$request = rtrim($request, '/');

// مسیرهای پیش‌فرض
$default_routes = [
    '' => 'pages/dashboard.php',
    'dashboard' => 'pages/dashboard.php',
    'products' => 'pages/products.php',
    'add-product' => 'pages/add-product.php',
    'categories' => 'pages/categories.php',
    'login' => 'pages/login.php',
    'logout' => 'pages/logout.php'
];

// صفحات عمومی که نیاز به لاگین ندارند
$public_pages = ['login', 'register', 'forgot-password'];

// بررسی لاگین برای صفحات خصوصی
if (!in_array($request, $public_pages) && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// پیدا کردن و لود کردن فایل مناسب
if (array_key_exists($request, $default_routes)) {
    $file = $default_routes[$request];
} else {
    $file = 'pages/404.php';
}

// بررسی وجود فایل
if (file_exists($file)) {
    require_once $file;
} else {
    require_once 'pages/404.php';
}