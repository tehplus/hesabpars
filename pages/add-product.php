<?php
/**
 * Add Product Page
 * 
 * این صفحه برای افزودن محصول جدید به سیستم استفاده می‌شود
 * شامل فرم کامل با تمام فیلدهای مورد نیاز و امکانات پیشرفته
 * 
 * Current Date: 2025-05-01 15:27:54
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Products
 * @category Pages
 * @version 1.0.0
 */

// تنظیمات اولیه و لود کردن فایل‌های مورد نیاز
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/jdf.php';

// بررسی لاگین بودن کاربر
if (!isLoggedIn()) {
    redirect('login.php');
}

// کلاس مدیریت محصولات
class ProductManager {
    private $db;
    private $errors = [];
    private $success = [];
    private $categories = [];
    private $units = [];
    private $taxTypes = [];
    private $currencies = [];

    // تنظیمات آپلود تصویر
    private const UPLOAD_MAX_SIZE = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_FILES = 5;

    /**
     * سازنده کلاس
     */
    public function __construct() {
        try {
            global $db;
            $this->db = $db;
            $this->loadInitialData();
        } catch (Exception $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            die('خطا در اتصال به پایگاه داده');
        }
    }

    /**
     * بارگذاری داده‌های اولیه
     */
    private function loadInitialData() {
        try {
            // دریافت دسته‌بندی‌ها
            $stmt = $this->db->query("
                SELECT id, name, parent_id, icon 
                FROM categories 
                WHERE status = 'active' 
                ORDER BY sort_order
            ");
            $this->categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // دریافت واحدها
            $stmt = $this->db->query("
                SELECT id, name, symbol 
                FROM units 
                WHERE active = 1 
                ORDER BY name
            ");
            $this->units = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // دریافت انواع مالیات
            $stmt = $this->db->query("
                SELECT id, name, rate 
                FROM tax_types 
                WHERE active = 1 
                ORDER BY name
            ");
            $this->taxTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // دریافت ارزها
            $stmt = $this->db->query("
                SELECT id, code, name, symbol 
                FROM currencies 
                WHERE active = 1 
                ORDER BY name
            ");
            $this->currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $this->logError('Error loading initial data: ' . $e->getMessage());
            $this->errors[] = 'خطا در بارگذاری اطلاعات';
        }
    }

    /**
     * پردازش درخواست POST
     */
    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // بررسی توکن CSRF
                if (!$this->validateCSRFToken()) {
                    throw new Exception('توکن امنیتی نامعتبر است');
                }

                // اعتبارسنجی داده‌های ورودی
                $this->validateInput();

                // اگر خطایی وجود نداشت، محصول را ذخیره کن
                if (empty($this->errors)) {
                    $this->saveProduct();
                }

            } catch (Exception $e) {
                $this->logError('Error processing form: ' . $e->getMessage());
                $this->errors[] = $e->getMessage();
            }
        }
    }

    /**
     * اعتبارسنجی توکن CSRF
     */
    private function validateCSRFToken() {
        return (
            isset($_POST['csrf_token']) && 
            isset($_SESSION['csrf_token']) && 
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        );
    }

    /**
     * اعتبارسنجی داده‌های ورودی
     */
    private function validateInput() {
        // بررسی فیلدهای الزامی
        $required = ['name', 'code', 'category_id', 'main_unit'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $this->errors[] = "فیلد $field اجباری است";
            }
        }

        // اعتبارسنجی کد محصول
        if (!preg_match('/^[A-Za-z0-9\-\_]{3,50}$/', $_POST['code'])) {
            $this->errors[] = 'کد محصول باید شامل حروف، اعداد، خط تیره یا زیرخط باشد';
        }

        // بررسی یکتا بودن کد محصول
        $stmt = $this->db->prepare("SELECT id FROM products WHERE code = ?");
        $stmt->execute([$_POST['code']]);
        if ($stmt->rowCount() > 0) {
            $this->errors[] = 'این کد محصول قبلاً ثبت شده است';
        }

        // اعتبارسنجی قیمت‌ها
        if (!empty($_POST['selling_price']) && !is_numeric($_POST['selling_price'])) {
            $this->errors[] = 'قیمت فروش باید عددی باشد';
        }

        if (!empty($_POST['purchase_price']) && !is_numeric($_POST['purchase_price'])) {
            $this->errors[] = 'قیمت خرید باید عددی باشد';
        }

        // اعتبارسنجی تصاویر
        if (!empty($_FILES['images']['name'][0])) {
            $this->validateImages();
        }
    }

