<?php
/**
 * کلاس مدیریت کاربران و احراز هویت
 * 
 * این کلاس مسئول:
 * - ثبت‌نام کاربران
 * - ورود و خروج
 * - مدیریت نشست‌ها
 * - بررسی دسترسی‌ها
 * 
 * Current Date: 2025-05-01 16:50:43
 * Current User: tehplus
 */

// جلوگیری از دسترسی مستقیم
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

class Auth {
    // دیتابیس
    private $db;
    
    // کاربر جاری
    private $currentUser = null;
    
    // پیام‌های خطا
    private $errors = [];

    /**
     * سازنده کلاس
     * 
     * @param Database $db
     */
    public function __construct($db) {
        $this->db = $db;
        $this->startSession();
        $this->loadCurrentUser();
    }

    /**
     * راه‌اندازی session
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    /**
     * بارگذاری کاربر جاری
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
     * ثبت‌نام کاربر جدید
     * 
     * @param array $data اطلاعات کاربر
     * @return bool|int
     */
    public function register($data) {
        // بررسی داده‌های ورودی
        if (empty($data['email']) || empty($data['password'])) {
            $this->errors[] = 'ایمیل و رمز عبور الزامی است';
            return false;
        }

        // بررسی فرمت ایمیل
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'ایمیل نامعتبر است';
            return false;
        }

        // بررسی طول رمز عبور
        if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            $this->errors[] = 'رمز عبور باید حداقل ' . PASSWORD_MIN_LENGTH . ' کاراکتر باشد';
            return false;
        }

        // بررسی تکراری بودن ایمیل
        $query = "SELECT COUNT(*) FROM users WHERE email = ?";
        if ($this->db->getRow($query, [$data['email']])) {
            $this->errors[] = 'این ایمیل قبلاً ثبت شده است';
            return false;
        }

        try {
            $this->db->beginTransaction();

            // آماده‌سازی داده‌ها
            $userData = [
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'name' => $data['name'] ?? '',
                'status' => 'active',
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // درج کاربر
            $userId = $this->db->insert('users', $userData);

            $this->db->commit();
            return $userId;

        } catch (Exception $e) {
            $this->db->rollback();
            $this->errors[] = 'خطا در ثبت‌نام: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * ورود کاربر
     * 
     * @param string $email ایمیل
     * @param string $password رمز عبور
     * @return bool
     */
    public function login($email, $password) {
        // بررسی داده‌های ورودی
        if (empty($email) || empty($password)) {
            $this->errors[] = 'ایمیل و رمز عبور الزامی است';
            return false;
        }

        // دریافت اطلاعات کاربر
        $query = "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1";
        $user = $this->db->getRow($query, [$email]);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->errors[] = 'ایمیل یا رمز عبور اشتباه است';
            return false;
        }

        // ذخیره در session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_login'] = time();

        // بروزرسانی آخرین ورود
        $this->db->update('users', 
            ['last_login' => date('Y-m-d H:i:s')],
            'id = ?',
            [$user['id']]
        );

        $this->currentUser = $user;
        return true;
    }

    /**
     * خروج کاربر
     */
    public function logout() {
        // پاک کردن متغیرهای session
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        unset($_SESSION['last_login']);

        // پاک کردن اطلاعات کاربر
        $this->currentUser = null;

        // نابود کردن session
        session_destroy();
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
     * بررسی نقش کاربر
     * 
     * @param string $role نقش مورد نظر
     * @return bool
     */
    public function hasRole($role) {
        return $this->isLoggedIn() && $this->currentUser['role'] === $role;
    }

    /**
     * بررسی دسترسی به صفحه
     * 
     * @param string $page صفحه مورد نظر
     * @return bool
     */
    public function hasAccess($page) {
        // اگر کاربر لاگین نیست
        if (!$this->isLoggedIn()) {
            return false;
        }

        // اگر کاربر ادمین است
        if ($this->hasRole('admin')) {
            return true;
        }

        // دسترسی‌های پیش‌فرض
        $defaultAccess = [
            'dashboard' => ['user', 'admin'],
            'profile' => ['user', 'admin'],
            'reports' => ['user', 'admin']
        ];

        // بررسی دسترسی
        return isset($defaultAccess[$page]) && 
               in_array($this->currentUser['role'], $defaultAccess[$page]);
    }

    /**
     * بروزرسانی پروفایل
     * 
     * @param array $data اطلاعات جدید
     * @return bool
     */
    public function updateProfile($data) {
        if (!$this->isLoggedIn()) {
            $this->errors[] = 'ابتدا وارد شوید';
            return false;
        }

        try {
            $this->db->beginTransaction();

            $updateData = [];

            // بروزرسانی نام
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            // بروزرسانی رمز عبور
            if (!empty($data['password'])) {
                if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                    $this->errors[] = 'رمز عبور باید حداقل ' . PASSWORD_MIN_LENGTH . ' کاراکتر باشد';
                    return false;
                }
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');

                $this->db->update('users', 
                    $updateData,
                    'id = ?',
                    [$this->currentUser['id']]
                );

                // بروزرسانی اطلاعات کاربر
                $this->loadCurrentUser();
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            $this->errors[] = 'خطا در بروزرسانی پروفایل: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * دریافت خطاها
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * پاک کردن خطاها
     */
    public function clearErrors() {
        $this->errors = [];
    }
}