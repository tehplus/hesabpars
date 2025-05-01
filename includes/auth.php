<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تابع بررسی لاگین بودن کاربر
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// تابع خروج کاربر
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// تابع بررسی دسترسی ادمین
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// تابع ریدایرکت کاربران غیر مجاز
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// تابع ریدایرکت کاربران غیر ادمین
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * بررسی دسترسی کاربر و نمایش خطا
 * @param string $permission نام دسترسی
 */
function checkPermission($permission) {
    if (!hasPermission($permission)) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'خطای دسترسی',
            'message' => 'شما دسترسی لازم برای این عملیات را ندارید'
        ];
        
        // اگر درخواست Ajax باشه
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'شما دسترسی لازم برای این عملیات را ندارید'
            ]);
            exit;
        }

        // در غیر اینصورت ریدایرکت به داشبورد
        header('Location: /dashboard.php');
        exit;
    }
}

/**
 * بررسی دسترسی کاربر
 * @param string $permission نام دسترسی
 * @return bool نتیجه بررسی
 */
function hasPermission($permission) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // در این مرحله همه دسترسی‌ها رو true برمی‌گردونیم
    // در آینده سیستم دسترسی‌های پیچیده‌تری پیاده‌سازی میشه
    $allowedPermissions = [
        'view_categories',
        'add_categories',
        'edit_categories',
        'delete_categories',
        'bulk_edit_categories'
    ];

    return in_array($permission, $allowedPermissions);
}