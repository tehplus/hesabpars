<?php
/**
 * کلاس مدیریت دیتابیس
 * 
 * Current Date: 2025-05-01 16:12:24
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Database
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

class Database {
    /**
     * @var PDO نمونه اتصال به دیتابیس
     */
    private $connection;

    /**
     * @var PDOStatement آخرین کوئری اجرا شده
     */
    private $statement;

    /**
     * @var array آرایه‌ای از تنظیمات اتصال
     */
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_persian_ci"
    ];

    /**
     * @var array آرایه‌ای از کوئری‌های کش شده
     */
    private $queryCache = [];

    /**
     * @var array آرایه‌ای از تراکنش‌های فعال
     */
    private $transactions = [];

    /**
     * @var int تعداد کوئری‌های اجرا شده
     */
    private $queryCount = 0;

    /**
     * @var float زمان کل اجرای کوئری‌ها
     */
    private $queryTime = 0;

    /**
     * سازنده کلاس
     * 
     * @param string $host هاست دیتابیس
     * @param string $dbname نام دیتابیس
     * @param string $username نام کاربری
     * @param string $password رمز عبور
     * @throws PDOException
     */
    public function __construct($host, $dbname, $username, $password) {
        try {
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $username, $password, $this->options);
            
            // غیرفعال کردن auto-commit
            $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            
        } catch (PDOException $e) {
            $this->logError('خطا در اتصال به دیتابیس: ' . $e->getMessage());
            throw new PDOException('خطا در اتصال به دیتابیس');
        }
    }

    /**
     * اجرای یک کوئری با پارامترهای امن
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترهای کوئری
     * @return PDOStatement
     * @throws PDOException
     */
    public function query($query, $params = []) {
        $start = microtime(true);
        
        try {
            $this->statement = $this->connection->prepare($query);
            $this->statement->execute($params);
            
            $this->queryCount++;
            $this->queryTime += microtime(true) - $start;
            
            if (DEBUG_MODE) {
                $this->logQuery($query, $params, $this->queryTime);
            }
            
            return $this->statement;
            
        } catch (PDOException $e) {
            $this->logError('خطا در اجرای کوئری: ' . $e->getMessage(), [
                'query' => $query,
                'params' => $params
            ]);
            throw new PDOException('خطا در اجرای کوئری');
        }
    }

    /**
     * دریافت یک رکورد
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترهای کوئری
     * @return array|false
     */
    public function getRow($query, $params = []) {
        return $this->query($query, $params)->fetch();
    }

    /**
     * دریافت همه رکوردها
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترهای کوئری
     * @return array
     */
    public function getRows($query, $params = []) {
        return $this->query($query, $params)->fetchAll();
    }

    /**
     * دریافت یک مقدار
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترهای کوئری
     * @return mixed
     */
    public function getValue($query, $params = []) {
        return $this->query($query, $params)->fetchColumn();
    }

    /**
     * درج یک رکورد
     * 
     * @param string $table نام جدول
     * @param array $data داده‌های رکورد
     * @return int|false
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_fill(0, count($fields), '?');
        
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            implode(', ', $values)
        );
        
        $this->query($query, array_values($data));
        return $this->connection->lastInsertId();
    }

    /**
     * بروزرسانی رکورد
     * 
     * @param string $table نام جدول
     * @param array $data داده‌های جدید
     * @param string $where شرط بروزرسانی
     * @param array $params پارامترهای شرط
     * @return int تعداد رکوردهای تغییر یافته
     */
    public function update($table, $data, $where, $params = []) {
        $sets = array_map(function($field) {
            return "{$field} = ?";
        }, array_keys($data));
        
        $query = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $sets),
            $where
        );
        
        $params = array_merge(array_values($data), $params);
        $this->query($query, $params);
        
        return $this->statement->rowCount();
    }

    /**
     * حذف رکورد
     * 
     * @param string $table نام جدول
     * @param string $where شرط حذف
     * @param array $params پارامترهای شرط
     * @return int تعداد رکوردهای حذف شده
     */
    public function delete($table, $where, $params = []) {
        $query = sprintf("DELETE FROM %s WHERE %s", $table, $where);
        $this->query($query, $params);
        return $this->statement->rowCount();
    }

    /**
     * شروع تراکنش
     * 
     * @return bool
     */
    public function beginTransaction() {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            $this->logError('خطا در شروع تراکنش: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * تایید تراکنش
     * 
     * @return bool
     */
    public function commit() {
        try {
            return $this->connection->commit();
        } catch (PDOException $e) {
            $this->logError('خطا در تایید تراکنش: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * برگشت تراکنش
     * 
     * @return bool
     */
    public function rollback() {
        try {
            return $this->connection->rollBack();
        } catch (PDOException $e) {
            $this->logError('خطا در برگشت تراکنش: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * اجرای کوئری با کش
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترهای کوئری
     * @param int $ttl زمان نگهداری کش
     * @return mixed
     */
    public function getCached($query, $params = [], $ttl = 3600) {
        $key = md5($query . serialize($params));
        
        if (isset($this->queryCache[$key]) && $this->queryCache[$key]['expires'] > time()) {
            return $this->queryCache[$key]['data'];
        }
        
        $result = $this->getRows($query, $params);
        
        $this->queryCache[$key] = [
            'data' => $result,
            'expires' => time() + $ttl
        ];
        
        return $result;
    }

    /**
     * پاک کردن کش کوئری
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترهای کوئری
     * @return void
     */
    public function clearCache($query = null, $params = []) {
        if ($query === null) {
            $this->queryCache = [];
            return;
        }
        
        $key = md5($query . serialize($params));
        unset($this->queryCache[$key]);
    }

    /**
     * escape کردن مقدار برای استفاده در کوئری
     * 
     * @param mixed $value مقدار ورودی
     * @return string
     */
    public function escape($value) {
        return $this->connection->quote($value);
    }

    /**
     * دریافت تعداد کوئری‌های اجرا شده
     * 
     * @return int
     */
    public function getQueryCount() {
        return $this->queryCount;
    }

    /**
     * دریافت زمان کل اجرای کوئری‌ها
     * 
     * @return float
     */
    public function getQueryTime() {
        return $this->queryTime;
    }

    /**
     * ثبت لاگ کوئری
     * 
     * @param string $query کوئری SQL
     * @param array $params پارامترها
     * @param float $time زمان اجرا
     * @return void
     */
    private function logQuery($query, $params, $time) {
        $log = sprintf(
            "[%s] Query: %s; Params: %s; Time: %.4f\n",
            date('Y-m-d H:i:s'),
            $query,
            json_encode($params),
            $time
        );
        
        error_log($log, 3, LOGS_PATH . '/sql/queries.log');
    }

    /**
     * ثبت لاگ خطا
     * 
     * @param string $message پیام خطا
     * @param array $context اطلاعات اضافی
     * @return void
     */
    private function logError($message, $context = []) {
        $log = sprintf(
            "[%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        error_log($log, 3, LOGS_PATH . '/sql/errors.log');
    }

    /**
     * بستن اتصال دیتابیس
     * 
     * @return void
     */
    public function __destruct() {
        $this->connection = null;
        $this->statement = null;
    }

    /**
     * جلوگیری از کپی شدن شیء
     */
    private function __clone() {}

    /**
     * جلوگیری از unserialize شدن شیء
     */
    public function __wakeup() {}
}