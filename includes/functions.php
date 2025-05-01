<?php
/**
 * توابع عمومی برنامه
 * Current Date: 2025-05-01 16:54:20
 * Current User: tehplus
 */

// جلوگیری از دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

/**
 * پاکسازی داده‌های ورودی
 * 
 * @param string|array $data داده ورودی
 * @return string|array داده تمیز شده
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
 * 
 * @param string $url آدرس مقصد
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * نمایش پیام‌های خطا
 * 
 * @param array $errors آرایه پیام‌های خطا
 * @return string HTML پیام‌های خطا
 */
function showErrors($errors) {
    if (!empty($errors)) {
        $output = '<div class="alert alert-danger">';
        $output .= '<ul class="mb-0">';
        foreach ($errors as $error) {
            $output .= '<li>' . $error . '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
        return $output;
    }
    return '';
}

/**
 * نمایش پیام موفقیت
 * 
 * @param string $message پیام
 * @return string HTML پیام
 */
function showSuccess($message) {
    if (!empty($message)) {
        return '<div class="alert alert-success">' . $message . '</div>';
    }
    return '';
}

/**
 * تبدیل تاریخ میلادی به شمسی
 * 
 * @param string $date تاریخ میلادی
 * @param string $format فرمت خروجی
 * @return string تاریخ شمسی
 */
function toJalali($date, $format = 'Y/m/d') {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return $timestamp ? jdate($format, $timestamp) : '';
}

/**
 * فرمت کردن قیمت
 * 
 * @param float $price قیمت
 * @return string قیمت فرمت شده
 */
function formatPrice($price) {
    return number_format($price) . ' ریال';
}

/**
 * تولید توکن CSRF
 * 
 * @return string توکن
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * بررسی توکن CSRF
 * 
 * @param string $token توکن ارسالی
 * @return bool
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ایجاد فیلد مخفی CSRF
 * 
 * @return string HTML فیلد مخفی
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * محدود کردن طول متن
 * 
 * @param string $text متن
 * @param int $length طول مورد نظر
 * @return string متن کوتاه شده
 */
function limitText($text, $length = 100) {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * بررسی درخواست POST
 * 
 * @return bool
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * بررسی درخواست GET
 * 
 * @return bool
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * دریافت مقدار از POST
 * 
 * @param string $key کلید
 * @param mixed $default مقدار پیش‌فرض
 * @return mixed
 */
function post($key, $default = '') {
    return isset($_POST[$key]) ? clean($_POST[$key]) : $default;
}

/**
 * دریافت مقدار از GET
 * 
 * @param string $key کلید
 * @param mixed $default مقدار پیش‌فرض
 * @return mixed
 */
function get($key, $default = '') {
    return isset($_GET[$key]) ? clean($_GET[$key]) : $default;
}

/**
 * بررسی وجود فایل آپلود شده
 * 
 * @param string $key نام فیلد
 * @return bool
 */
function hasFile($key) {
    return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
}

/**
 * ذخیره فایل آپلود شده
 * 
 * @param string $key نام فیلد
 * @param string $path مسیر ذخیره
 * @return string|false نام فایل یا false
 */
function saveFile($key, $path) {
    if (!hasFile($key)) {
        return false;
    }

    $file = $_FILES[$key];
    $name = uniqid() . '_' . $file['name'];
    $destination = rtrim($path, '/') . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return false;
    }

    return $name;
}

/**
 * ایجاد دایرکتوری
 * 
 * @param string $path مسیر
 * @return bool
 */
function createDir($path) {
    return !file_exists($path) && mkdir($path, 0777, true);
}

/**
 * لاگ کردن خطا
 * 
 * @param string $message پیام خطا
 * @param array $context اطلاعات اضافی
 * @return void
 */
function logError($message, $context = []) {
    $log = sprintf(
        "[%s] %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
    );
    
    error_log($log, 3, LOG_PATH . '/errors.log');
}