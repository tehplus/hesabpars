<?php
/**
 * فایل اصلی برنامه
 * 
 * Current Date: 2025-05-01 16:55:54
 * Current User: tehplus
 */

// مسیر اصلی برنامه
define('BASE_PATH', __DIR__);

// لود کردن فایل‌های اصلی
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

// ایجاد نمونه از کلاس‌ها
$db = new Database();
$auth = new Auth($db);

// بررسی مسیر درخواستی
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace(BASE_URL, '', $path);

// مسیریابی
switch ($path) {
    case '/':
    case '/index.php':
        if ($auth->isLoggedIn()) {
            require_once 'pages/dashboard.php';
        } else {
            require_once 'pages/login.php';
        }
        break;

    case '/login':
        if ($auth->isLoggedIn()) {
            redirect(BASE_URL . '/dashboard');
        }
        require_once 'pages/login.php';
        break;

    case '/logout':
        $auth->logout();
        redirect(BASE_URL . '/login');
        break;

    case '/register':
        if ($auth->isLoggedIn()) {
            redirect(BASE_URL . '/dashboard');
        }
        require_once 'pages/register.php';
        break;

    case '/dashboard':
        if (!$auth->isLoggedIn()) {
            redirect(BASE_URL . '/login');
        }
        require_once 'pages/dashboard.php';
        break;

    default:
        // صفحه 404
        header("HTTP/1.0 404 Not Found");
        require_once 'pages/404.php';
        break;
}