<?php
/**
 * File: db.php
 * Description: کلاس مدیریت دیتابیس با الگوی Singleton
 * 
 * @package HesabPars
 * @author TehPlus
 * @version 1.0.0
 */

class Database {
    /**
     * @var PDO|null نمونه PDO
     */
    private static $instance = null;

    /**
     * @var PDO اتصال به دیتابیس
     */
    private $connection;

    /**
     * @var array آخرین خطای رخ داده
     */
    private $error;

    /**
     * @var PDOStatement|false آخرین کوئری اجرا شده
     */
    private $statement;

    /**
     * @var bool وضعیت تراکنش
     */
    private $inTransaction = false;

    /**
     * سازنده خصوصی برای الگوی Singleton
     */
    
    private function __construct() {
        try {
            // بررسی تعریف شدن ثابت‌های مورد نیاز
            if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
                throw new Exception('Database configuration constants are not defined');
            }

            // تنظیمات PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_persian_ci"
            ];

            // ایجاد اتصال PDO
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                $options
            );

            // تنظیم timezone برای دیتابیس
            $this->query("SET time_zone = '+03:30'");

        } catch (PDOException $e) {
            $this->error = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];

            // لاگ کردن خطا
            $error_message = date('Y-m-d H:i:s') . " Database Error: " . $e->getMessage() . "\n";
            error_log($error_message, 3, LOG_PATH . '/database.log');

            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * جلوگیری از clone شدن برای Singleton
     */
    private function __clone() {}

    /**
     * دریافت نمونه یکتای کلاس
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * دریافت اتصال PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * اجرای یک کوئری
     * 
     * @param string $sql کوئری SQL
     * @param array $params پارامترها
     * @return PDOStatement|false
     */
    public function query($sql, $params = []) {
        try {
            $this->statement = $this->connection->prepare($sql);
            $this->statement->execute($params);
            return $this->statement;
        } catch (PDOException $e) {
            $this->error = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql' => $sql,
                'params' => $params
            ];
            
            error_log(date('Y-m-d H:i:s') . " Query Error: " . $e->getMessage() . 
                     "\nSQL: " . $sql . 
                     "\nParams: " . print_r($params, true) . "\n", 
                     3, LOG_PATH . '/database.log');
            
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }

    /**
     * دریافت یک ردیف
     * 
     * @param string $sql کوئری SQL
     * @param array $params پارامترها
     * @return array|false
     */
    public function getRow($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " GetRow Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * دریافت همه ردیف‌ها
     * 
     * @param string $sql کوئری SQL
     * @param array $params پارامترها
     * @return array
     */
    public function getRows($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " GetRows Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * دریافت یک مقدار
     * 
     * @param string $sql کوئری SQL
     * @param array $params پارامترها
     * @return mixed
     */
    public function getValue($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " GetValue Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * درج یک رکورد
     * 
     * @param string $table نام جدول
     * @param array $data داده‌ها
     * @return int|false
     */
    public function insert($table, $data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($fields), '?');

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $table,
                implode(', ', $fields),
                implode(', ', $placeholders)
            );

            $this->query($sql, $values);
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " Insert Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * بروزرسانی رکورد
     * 
     * @param string $table نام جدول
     * @param array $data داده‌ها
     * @param string $where شرط
     * @param array $params پارامترهای شرط
     * @return int
     */
    public function update($table, $data, $where, $params = []) {
        try {
            $set = [];
            foreach ($data as $field => $value) {
                $set[] = "$field = ?";
            }

            $sql = sprintf(
                "UPDATE %s SET %s WHERE %s",
                $table,
                implode(', ', $set),
                $where
            );

            $stmt = $this->query($sql, array_merge(array_values($data), $params));
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " Update Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * حذف رکورد
     * 
     * @param string $table نام جدول
     * @param string $where شرط
     * @param array $params پارامترها
     * @return int
     */
    public function delete($table, $where, $params = []) {
        try {
            $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " Delete Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * شروع تراکنش
     * 
     * @return bool
     */
    public function beginTransaction() {
        try {
            if (!$this->inTransaction) {
                $this->inTransaction = $this->connection->beginTransaction();
            }
            return $this->inTransaction;
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " Transaction Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * تایید تراکنش
     * 
     * @return bool
     */
    public function commit() {
        try {
            if ($this->inTransaction) {
                $this->inTransaction = false;
                return $this->connection->commit();
            }
            return false;
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " Commit Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * برگشت تراکنش
     * 
     * @return bool
     */
    public function rollback() {
        try {
            if ($this->inTransaction) {
                $this->inTransaction = false;
                return $this->connection->rollBack();
            }
            return false;
        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " Rollback Error: " . $e->getMessage() . "\n", 
                     3, LOG_PATH . '/database.log');
            throw $e;
        }
    }

    /**
     * escape کردن مقادیر
     * 
     * @param string $value مقدار
     * @return string
     */
    public function escape($value) {
        return $this->connection->quote($value);
    }

    /**
     * دریافت آخرین خطا
     * 
     * @return array|null
     */
    public function getError() {
        return $this->error;
    }

    /**
     * تعداد رکوردهای تحت تاثیر
     * 
     * @return int
     */
    public function rowCount() {
        return $this->statement ? $this->statement->rowCount() : 0;
    }

    /**
     * دریافت آخرین ID درج شده
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// ایجاد نمونه از کلاس Database و ذخیره در متغیر سراسری
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    // لاگ کردن خطا
    error_log(date('Y-m-d H:i:s') . " Fatal Database Error: " . $e->getMessage() . "\n", 
             3, LOG_PATH . '/database.log');
    
    // نمایش خطای مناسب
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        die("Database Error: " . $e->getMessage());
    } else {
        die("خطا در اتصال به پایگاه داده. لطفاً با پشتیبانی تماس بگیرید.");
    }
}
