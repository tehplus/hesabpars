<?php
/**
 * توابع عمومی برنامه
 */

// جلوگیری از دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

/**
 * پاکسازی داده‌های ورودی
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * ریدایرکت به یک صفحه
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}