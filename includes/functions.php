<?php
/**
 * توابع عمومی برنامه
 * 
 * Current Date: 2025-05-01 16:08:45
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Functions
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

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
function redirect($path, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION[$type] = $message;
    }
    header('Location: ' . url($path));
    exit;
}

/**
 * escape کردن متن برای جلوگیری از XSS
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * بررسی لاگین بودن کاربر
 */
function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

/**
 * بررسی دسترسی کاربر
 */
function checkPermission($permission) {
    global $auth;
    if (!$auth->hasPermission($permission)) {
        redirect('login', 'شما دسترسی لازم برای این عملیات را ندارید.', 'error');
    }
}

/**
 * ریدایرکت اگر کاربر لاگین نباشد
 */
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        redirect('login', 'لطفاً ابتدا وارد شوید.', 'warning');
    }
}

/**
 * دریافت آواتار کاربر
 */
function getUserAvatar($user_id) {
    $avatar_path = UPLOADS_PATH . "/users/{$user_id}/avatar.jpg";
    if (file_exists($avatar_path)) {
        return asset("uploads/users/{$user_id}/avatar.jpg");
    }
    return asset('img/default-avatar.png');
}

/**
 * فرمت کردن قیمت
 */
function formatPrice($price, $decimals = 0) {
    return number_format($price, $decimals, '.', ',');
}

/**
 * تبدیل تاریخ میلادی به شمسی
 */
function toJalali($date) {
    if (!$date) return '';
    $date = is_numeric($date) ? $date : strtotime($date);
    return jdate($date, 'Y/m/d');
}

/**
 * تبدیل تاریخ شمسی به میلادی
 */
function toGregorian($date) {
    if (!$date) return '';
    list($year, $month, $day) = explode('/', $date);
    $datetime = jalali_to_gregorian($year, $month, $day);
    return implode('-', $datetime);
}

/**
 * ثبت لاگ خطا
 */
function logError($message, $context = []) {
    $log_message = sprintf(
        "[%s] %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        !empty($context) ? json_encode($context) : ''
    );
    
    error_log($log_message, 3, LOGS_PATH . '/errors/app.log');
}

/**
 * ثبت لاگ فعالیت
 */
function logActivity($user_id, $action, $description = '', $data = []) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO activity_logs 
            (user_id, action, description, data, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $action,
            $description,
            json_encode($data),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);

        return true;
    } catch (Exception $e) {
        logError('خطا در ثبت لاگ فعالیت: ' . $e->getMessage());
        return false;
    }
}

/**
 * بررسی اعتبار کد ملی
 */
function isValidNationalCode($code) {
    if (!preg_match('/^[0-9]{10}$/', $code)) {
        return false;
    }
    
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += ((10 - $i) * intval($code[$i]));
    }
    
    $remainder = $sum % 11;
    $lastDigit = intval($code[9]);
    
    return ($remainder < 2 && $lastDigit == $remainder) || 
           ($remainder >= 2 && $lastDigit == (11 - $remainder));
}

/**
 * اعتبارسنجی شماره موبایل
 */
function isValidMobileNumber($mobile) {
    return preg_match('/^09[0-9]{9}$/', $mobile);
}

/**
 * تبدیل اعداد انگلیسی به فارسی
 */
function enToFa($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english, $persian, $string);
}

/**
 * تبدیل اعداد فارسی به انگلیسی
 */
function faToEn($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persian, $english, $string);
}

/**
 * برش متن با حفظ کلمات کامل
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length), ' ')) . $suffix;
}

/**
 * تولید slug از متن
 */
function slugify($text) {
    // حذف کاراکترهای غیر مجاز
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // تبدیل به حروف کوچک
    $text = mb_strtolower($text, 'UTF-8');
    // حذف - های اضافی
    $text = preg_replace('~-+~', '-', $text);
    // حذف - از ابتدا و انتها
    return trim($text, '-');
}

/**
 * آپلود فایل
 */
function uploadFile($file, $path, $allowed_types = [], $max_size = 0) {
    try {
        // بررسی خطاهای آپلود
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('خطا در آپلود فایل: ' . $file['error']);
        }

        // بررسی نوع فایل
        if (!empty($allowed_types)) {
            $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('نوع فایل مجاز نیست.');
            }
        }

        // بررسی حجم فایل
        if ($max_size > 0 && $file['size'] > $max_size) {
            throw new Exception('حجم فایل بیشتر از حد مجاز است.');
        }

        // ایجاد مسیر در صورت عدم وجود
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // تولید نام یکتا برای فایل
        $filename = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = rtrim($path, '/') . '/' . $filename;

        // انتقال فایل
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('خطا در انتقال فایل.');
        }

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];

    } catch (Exception $e) {
        logError('خطا در آپلود فایل: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * ارسال ایمیل
 */
function sendEmail($to, $subject, $body, $from = null) {
    try {
        $from = $from ?: MAIL_FROM_ADDRESS;
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $from,
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . PHP_VERSION
        ];

        if (!mail($to, $subject, $body, implode("\r\n", $headers))) {
            throw new Exception('خطا در ارسال ایمیل');
        }

        return true;
    } catch (Exception $e) {
        logError('خطا در ارسال ایمیل: ' . $e->getMessage());
        return false;
    }
}

/**
 * دریافت IP واقعی کاربر
 */
function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * تولید کد تصادفی
 */
function generateRandomCode($length = 6) {
    return substr(str_shuffle('0123456789'), 0, $length);
}

/**
 * بررسی اعتبار تاریخ شمسی
 */
function isValidJalaliDate($date) {
    if (!preg_match('/^[0-9]{4}\/(?:0[1-9]|1[0-2])\/(?:0[1-9]|[12][0-9]|3[01])$/', $date)) {
        return false;
    }
    
    list($year, $month, $day) = explode('/', $date);
    return jcheckdate($month, $day, $year);
}