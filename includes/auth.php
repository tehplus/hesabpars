<?php
/**
 * کلاس مدیریت احراز هویت و دسترسی‌ها
 * 
 * Current Date: 2025-05-01 16:14:26
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Authentication
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

class Auth {
    /**
     * @var Database نمونه کلاس دیتابیس
     */
    private $db;

    /**
     * @var array اطلاعات کاربر جاری
     */
    private $currentUser = null;

    /**
     * @var array کش دسترسی‌های کاربر
     */
    private $permissionsCache = [];

    /**
     * @var array تلاش‌های ناموفق ورود
     */
    private $loginAttempts = [];

    /**
     * سازنده کلاس
     * 
     * @param Database $db نمونه کلاس دیتابیس
     */
    public function __construct($db) {
        $this->db = $db;
        $this->initSession();
        $this->loadCurrentUser();
    }

    /**
     * راه‌اندازی session
     */
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => IS_HTTPS,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true
            ]);
        }

        if (!isset($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }

        if (!isset($_SESSION['token_time'])) {
            $_SESSION['token_time'] = time();
        }
    }

    /**
     * بارگذاری اطلاعات کاربر جاری
     */
    private function loadCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $query = "SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1";
            $this->currentUser = $this->db->getRow($query, [$_SESSION['user_id']]);
            
            if (!$this->currentUser) {
                $this->logout();
            }
        }
    }

    /**
     * ورود کاربر
     * 
     * @param string $username نام کاربری
     * @param string $password رمز عبور
     * @param bool $remember مرا به خاطر بسپار
     * @return bool
     */
    public function login($username, $password, $remember = false) {
        try {
            // بررسی قفل بودن حساب
            if ($this->isUserLocked($username)) {
                throw new Exception('حساب کاربری شما موقتاً قفل شده است.');
            }

            // دریافت اطلاعات کاربر
            $query = "SELECT * FROM users WHERE username = ? AND status = 'active' LIMIT 1";
            $user = $this->db->getRow($query, [$username]);

            if (!$user) {
                $this->incrementLoginAttempts($username);
                throw new Exception('نام کاربری یا رمز عبور اشتباه است.');
            }

            // بررسی رمز عبور
            if (!password_verify($password, $user['password'])) {
                $this->incrementLoginAttempts($username);
                throw new Exception('نام کاربری یا رمز عبور اشتباه است.');
            }

            // بررسی نیاز به تغییر رمز عبور
            if ($this->isPasswordExpired($user)) {
                throw new Exception('رمز عبور شما منقضی شده است. لطفاً رمز عبور خود را تغییر دهید.');
            }

            // ذخیره اطلاعات در session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            // ایجاد توکن برای "مرا به خاطر بسپار"
            if ($remember) {
                $this->setRememberToken($user['id']);
            }

            // بروزرسانی آخرین ورود
            $this->updateLastLogin($user['id']);

            // پاک کردن تلاش‌های ناموفق
            $this->clearLoginAttempts($username);

            // لود کردن کاربر جاری
            $this->loadCurrentUser();

            // ثبت لاگ
            $this->logActivity($user['id'], 'login', 'ورود موفق به سیستم');

            return true;

        } catch (Exception $e) {
            $this->logError('خطا در ورود: ' . $e->getMessage(), [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            throw $e;
        }
    }

    /**
     * خروج کاربر
     * 
     * @return void
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity($this->currentUser['id'], 'logout', 'خروج از سیستم');
        }

        // حذف توکن "مرا به خاطر بسپار"
        if (isset($_COOKIE['remember_token'])) {
            $this->deleteRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/', '', IS_HTTPS, true);
        }

        // پاک کردن session
        session_unset();
        session_destroy();
        
        $this->currentUser = null;
    }

    /**
     * بررسی لاگین بودن کاربر
     * 
     * @return bool
     */
    public function isLoggedIn() {
        return $this->currentUser !== null;
    }

    /**
     * دریافت اطلاعات کاربر جاری
     * 
     * @return array|null
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * بررسی دسترسی کاربر
     * 
     * @param string|array $permissions دسترسی‌های مورد نیاز
     * @return bool
     */
    public function hasPermission($permissions) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        // تبدیل به آرایه
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        // بررسی از کش
        $key = md5($this->currentUser['id'] . serialize($permissions));
        if (isset($this->permissionsCache[$key])) {
            return $this->permissionsCache[$key];
        }

        // دریافت دسترسی‌های کاربر
        $query = "SELECT permission FROM user_permissions WHERE user_id = ?";
        $userPermissions = $this->db->getRows($query, [$this->currentUser['id']]);
        $userPermissions = array_column($userPermissions, 'permission');

        // دریافت دسترسی‌های نقش
        $query = "SELECT permission FROM role_permissions WHERE role_id = ?";
        $rolePermissions = $this->db->getRows($query, [$this->currentUser['role_id']]);
        $rolePermissions = array_column($rolePermissions, 'permission');

        // ترکیب دسترسی‌ها
        $allPermissions = array_merge($userPermissions, $rolePermissions);

        // بررسی دسترسی‌ها
        $hasPermission = true;
        foreach ($permissions as $permission) {
            if (!in_array($permission, $allPermissions)) {
                $hasPermission = false;
                break;
            }
        }

        // ذخیره در کش
        $this->permissionsCache[$key] = $hasPermission;

        return $hasPermission;
    }

    /**
     * ثبت‌نام کاربر جدید
     * 
     * @param array $data اطلاعات کاربر
     * @return int
     */
    public function register($data) {
        try {
            $this->db->beginTransaction();

            // بررسی تکراری بودن نام کاربری
            if ($this->usernameExists($data['username'])) {
                throw new Exception('این نام کاربری قبلاً ثبت شده است.');
            }

            // بررسی تکراری بودن ایمیل
            if ($this->emailExists($data['email'])) {
                throw new Exception('این ایمیل قبلاً ثبت شده است.');
            }

            // هش کردن رمز عبور
            $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID, HASH_OPTIONS);

            // افزودن فیلدهای اضافی
            $data['status'] = 'active';
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['role_id'] = $data['role_id'] ?? 2; // کاربر عادی

            // درج در دیتابیس
            $userId = $this->db->insert('users', $data);

            // ارسال ایمیل خوش‌آمدگویی
            $this->sendWelcomeEmail($data['email'], $data['username']);

            $this->db->commit();
            
            // ثبت لاگ
            $this->logActivity($userId, 'register', 'ثبت‌نام کاربر جدید');

            return $userId;

        } catch (Exception $e) {
            $this->db->rollback();
            $this->logError('خطا در ثبت‌نام: ' . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * بروزرسانی پروفایل کاربر
     * 
     * @param int $userId شناسه کاربر
     * @param array $data اطلاعات جدید
     * @return bool
     */
    public function updateProfile($userId, $data) {
        try {
            $this->db->beginTransaction();

            // بررسی مجاز بودن تغییرات
            if (!$this->canUpdateProfile($userId)) {
                throw new Exception('شما مجاز به تغییر این پروفایل نیستید.');
            }

            // بررسی تکراری بودن ایمیل
            if (isset($data['email']) && $this->emailExists($data['email'], $userId)) {
                throw new Exception('این ایمیل قبلاً ثبت شده است.');
            }

            // بروزرسانی رمز عبور
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_ARGON2ID, HASH_OPTIONS);
            }

            // بروزرسانی در دیتابیس
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update('users', $data, 'id = ?', [$userId]);

            $this->db->commit();

            // ثبت لاگ
            $this->logActivity($userId, 'update_profile', 'بروزرسانی پروفایل');

            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            $this->logError('خطا در بروزرسانی پروفایل: ' . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * تغییر رمز عبور
     * 
     * @param int $userId شناسه کاربر
     * @param string $currentPassword رمز عبور فعلی
     * @param string $newPassword رمز عبور جدید
     * @return bool
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // بررسی رمز عبور فعلی
            $query = "SELECT password FROM users WHERE id = ? LIMIT 1";
            $user = $this->db->getRow($query, [$userId]);

            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('رمز عبور فعلی اشتباه است.');
            }

            // بررسی تکراری نبودن رمز عبور
            if (password_verify($newPassword, $user['password'])) {
                throw new Exception('رمز عبور جدید نمی‌تواند تکراری باشد.');
            }

            // بررسی پیچیدگی رمز عبور
            if (!$this->isPasswordStrong($newPassword)) {
                throw new Exception('رمز عبور جدید به اندازه کافی قوی نیست.');
            }

            // بروزرسانی رمز عبور
            $data = [
                'password' => password_hash($newPassword, PASSWORD_ARGON2ID, HASH_OPTIONS),
                'password_changed_at' => date('Y-m-d H:i:s')
            ];

            $this->db->update('users', $data, 'id = ?', [$userId]);

            // ثبت لاگ
            $this->logActivity($userId, 'change_password', 'تغییر رمز عبور');

            return true;

        } catch (Exception $e) {
            $this->logError('خطا در تغییر رمز عبور: ' . $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        }
    }

    /**
     * بازیابی رمز عبور
     * 
     * @param string $email ایمیل کاربر
     * @return bool
     */
    public function resetPassword($email) {
        try {
            // یافتن کاربر
            $query = "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1";
            $user = $this->db->getRow($query, [$email]);

            if (!$user) {
                throw new Exception('کاربری با این ایمیل یافت نشد.');
            }

            // تولید توکن
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // ذخیره توکن
            $data = [
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => $expiry,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('password_resets', $data);

            // ارسال ایمیل بازیابی
            $this->sendPasswordResetEmail($email, $token);

            // ثبت لاگ
            $this->logActivity($user['id'], 'reset_password_request', 'درخواست بازیابی رمز عبور');

            return true;

        } catch (Exception $e) {
            $this->logError('خطا در بازیابی رمز عبور: ' . $e->getMessage(), ['email' => $email]);
            throw $e;
        }
    }

    /**
     * اعتبارسنجی توکن بازیابی رمز عبور
     * 
     * @param string $token توکن
     * @return array|false
     */
    public function validateResetToken($token) {
        $query = "SELECT * FROM password_resets 
                 WHERE token = ? 
                 AND used = 0 
                 AND expires_at > NOW() 
                 LIMIT 1";

        return $this->db->getRow($query, [$token]);
    }

    /**
     * بررسی قفل بودن حساب کاربری
     * 
     * @param string $username نام کاربری
     * @return bool
     */
    private function isUserLocked($username) {
        if (!isset($this->loginAttempts[$username])) {
            return false;
        }

        $attempts = $this->loginAttempts[$username];

        if ($attempts['count'] >= LOGIN_MAX_ATTEMPTS) {
            $lockoutTime = $attempts['time'] + (LOGIN_LOCKOUT_TIME * 60);
            if (time() < $lockoutTime) {
                return true;
            }
            
            // پاک کردن تلاش‌های قبلی بعد از اتمام زمان قفل
            $this->clearLoginAttempts($username);
        }

        return false;
    }

    /**
     * افزایش تعداد تلاش‌های ناموفق
     * 
     * @param string $username نام کاربری
     */
    private function incrementLoginAttempts($username) {
        if (!isset($this->loginAttempts[$username])) {
            $this->loginAttempts[$username] = [
                'count' => 0,
                'time' => time()
            ];
        }

        $this->loginAttempts[$username]['count']++;
        $this->loginAttempts[$username]['time'] = time();
    }

    /**
     * پاک کردن تلاش‌های ناموفق
     * 
     * @param string $username نام کاربری
     */
    private function clearLoginAttempts($username) {
        unset($this->loginAttempts[$username]);
    }

    /**
     * بررسی منقضی شدن رمز عبور
     * 
     * @param array $user اطلاعات کاربر
     * @return bool
     */
    private function isPasswordExpired($user) {
        if (!$user['password_changed_at']) {
            return false;
        }

        $expiry = strtotime($user['password_changed_at'] . ' +90 days');
        return time() > $expiry;
    }

    /**
     * تنظیم توکن "مرا به خاطر بسپار"
     * 
     * @param int $userId شناسه کاربر
     */
    private function setRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

        $data = [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiry,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('remember_tokens', $data);

        setcookie(
            'remember_token',
            $token,
            strtotime('+30 days'),
            '/',
            '',
            IS_HTTPS,
            true
        );
    }

    /**
     * حذف توکن "مرا به خاطر بسپار"
     * 
     * @param string $token توکن
     */
    private function deleteRememberToken($token) {
        $this->db->delete('remember_tokens', 'token = ?', [$token]);
    }

    /**
     * بروزرسانی آخرین ورود
     * 
     * @param int $userId شناسه کاربر
     */
    private function updateLastLogin($userId) {
        $data = [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR']
        ];

        $this->db->update('users', $data, 'id = ?', [$userId]);
    }

    /**
     * بررسی تکراری بودن نام کاربری
     * 
     * @param string $username نام کاربری
     * @return bool
     */
    private function usernameExists($username) {
        $query = "SELECT COUNT(*) FROM users WHERE username = ?";
        return (bool) $this->db->getValue($query, [$username]);
    }

    /**
     * بررسی تکراری بودن ایمیل
     * 
     * @param string $email ایمیل
     * @param int|null $excludeId شناسه کاربر فعلی
     * @return bool
     */
    private function emailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (bool) $this->db->getValue($query, $params);
    }

    /**
     * بررسی مجاز بودن تغییر پروفایل
     * 
     * @param int $userId شناسه کاربر
     * @return bool
     */
    private function canUpdateProfile($userId) {
        return $this->isLoggedIn() && 
               ($this->currentUser['id'] === $userId || 
                $this->hasPermission('manage_users'));
    }

    /**
     * بررسی قوی بودن رمز عبور
     * 
     * @param string $password رمز عبور
     * @return bool
     */
    private function isPasswordStrong($password) {
        return strlen($password) >= PASSWORD_MIN_LENGTH &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }

    /**
     * ارسال ایمیل خوش‌آمدگویی
     * 
     * @param string $email ایمیل کاربر
     * @param string $username نام کاربری
     */
    private function sendWelcomeEmail($email, $username) {
        $subject = 'خوش آمدید به ' . APP_NAME;
        $message = sprintf(
            'سلام %s،\n\nبه %s خوش آمدید. امیدواریم تجربه خوبی داشته باشید.\n\nبا تشکر',
            $username,
            APP_NAME
        );

        mail($email, $subject, $message);
    }

    /**
     * ارسال ایمیل بازیابی رمز عبور
     * 
     * @param string $email ایمیل کاربر
     * @param string $token توکن بازیابی
     */
    private function sendPasswordResetEmail($email, $token) {
        $resetLink = sprintf('%s/reset-password?token=%s', BASE_URL, $token);
        
        $subject = 'بازیابی رمز عبور';
        $message = sprintf(
            'برای بازیابی رمز عبور خود روی لینک زیر کلیک کنید:\n\n%s\n\n' .
            'این لینک تا یک ساعت معتبر است.\n\n' .
            'اگر شما درخواست بازیابی رمز عبور نداده‌اید، این ایمیل را نادیده بگیرید.',
            $resetLink
        );

        mail($email, $subject, $message);
    }

    /**
     * ثبت لاگ فعالیت
     * 
     * @param int $userId شناسه کاربر
     * @param string $action عملیات
     * @param string $description توضیحات
     */
    private function logActivity($userId, $action, $description) {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('activity_logs', $data);
    }

    /**
     * ثبت لاگ خطا
     * 
     * @param string $message پیام خطا
     * @param array $context اطلاعات اضافی
     */
    private function logError($message, $context = []) {
        $log = sprintf(
            "[%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        error_log($log, 3, LOGS_PATH . '/auth/errors.log');
    }
}