<?php

// تعریف مسیر اصلی برنامه
define('BASE_PATH', __DIR__);

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// لود کردن فایل‌های اصلی یکبار
require_once 'includes/config.php';     // اول config لود میشه
require_once 'includes/functions.php';   // بعد functions
require_once 'includes/init.php';        // بعد init که شامل تنظیمات اصلی هست
require_once 'includes/auth.php';        // و در نهایت auth
// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// بررسی صفحه درخواستی
$page = $_GET['page'] ?? '';
// بررسی صفحه درخواستی
$page = $_GET['page'] ?? '';

// مسیریابی
switch ($page) {
    case '':
    case 'login':
        require_once 'pages/login.php';
        break;
        
    case 'register':
        require_once 'pages/register.php';
        break;
        
    case 'dashboard':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/dashboard.php';
        break;

    case 'products':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/products.php';
        break;

    case 'add-product':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/add-product.php';
        break;

    case 'categories':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/categories.php';
        break;

    case 'inventory':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/inventory.php';
        break;

    case 'inventory-transactions':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/inventory-transactions.php';
        break;

    case 'sales':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/sales.php';
        break;

    case 'add-sale':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/add-sale.php';
        break;

    case 'profile':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/profile.php';
        break;

    case 'users':
        if (!isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        require_once 'pages/users.php';
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php?page=login");
        exit;
        break;

    default:
        header("Location: index.php?page=login");
        exit;
}