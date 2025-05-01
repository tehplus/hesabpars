<?php
/**
 * کلاس مدیریت احراز هویت و امنیت
 * 
 * Current Date: 2025-05-01 15:49:10
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Authentication
 * @version 1.0.0
 */

class Auth {
    /**
     * @var PDO نمونه اتصال به دیتابیس
     */
    private $db;

    /**
     * @var array خطاها
     */
    private $errors = [];

    /**
     * @var int حداکثر تعداد تلاش برای ورود
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * @var int زمان مسدودیت به دقیقه
     */
    private const LOCKOUT_TIME = 15;

    /**
     * @var int طول توکن‌های امنیتی
     */
    private const TOKEN_LENGTH = 64;

    /**
     * سازنده کلاس
     */
    public function __construct() {
        global $db;
        $this->db = $db;
        $this->initializeSession();
    }

    /**
     * مقداردهی اولیه session
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->generateToken();
        }
    }

    /**
     * ورود کاربر
     */
    public function login($username, $password, $remember = false) {
        try {
            // بررسی تعداد تلاش‌های ناموفق
            if ($this->isLockedOut()) {
                throw new Exception('حساب کاربری شما موقتاً مسدود شده است. لطفاً بعداً تلاش کنید.');
            }

            // اعتبارسنجی ورودی‌ها
            $this->validateLoginInput($username, $password);

            // بررسی اطلاعات کاربر
            $stmt = $this->db->prepare("
                SELECT id, username, password, email, full_name, role, status, last_login 
                FROM users 
                WHERE (username = :username OR email = :email) 
                AND status = :status
            ");

            $stmt->execute([
                'username' => $username,
                'email' => $username,
                'status' => STATUS_ACTIVE
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                $this->incrementLoginAttempts();
                throw new Exception('نام کاربری یا رمز عبور اشتباه است.');
            }

            // پاک کردن تلاش‌های ناموفق
            $this->resetLoginAttempts();

            // ذخیره اطلاعات در session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_login'] = $user['last_login'];
            $_SESSION['login_time'] = time();

            // به‌روزرسانی آخرین ورود
            $this->updateLastLogin($user['id']);

            // ذخیره کوکی "مرا به خاطر بسپار"
            if ($remember) {
                $this->setRememberMeCookie($user['id']);
            }

            // ثبت لاگ ورود
            $this->logLogin($user['id'], true);

            return true;

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->logLogin(0, false, $e->getMessage());
            return false;
        }
    }

    /**
     * خروج کاربر
     */
    public function logout() {
        // ثبت لاگ خروج
        if (isset($_SESSION['user_id'])) {
            $this->logLogout($_SESSION['user_id']);
        }

        // حذف کوکی "مرا به خاطر بسپار"
        if (isset($_COOKIE['remember_token'])) {
            $this->removeRememberMeToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // پاک کردن session
        $_SESSION = [];
        session_destroy();

        // ریدایرکت به صفحه ورود
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    /**
     * بررسی لاگین بودن کاربر
     */
    public function isLoggedIn() {
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        // بررسی کوکی "مرا به خاطر بسپار"
        if (isset($_COOKIE['remember_token'])) {
            return $this->loginWithRememberToken($_COOKIE['remember_token']);
        }

        return false;
    }

    /**
     * بررسی مسدود بودن حساب
     */
    private function isLockedOut() {
        if (!isset($_SESSION['lockout_time'])) {
            return false;
        }

        if (time() - $_SESSION['lockout_time'] > self::LOCKOUT_TIME * 60) {
            unset($_SESSION['lockout_time']);
            $this->resetLoginAttempts();
            return false;
        }

        return true;
    }

    /**
     * افزایش تعداد تلاش‌های ناموفق
     */
    private function incrementLoginAttempts() {
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['lockout_time'] = time();
        }
    }

    /**
     * پاک کردن تلاش‌های ناموفق
     */
    private function resetLoginAttempts() {
        $_SESSION['login_attempts'] = 0;
        if (isset($_SESSION['lockout_time'])) {
            unset($_SESSION['lockout_time']);
        }
    }

    /**
     * به‌روزرسانی آخرین ورود
     */
    private function updateLastLogin($user_id) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET last_login = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
    }

    /**
     * تنظیم کوکی "مرا به خاطر بسپار"
     */
    private function setRememberMeCookie($user_id) {
        $token = $this->generateToken();
        $expires = time() + (30 * 24 * 60 * 60); // 30 روز

        $stmt = $this->db->prepare("
            INSERT INTO remember_tokens (user_id, token, expires_at) 
            VALUES (?, ?, FROM_UNIXTIME(?))
        ");
        $stmt->execute([$user_id, $token, $expires]);

        setcookie('remember_token', $token, $expires, '/', '', true, true);
    }

    /**
     * ورود با توکن "مرا به خاطر بسپار"
     */
    private function loginWithRememberToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.* 
                FROM users u 
                JOIN remember_tokens rt ON u.id = rt.user_id 
                WHERE rt.token = ? 
                AND rt.expires_at > NOW() 
                AND u.status = ?
            ");

            $stmt->execute([$token, STATUS_ACTIVE]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            // ذخیره اطلاعات در session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_login'] = $user['last_login'];
            $_SESSION['login_time'] = time();

            // تمدید توکن
            $this->refreshRememberToken($token);

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * تمدید توکن "مرا به خاطر بسپار"
     */
    private function refreshRememberToken($token) {
        $expires = time() + (30 * 24 * 60 * 60); // 30 روز

        $stmt = $this->db->prepare("
            UPDATE remember_tokens 
            SET expires_at = FROM_UNIXTIME(?) 
            WHERE token = ?
        ");
        $stmt->execute([$expires, $token]);

        setcookie('remember_token', $token, $expires, '/', '', true, true);
    }

    /**
     * حذف توکن "مرا به خاطر بسپار"
     */
    private function removeRememberMeToken($token) {
        $stmt = $this->db->prepare("
            DELETE FROM remember_tokens 
            WHERE token = ?
        ");
        $stmt->execute([$token]);
    }

    /**
     * ثبت لاگ ورود
     */
    private function logLogin($user_id, $success, $message = '') {
        $stmt = $this->db->prepare("
            INSERT INTO login_logs 
            (user_id, ip_address, user_agent, success, message) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $success ? 1 : 0,
            $message
        ]);
    }

    /**
     * ثبت لاگ خروج
     */
    private function logLogout($user_id) {
        $stmt = $this->db->prepare("
            INSERT INTO logout_logs 
            (user_id, ip_address, user_agent) 
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }

    /**
     * اعتبارسنجی ورودی‌های ورود
     */
    private function validateLoginInput($username, $password) {
        if (empty($username) || empty($password)) {
            throw new Exception('لطفاً نام کاربری و رمز عبور را وارد کنید.');
        }

        if (strlen($username) > 50) {
            throw new Exception('نام کاربری نمی‌تواند بیشتر از 50 کاراکتر باشد.');
        }

        if (strlen($password) > 72) {
            throw new Exception('رمز عبور نمی‌تواند بیشتر از 72 کاراکتر باشد.');
        }
    }

    /**
     * بررسی دسترسی به یک عملیات
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT 1 
            FROM role_permissions rp 
            JOIN permissions p ON rp.permission_id = p.id 
            WHERE rp.role_id = ? 
            AND p.name = ?
        ");

        $stmt->execute([$_SESSION['role'], $permission]);
        return $stmt->rowCount() > 0;
    }

    /**
     * تولید توکن امنیتی
     */
    private function generateToken($length = null) {
        $length = $length ?? self::TOKEN_LENGTH;
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * بررسی توکن CSRF
     */
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * دریافت توکن CSRF
     */
    public function getCSRFToken() {
        return $_SESSION['csrf_token'];
    }

    /**
     * دریافت خطاها
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * بررسی وجود خطا
     */
    public function hasError() {
        return !empty($this->errors);
    }

    /**
     * پاک کردن خطاها
     */
    public function clearErrors() {
        $this->errors = [];
    }
}

// ایجاد نمونه از کلاس Auth
$auth = new Auth();