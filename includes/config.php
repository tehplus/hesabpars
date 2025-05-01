<?php
// تنظیمات دیتابیس
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'hesabpars');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// تنظیمات مسیرها
if (!defined('BASE_URL')) {
    define('BASE_URL', '/hesabpars');
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'حساب پارسه');
}

// تنظیمات امنیتی
define('HASH_COST', 10);
define('SESSION_LIFETIME', 3600);

// تنظیمات نمایشی
define('ITEMS_PER_PAGE', 20);
define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');

// مسیرهای ذخیره فایل
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', BASE_PATH . '/uploads');
}

if (!defined('LOG_PATH')) {
    define('LOG_PATH', BASE_PATH . '/logs');
}

// ایجاد مسیرهای مورد نیاز اگر وجود نداشته باشند
foreach ([UPLOAD_PATH, LOG_PATH] as $path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// اتصال به دیتابیس
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("خطا در اتصال به دیتابیس. لطفاً با پشتیبانی تماس بگیرید.");
}

// بررسی وجود جداول مورد نیاز
try {
    $tables = ['categories', 'currencies', 'tax_types', 'tax_units', 'products'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            $missing_tables[] = $table;
        }
    }

    if (!empty($missing_tables)) {
        $sql = file_get_contents(BASE_PATH . '/database/schema.sql');
        if ($sql) {
            $db->exec($sql);
            
            // اضافه کردن داده‌های پیش‌فرض
            require_once BASE_PATH . '/database/insert_defaults.php';
        }
    }
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}