<?php
/**
 * توابع عمومی برنامه
 * 
 * @package HesabPars
 * @author TehPlus
 * @version 1.0.0
 * Current Date: 2025-05-01 15:35:50
 * Current User: tehplus
 */

if (!function_exists('asset')) {
    /**
     * تولید URL برای فایل‌های asset
     */
    function asset($path) {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * تولید URL کامل
     */
    function url($path) {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * ریدایرکت به URL مشخص شده
     */
    function redirect($path) {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('isLoggedIn')) {
    /**
     * بررسی لاگین بودن کاربر
     */
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('getCurrentUser')) {
    /**
     * دریافت اطلاعات کاربر جاری
     */
    function getCurrentUser() {
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('escape')) {
    /**
     * escape کردن متن برای جلوگیری از XSS
     */
    function escape($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * تولید رشته تصادفی
     */
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 
            ceil($length/strlen($x)))), 1, $length);
    }
}

if (!function_exists('formatNumber')) {
    /**
     * فرمت‌بندی اعداد
     */
    function formatNumber($number) {
        return number_format($number, 0, '.', ',');
    }
}

if (!function_exists('formatDate')) {
    /**
     * فرمت‌بندی تاریخ
     */
    function formatDate($date) {
        return date('Y/m/d H:i', strtotime($date));
    }
}

if (!function_exists('createAlert')) {
    /**
     * ایجاد پیام هشدار
     */
    function createAlert($type, $message) {
        $_SESSION['alert'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!function_exists('showAlert')) {
    /**
     * نمایش پیام هشدار
     */
    function showAlert() {
        if (isset($_SESSION['alert'])) {
            $alert = $_SESSION['alert'];
            unset($_SESSION['alert']);
            return sprintf(
                '<div class="alert alert-%s alert-dismissible fade show" role="alert">
                    %s
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>',
                $alert['type'],
                $alert['message']
            );
        }
        return '';
    }
}