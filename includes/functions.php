<?php
/**
 * فایل توابع عمومی برنامه
 */

/**
 * نمایش پیغام خطا
 */
function showError($message) {
    echo '<div class="alert alert-danger" role="alert">';
    echo htmlspecialchars($message);
    echo '</div>';
}

/**
 * نمایش پیغام موفقیت
 */
function showSuccess($message) {
    echo '<div class="alert alert-success" role="alert">';
    echo htmlspecialchars($message);
    echo '</div>';
}

/**
 * دریافت اطلاعات کاربر جاری
 */
function getCurrentUser() {
    global $db;
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

/**
 * فرمت کردن مبلغ
 */
function formatMoney($amount) {
    return number_format($amount, 0, '.', ',');
}

/**
 * تبدیل تاریخ میلادی به شمسی
 */
function toJalali($date) {
    if (!$date) return '';
    $datetime = new DateTime($date);
    $timezone = new DateTimeZone(APP_TIMEZONE);
    $datetime->setTimezone($timezone);
    
    require_once BASE_PATH . '/lib/jdf.php';
    return jdate("Y/m/d H:i", $datetime->getTimestamp());
}

/**
 * بررسی درخواست Ajax
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * تولید پیام خطا برای درخواست‌های Ajax
 */
function jsonError($message) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

/**
 * تولید پیام موفقیت برای درخواست‌های Ajax
 */
function jsonSuccess($data = [], $message = 'عملیات با موفقیت انجام شد') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}