    /**
     * اعتبارسنجی تصاویر آپلود شده
     */
    private function validateImages() {
        if (count($_FILES['images']['name']) > self::MAX_FILES) {
            $this->errors[] = 'حداکثر تعداد تصاویر مجاز ' . self::MAX_FILES . ' عدد است';
            return;
        }

        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === 0) {
                // بررسی نوع فایل
                if (!in_array($_FILES['images']['type'][$key], self::ALLOWED_TYPES)) {
                    $this->errors[] = "فرمت فایل $name پشتیبانی نمی‌شود";
                }

                // بررسی حجم فایل
                if ($_FILES['images']['size'][$key] > self::UPLOAD_MAX_SIZE) {
                    $this->errors[] = "حجم فایل $name بیشتر از حد مجاز است";
                }

                // بررسی اعتبار تصویر
                if (!getimagesize($_FILES['images']['tmp_name'][$key])) {
                    $this->errors[] = "فایل $name یک تصویر معتبر نیست";
                }
            }
        }
    }

    /**
     * ذخیره محصول
     */
    private function saveProduct() {
        try {
            $this->db->beginTransaction();

            // آماده‌سازی داده‌ها
            $data = [
                'name' => $_POST['name'],
                'code' => $_POST['code'],
                'accounting_code' => $_POST['accounting_code'] ?? null,
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'] ?? null,
                'selling_price' => $_POST['selling_price'] ?? 0,
                'purchase_price' => $_POST['purchase_price'] ?? 0,
                'sales_description' => $_POST['sales_description'] ?? null,
                'purchase_description' => $_POST['purchase_description'] ?? null,
                'main_unit' => $_POST['main_unit'],
                'sub_unit' => $_POST['sub_unit'] ?? null,
                'unit_description' => $_POST['unit_description'] ?? null,
                'stock_control' => isset($_POST['stock_control']) ? 1 : 0,
                'current_stock' => $_POST['current_stock'] ?? 0,
                'min_stock' => $_POST['min_stock'] ?? 0,
                'lead_time' => $_POST['lead_time'] ?? 0,
                'sales_tax_enabled' => isset($_POST['sales_tax_enabled']) ? 1 : 0,
                'sales_tax_rate' => $_POST['sales_tax_rate'] ?? 0,
                'purchase_tax_enabled' => isset($_POST['purchase_tax_enabled']) ? 1 : 0,
                'purchase_tax_rate' => $_POST['purchase_tax_rate'] ?? 0,
                'tax_type_id' => $_POST['tax_type'] ?? null,
                'tax_unit_id' => $_POST['tax_unit'] ?? null,
                'tax_code' => $_POST['tax_code'] ?? null,
                'created_by' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            // درج محصول
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO products ($columns) VALUES ($values)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            $product_id = $this->db->lastInsertId();

            // ذخیره تصاویر
            if (!empty($_FILES['images']['name'][0])) {
                $this->saveImages($product_id);
            }

            // ذخیره تگ‌ها
            if (!empty($_POST['tags'])) {
                $this->saveTags($product_id, $_POST['tags']);
            }

            // ذخیره مشخصات فنی
            if (!empty($_POST['spec_keys'])) {
                $this->saveSpecifications($product_id, $_POST['spec_keys'], $_POST['spec_values']);
            }

            $this->db->commit();
            $this->success[] = 'محصول با موفقیت ثبت شد';

            // ریدایرکت به صفحه لیست محصولات
            $_SESSION['success'] = 'محصول با موفقیت ثبت شد';
            redirect('products.php');

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError('Error saving product: ' . $e->getMessage());
            $this->errors[] = 'خطا در ذخیره اطلاعات محصول';
        }
    }

    /**
     * ذخیره تصاویر محصول
     */
    private function saveImages($product_id) {
        $upload_path = __DIR__ . '/../uploads/products/' . $product_id;
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $filepath = $upload_path . '/' . $filename;

                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $filepath)) {
                    // ذخیره اطلاعات تصویر در دیتابیس
                    $sql = "INSERT INTO product_images (product_id, filename, sort_order) VALUES (?, ?, ?)";
                    $this->db->prepare($sql)->execute([$product_id, $filename, $key]);

                    // بهینه‌سازی تصویر
                    $this->optimizeImage($filepath);
                }
            }
        }
    }

    /**
     * بهینه‌سازی تصویر
     */
    private function optimizeImage($filepath) {
        if (extension_loaded('gd')) {
            $image_info = getimagesize($filepath);
            
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($filepath);
                    imagejpeg($image, $filepath, 85);
                    break;
                    
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($filepath);
                    imagepng($image, $filepath, 8);
                    break;
                    
                case IMAGETYPE_WEBP:
                    $image = imagecreatefromwebp($filepath);
                    imagewebp($image, $filepath, 85);
                    break;
            }

            if (isset($image)) {
                imagedestroy($image);
            }
        }
    }

    /**
     * ذخیره تگ‌ها
     */
    private function saveTags($product_id, $tags) {
        $sql = "INSERT INTO product_tags (product_id, tag) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);

        foreach ($tags as $tag) {
            $stmt->execute([$product_id, trim($tag)]);
        }
    }

    /**
     * ذخیره مشخصات فنی
     */
    private function saveSpecifications($product_id, $keys, $values) {
        $sql = "INSERT INTO product_specifications (product_id, spec_key, spec_value) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        foreach ($keys as $index => $key) {
            if (!empty($key) && !empty($values[$index])) {
                $stmt->execute([$product_id, $key, $values[$index]]);
            }
        }
    }

    /**
     * ثبت خطا در لاگ
     */
    private function logError($message) {
        $log_file = __DIR__ . '/../logs/product_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";
        error_log($log_message, 3, $log_file);
    }

    /**
     * تولید توکن CSRF
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * دریافت خطاها
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * دریافت پیام‌های موفقیت
     */
    public function getSuccess() {
        return $this->success;
    }

    /**
     * دریافت دسته‌بندی‌ها
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * دریافت واحدها
     */
    public function getUnits() {
        return $this->units;
    }

    /**
     * دریافت انواع مالیات
     */
    public function getTaxTypes() {
        return $this->taxTypes;
    }

    /**
     * دریافت ارزها
     */
    public function getCurrencies() {
        return $this->currencies;
    }
}

