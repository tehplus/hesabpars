<?php
// تنظیمات محیط برنامه
define('APP_ENV', 'development');
define('IS_HTTPS', false);
define('DEFAULT_TIMEZONE', 'Asia/Tehran');
define('DEFAULT_CHARSET', 'UTF-8');
define('SESSION_SAME_SITE', 'Lax');
define('SESSION_NAME', 'HESABPARS_SESSID');

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'hesabpars');
define('DB_USER', 'root');
define('DB_PASS', '');

// تنظیمات سایت
define('SITE_NAME', 'حساب پارسه');
define('SITE_URL', 'http://localhost/hesabpars');
define('BASE_URL', '/hesabpars');

// تنظیمات امنیتی
define('HASH_COST', 10);
define('SESSION_LIFETIME', 3600);

// تنظیمات نمایشی
define('ITEMS_PER_PAGE', 20);
define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');

// مسیرها
define('UPLOADS_PATH', __DIR__ . '/../uploads');
define('LOGS_PATH', __DIR__ . '/../logs');
define('CACHE_PATH', __DIR__ . '/../cache');
define('INCLUDES_PATH', __DIR__);

// تنظیمات منطقه‌ای
date_default_timezone_set('Asia/Tehran');

// توابع کمکی
function asset($path) {
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

function url($path) {
    return SITE_URL . '/' . ltrim($path, '/');
}