<?php
/**
 * توابع عمومی برنامه
 * 
 * Current Date: 2025-05-01 15:40:47
 * Current User: tehplus
 * 
 * @package HesabPars
 * @version 1.0.0
 */

/**
 * تولید URL برای فایل‌های asset
 */
function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * تولید URL کامل
 */
function url($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * ریدایرکت به URL مشخص شده
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

/**
 * escape کردن متن برای جلوگیری از XSS
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}