<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// بررسی دسترسی کاربر
if (!isLoggedIn()) {
    $_SESSION['error'] = 'لطفاً ابتدا وارد حساب کاربری خود شوید.';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// تنظیمات نمایش خطاها
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // دریافت تنظیمات مالیات پیش‌فرض
    $defaultTaxRate = 9; // نرخ پیش‌فرض مالیات

    // بررسی اتصال به دیتابیس
    if (!isset($db) || !($db instanceof PDO)) {
        throw new Exception('خطا در اتصال به پایگاه داده');
    }

    // دریافت لیست دسته‌بندی‌ها
    $categoryQuery = "SELECT id, name, parent_id FROM categories WHERE status = 'active' ORDER BY name";
    $stmt = $db->prepare($categoryQuery);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // دریافت لیست ارزها
    $currencyQuery = "SELECT id, code, name, symbol FROM currencies WHERE active = 1 ORDER BY name";
    $stmt = $db->prepare($currencyQuery);
    $stmt->execute();
    $currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // دریافت انواع مالیات
    $taxTypeQuery = "SELECT id, name, rate FROM tax_types WHERE active = 1 ORDER BY name";
    $stmt = $db->prepare($taxTypeQuery);
    $stmt->execute();
    $taxTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // دریافت واحدهای مالیاتی
    $taxUnitQuery = "SELECT id, name, code FROM tax_units WHERE active = 1 ORDER BY name";
    $stmt = $db->prepare($taxUnitQuery);
    $stmt->execute();
    $taxUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('خطای پایگاه داده: ' . $e->getMessage());
    $_SESSION['error'] = 'خطا در دریافت اطلاعات از پایگاه داده. لطفاً با پشتیبانی تماس بگیرید.';
    header('Location: ' . BASE_URL . '/error.php');
    exit;
} catch (Exception $e) {
    error_log('خطای عمومی: ' . $e->getMessage());
    $_SESSION['error'] = 'خطای سیستمی رخ داده است. لطفاً با پشتیبانی تماس بگیرید.';
    header('Location: ' . BASE_URL . '/error.php');
    exit;
}