// ایجاد نمونه از کلاس و پردازش درخواست
$productManager = new ProductManager();
$productManager->handlePost();

// تولید توکن CSRF
$csrf_token = $productManager->generateCSRFToken();

// دریافت داده‌های مورد نیاز
$categories = $productManager->getCategories();
$units = $productManager->getUnits();
$taxTypes = $productManager->getTaxTypes();
$currencies = $productManager->getCurrencies();
$errors = $productManager->getErrors();
$success = $productManager->getSuccess();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن محصول جدید - <?php echo SITE_NAME; ?></title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.rtl.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/select2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/select2-bootstrap5-theme.rtl.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/sweetalert2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/add-product.css'); ?>">
</head>
<body class="bg-light">
    <?php include_once '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <!-- نمایش خطاها -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- نمایش پیام‌های موفقیت -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($success as $message): ?>
                            <li><?php echo htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- هدر صفحه -->
            <div class="page-header">
                <h1>افزودن محصول جدید</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-light" onclick="window.history.back()">
                        <i class="fas fa-arrow-right"></i>
                        بازگشت
                    </button>
                </div>
            </div>

            <!-- آمار سریع -->
            <div class="grid-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--primary-color);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalProducts ?? 0); ?></h3>
                        <p>کل محصولات</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--success-color);">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($activeProducts ?? 0); ?></h3>
                        <p>محصولات فعال</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--warning-color);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($lowStockProducts ?? 0); ?></h3>
                        <p>کمبود موجودی</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--info-color);">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalCategories ?? 0); ?></h3>
                        <p>دسته‌بندی‌ها</p>
                    </div>
                </div>
            </div>

            <!-- فرم افزودن محصول -->
            <form id="productForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- توکن CSRF -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- بخش مشخصات اصلی -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h2>مشخصات اصلی محصول</h2>
                    </div>
                    <div class="form-section-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">نام محصول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">
                                    لطفاً نام محصول را وارد کنید
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="code">کد محصول <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="code" name="code" required 
                                           pattern="[A-Za-z0-9\-\_]{3,50}">
                                    <button type="button" class="btn btn-outline-secondary" id="generateCode">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    کد محصول باید شامل حروف، اعداد، خط تیره یا زیرخط باشد
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">دسته‌بندی <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="category_id" name="category_id" required>
                                    <option value="">انتخاب دسته‌بندی</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    لطفاً دسته‌بندی را انتخاب کنید
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="accounting_code">کد حسابداری</label>
                                <div class="input-group">
                                    <div class="form-check form-switch me-2">
                                        <input class="form-check-input" type="checkbox" id="autoAccountingCode" checked>
                                        <label class="form-check-label">تولید خودکار</label>
                                    </div>
                                    <input type="text" class="form-control" id="accounting_code" name="accounting_code" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <label class="form-label" for="description">توضیحات</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                <small class="text-muted">
                                    می‌توانید از Markdown برای قالب‌بندی متن استفاده کنید.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- بخش تصاویر -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h2>تصاویر محصول</h2>
                    </div>
                    <div class="form-section-body">
                        <div class="upload-area" id="dropZone">
                            <input type="file" id="productImages" name="images[]" multiple accept="image/*" class="d-none">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p class="mb-2">تصاویر را اینجا رها کنید یا کلیک کنید</p>
                            <small class="text-muted">
                                حداکثر 5 تصویر با فرمت JPG، PNG یا WebP و حجم حداکثر 2 مگابایت
                            </small>
                        </div>
                        <div class="image-preview-container"></div>
                    </div>
                </div>

                <!-- بخش قیمت‌گذاری -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h2>قیمت‌گذاری</h2>
                    </div>
                    <div class="form-section-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">قیمت فروش</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="selling_price" name="selling_price" 
                                           min="0" step="0.01">
                                    <select class="form-select" name="selling_currency" style="max-width: 100px;">
                                        <?php foreach ($currencies as $currency): ?>
                                            <option value="<?php echo htmlspecialchars($currency['code']); ?>">
                                                <?php echo htmlspecialchars($currency['symbol']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">قیمت خرید</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="purchase_price" name="purchase_price" 
                                           min="0" step="0.01">
                                    <select class="form-select" name="purchase_currency" style="max-width: 100px;">
                                        <?php foreach ($currencies as $currency): ?>
                                            <option value="<?php echo htmlspecialchars($currency['code']); ?>">
                                                <?php echo htmlspecialchars($currency['symbol']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">حاشیه سود (%)</label>
                                <input type="number" class="form-control" id="profit_margin" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">قیمت نهایی با مالیات</label>
                                <input type="number" class="form-control" id="final_price" readonly>
                                                            </div>
                            <div class="col-md-4">
                                <label class="form-label">وضعیت قیمت‌گذاری</label>
                                <select class="form-select" name="price_status">
                                    <option value="fixed">ثابت</option>
                                    <option value="variable">متغیر</option>
                                    <option value="negotiable">قابل مذاکره</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- بخش موجودی و واحد -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h2>موجودی و واحد</h2>
                    </div>
                    <div class="form-section-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="main_unit">واحد اصلی <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="main_unit" name="main_unit" required>
                                    <option value="">انتخاب واحد</option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?php echo htmlspecialchars($unit['id']); ?>">
                                            <?php echo htmlspecialchars($unit['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    لطفاً واحد اصلی را انتخاب کنید
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sub_unit">واحد فرعی</label>
                                <select class="form-select select2" id="sub_unit" name="sub_unit">
                                    <option value="">انتخاب واحد</option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?php echo htmlspecialchars($unit['id']); ?>">
                                            <?php echo htmlspecialchars($unit['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="stock_control" name="stock_control">
                                    <label class="form-check-label" for="stock_control">کنترل موجودی</label>
                                </div>
                            </div>
                        </div>

                        <div class="inventory-section" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">موجودی فعلی</label>
                                    <input type="number" class="form-control" id="current_stock" name="current_stock" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">حداقل موجودی</label>
                                    <input type="number" class="form-control" id="min_stock" name="min_stock" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">زمان تحویل (روز)</label>
                                    <input type="number" class="form-control" id="lead_time" name="lead_time" min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">هشدار موجودی</label>
                                    <select class="form-select" name="stock_alert_type">
                                        <option value="none">بدون هشدار</option>
                                        <option value="email">ایمیل</option>
                                        <option value="sms">پیامک</option>
                                        <option value="notification">نوتیفیکیشن</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">وضعیت نمایش</label>
                                    <select class="form-select" name="stock_display">
                                        <option value="show">نمایش دقیق موجودی</option>
                                        <option value="status">فقط وضعیت موجودی</option>
                                        <option value="hide">عدم نمایش</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- بخش مالیات -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h2>مالیات</h2>
                    </div>
                    <div class="form-section-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="tax-section">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sales_tax_enabled" 
                                               name="sales_tax_enabled" checked>
                                        <label class="form-check-label" for="sales_tax_enabled">مالیات فروش</label>
                                    </div>
                                    <div class="tax-details mt-3">
                                        <label class="form-label">نرخ مالیات (%)</label>
                                        <input type="number" class="form-control" id="sales_tax_rate" 
                                               name="sales_tax_rate" value="9" min="0" max="100" step="0.01">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tax-section">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="purchase_tax_enabled" 
                                               name="purchase_tax_enabled" checked>
                                        <label class="form-check-label" for="purchase_tax_enabled">مالیات خرید</label>
                                    </div>
                                    <div class="tax-details mt-3">
                                        <label class="form-label">نرخ مالیات (%)</label>
                                        <input type="number" class="form-control" id="purchase_tax_rate" 
                                               name="purchase_tax_rate" value="9" min="0" max="100" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">نوع مالیات</label>
                                <select class="form-select select2" id="tax_type" name="tax_type">
                                    <option value="">انتخاب کنید</option>
                                    <?php foreach ($taxTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type['id']); ?>">
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">کد مالیاتی</label>
                                <input type="text" class="form-control" id="tax_code" name="tax_code">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">وضعیت معافیت</label>
                                <select class="form-select" name="tax_exemption">
                                    <option value="none">بدون معافیت</option>
                                    <option value="partial">معافیت جزئی</option>
                                    <option value="full">معافیت کامل</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- بخش مشخصات تکمیلی -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h2>مشخصات تکمیلی</h2>
                    </div>
                    <div class="form-section-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">تگ‌ها</label>
                                <div class="tag-container">
                                    <input type="text" class="form-control border-0" id="tagInput" 
                                           placeholder="تگ را وارد کنید و Enter بزنید">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">مشخصات فنی</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered specifications-table">
                                        <thead>
                                            <tr>
                                                <th>عنوان</th>
                                                <th>مقدار</th>
                                                <th style="width: 50px;">
                                                    <button type="button" class="btn btn-sm btn-primary" id="addSpecification">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">وضعیت SEO</label>
                                <select class="form-select" name="seo_status">
                                    <option value="default">پیش‌فرض</option>
                                    <option value="custom">سفارشی</option>
                                    <option value="disabled">غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">برچسب محصول</label>
                                <select class="form-select" name="product_label">
                                    <option value="">بدون برچسب</option>
                                    <option value="new">جدید</option>
                                    <option value="sale">حراج</option>
                                    <option value="special">ویژه</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- دکمه‌های عملیات -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        ذخیره محصول
                    </button>
                    <button type="button" class="btn btn-success me-2" id="saveDraft">
                        <i class="fas fa-save"></i>
                        ذخیره پیش‌نویس
                    </button>
                    <a href="<?php echo BASE_URL; ?>/pages/products.php" class="btn btn-secondary me-2">
                        <i class="fas fa-times"></i>
                        انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo asset('js/jquery.min.js'); ?>"></script>
    <script src="<?php echo asset('js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo asset('js/select2.min.js'); ?>"></script>
    <script src="<?php echo asset('js/sweetalert2.all.min.js'); ?>"></script>
    <script src="<?php echo asset('js/marked.min.js'); ?>"></script>
    <script src="<?php echo asset('js/add-product.js'); ?>"></script>
</body>
</html>
                