// پردازش فرم در صورت ارسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // اعتبارسنجی داده‌های ورودی
        $required_fields = ['name', 'code', 'category_id', 'main_unit'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "فیلد " . $field . " اجباری است.";
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // آماده‌سازی داده‌ها برای ذخیره
        $data = [
            'accounting_code' => $_POST['accounting_code'] ?? null,
            'name' => $_POST['name'],
            'code' => $_POST['code'],
            'barcodes' => $_POST['barcodes'] ?? null,
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
            'min_order' => $_POST['min_order'] ?? 1,
            'lead_time' => $_POST['lead_time'] ?? 0,
            'sales_tax_enabled' => isset($_POST['sales_tax_enabled']) ? 1 : 0,
            'sales_tax_rate' => $_POST['sales_tax_rate'] ?? $defaultTaxRate,
            'purchase_tax_enabled' => isset($_POST['purchase_tax_enabled']) ? 1 : 0,
            'purchase_tax_rate' => $_POST['purchase_tax_rate'] ?? $defaultTaxRate,
            'tax_type_id' => $_POST['tax_type'] ?? null,
            'tax_unit_id' => $_POST['tax_unit'] ?? null,
            'tax_code' => $_POST['tax_code'] ?? null
        ];

        // ذخیره در دیتابیس
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO products ($columns) VALUES ($values)";
        
        $stmt = $db->prepare($query);
        $stmt->execute(array_values($data));

        $_SESSION['success'] = 'محصول با موفقیت ثبت شد.';
        header('Location: ' . BASE_URL . '/pages/products.php');
        exit;

    } catch (PDOException $e) {
        error_log('خطای پایگاه داده در ذخیره محصول: ' . $e->getMessage());
        $_SESSION['error'] = 'خطا در ذخیره اطلاعات محصول. لطفاً با پشتیبانی تماس بگیرید.';
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن محصول جدید - <?php echo SITE_NAME; ?></title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
</head>
<body class="bg-light">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <!-- نمایش پیام‌های خطا -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- نمایش پیام‌های موفقیت -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">افزودن محصول جدید</h5>
                </div>
                <div class="card-body">
                    <form id="productForm" method="POST" class="needs-validation" novalidate>
                        <!-- مشخصات اصلی محصول -->
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
                                <input type="text" class="form-control" id="code" name="code" required>
                                <div class="invalid-feedback">
                                    لطفاً کد محصول را وارد کنید
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">دسته‌بندی <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
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
                                <input type="text" class="form-control" id="accounting_code" name="accounting_code">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label" for="description">توضیحات</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- قیمت‌گذاری -->
                        <h5 class="mb-3">قیمت‌گذاری</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="selling_price">قیمت فروش</label>
                                <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="purchase_price">قیمت خرید</label>
                                <input type="number" class="form-control" id="purchase_price" name="purchase_price" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- واحد و موجودی -->
                        <h5 class="mb-3">واحد و موجودی</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="main_unit">واحد اصلی <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="main_unit" name="main_unit" required>
                                <div class="invalid-feedback">
                                    لطفاً واحد اصلی را وارد کنید
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sub_unit">واحد فرعی</label>
                                <input type="text" class="form-control" id="sub_unit" name="sub_unit">
                            </div>
                        </div>

                        <!-- کنترل موجودی -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="stock_control" name="stock_control">
                                    <label class="form-check-label" for="stock_control">کنترل موجودی</label>
                                </div>
                            </div>
                        </div>

                        <div class="stock-details" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label" for="current_stock">موجودی فعلی</label>
                                    <input type="number" class="form-control" id="current_stock" name="current_stock" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="min_order">حداقل سفارش</label>
                                    <input type="number" class="form-control" id="min_order" name="min_order" min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="lead_time">زمان تحویل (روز)</label>
                                    <input type="number" class="form-control" id="lead_time" name="lead_time" min="0">
                                </div>
                            </div>
                        </div>

                        <!-- مالیات -->
                        <h5 class="mb-3">مالیات</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sales_tax_enabled" name="sales_tax_enabled">
                                    <label class="form-check-label" for="sales_tax_enabled">مالیات فروش</label>
                                </div>
                                <div class="sales-tax-details mt-3" style="display: none;">
                                    <label class="form-label" for="sales_tax_rate">نرخ مالیات فروش (%)</label>
                                    <input type="number" class="form-control" id="sales_tax_rate" name="sales_tax_rate" 
                                           value="<?php echo $defaultTaxRate; ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="purchase_tax_enabled" name="purchase_tax_enabled">
                                    <label class="form-check-label" for="purchase_tax_enabled">مالیات خرید</label>
                                </div>
                                <div class="purchase-tax-details mt-3" style="display: none;">
                                    <label class="form-label" for="purchase_tax_rate">نرخ مالیات خرید (%)</label>
                                    <input type="number" class="form-control" id="purchase_tax_rate" name="purchase_tax_rate" 
                                           value="<?php echo $defaultTaxRate; ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label" for="tax_type">نوع مالیات</label>
                                <select class="form-select" id="tax_type" name="tax_type">
                                    <option value="">انتخاب کنید</option>
                                    <?php foreach ($taxTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type['id']); ?>">
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="tax_unit">واحد مالیاتی</label>
                                <select class="form-select" id="tax_unit" name="tax_unit">
                                    <option value="">انتخاب کنید</option>
                                    <?php foreach ($taxUnits as $unit): ?>
                                        <option value="<?php echo htmlspecialchars($unit['id']); ?>">
                                            <?php echo htmlspecialchars($unit['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="tax_code">کد مالیاتی</label>
                                <input type="text" class="form-control" id="tax_code" name="tax_code">
                            </div>
                        </div>

                        <!-- دکمه‌های عملیات -->
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    ذخیره محصول
                                </button>
                                <a href="<?php echo BASE_URL; ?>/pages/products.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    انصراف
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // راه‌اندازی Select2
            $('.form-select').select2({
                theme: 'bootstrap-5',
                dir: 'rtl'
            });

            // مدیریت نمایش/مخفی‌سازی بخش کنترل موجودی
            $('#stock_control').change(function() {
                $('.stock-details').toggle(this.checked);
            });

            // مدیریت نمایش/مخفی‌سازی بخش‌های مالیات
            $('#sales_tax_enabled').change(function() {
                $('.sales-tax-details').toggle(this.checked);
            });

            $('#purchase_tax_enabled').change(function() {
                $('.purchase-tax-details').toggle(this.checked);
            });

            // اعتبارسنجی فرم
            const form = document.getElementById('productForm');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // تولید خودکار کد حسابداری
            function generateAccountingCode() {
                const timestamp = new Date().getTime();
                const random = Math.floor(Math.random() * 1000);
                return `PRD-${timestamp}-${random}`;
            }

            $('#accounting_code').val(generateAccountingCode());
        });
    </script>
</body>
</html>