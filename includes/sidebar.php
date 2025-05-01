<?php
/**
 * سایدبار اصلی برنامه
 * 
 * Current Date: 2025-05-01 15:59:16
 * Current User: tehplus
 * 
 * @package HesabPars
 * @subpackage Layout
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// دریافت آدرس صفحه فعلی
$current_page = $_GET['url'] ?? '';

// تابع بررسی فعال بودن منو
function isMenuActive($url) {
    global $current_page;
    return strpos($current_page, $url) === 0 ? 'active' : '';
}

// تابع بررسی باز بودن منو
function isMenuOpen($urls) {
    global $current_page;
    foreach ($urls as $url) {
        if (strpos($current_page, $url) === 0) {
            return 'show';
        }
    }
    return '';
}
?>

<!-- سایدبار -->
<aside class="main-sidebar">
    <!-- اسکرول داخلی -->
    <div class="sidebar-wrapper">
        <!-- منوی اصلی -->
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <!-- داشبورد -->
                <li class="nav-item <?php echo isMenuActive('dashboard'); ?>">
                    <a href="<?php echo url('dashboard'); ?>" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>داشبورد</span>
                    </a>
                </li>

                <!-- محصولات -->
                <li class="nav-item">
                    <a href="#productsCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['products', 'categories']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-box"></i>
                        <span>محصولات</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['products', 'categories']); ?>" id="productsCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('products/list'); ?>">
                                <a href="<?php echo url('products/list'); ?>" class="nav-link">
                                    <i class="fas fa-list"></i>
                                    <span>لیست محصولات</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('products/add'); ?>">
                                <a href="<?php echo url('products/add'); ?>" class="nav-link">
                                    <i class="fas fa-plus"></i>
                                    <span>افزودن محصول</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('products/categories'); ?>">
                                <a href="<?php echo url('products/categories'); ?>" class="nav-link">
                                    <i class="fas fa-tags"></i>
                                    <span>دسته‌بندی‌ها</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- انبار -->
                <li class="nav-item">
                    <a href="#inventoryCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['inventory', 'stock']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-warehouse"></i>
                        <span>انبار</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['inventory', 'stock']); ?>" id="inventoryCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('inventory/stock'); ?>">
                                <a href="<?php echo url('inventory/stock'); ?>" class="nav-link">
                                    <i class="fas fa-boxes"></i>
                                    <span>موجودی انبار</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('inventory/transactions'); ?>">
                                <a href="<?php echo url('inventory/transactions'); ?>" class="nav-link">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span>گردش انبار</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- خرید -->
                <li class="nav-item">
                    <a href="#purchasesCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['purchases', 'suppliers']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>خرید</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['purchases', 'suppliers']); ?>" id="purchasesCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('purchases/list'); ?>">
                                <a href="<?php echo url('purchases/list'); ?>" class="nav-link">
                                    <i class="fas fa-list"></i>
                                    <span>لیست خریدها</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('purchases/add'); ?>">
                                <a href="<?php echo url('purchases/add'); ?>" class="nav-link">
                                    <i class="fas fa-plus"></i>
                                    <span>خرید جدید</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('purchases/suppliers'); ?>">
                                <a href="<?php echo url('purchases/suppliers'); ?>" class="nav-link">
                                    <i class="fas fa-truck"></i>
                                    <span>تامین‌کنندگان</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- فروش -->
                <li class="nav-item">
                    <a href="#salesCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['sales', 'customers']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-cash-register"></i>
                        <span>فروش</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['sales', 'customers']); ?>" id="salesCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('sales/list'); ?>">
                                <a href="<?php echo url('sales/list'); ?>" class="nav-link">
                                    <i class="fas fa-list"></i>
                                    <span>لیست فروش‌ها</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('sales/add'); ?>">
                                <a href="<?php echo url('sales/add'); ?>" class="nav-link">
                                    <i class="fas fa-plus"></i>
                                    <span>فروش جدید</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('sales/customers'); ?>">
                                <a href="<?php echo url('sales/customers'); ?>" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <span>مشتریان</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- مالی -->
                <li class="nav-item">
                    <a href="#financialCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['financial', 'accounting']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>امور مالی</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['financial', 'accounting']); ?>" id="financialCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('financial/transactions'); ?>">
                                <a href="<?php echo url('financial/transactions'); ?>" class="nav-link">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span>تراکنش‌ها</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('financial/invoices'); ?>">
                                <a href="<?php echo url('financial/invoices'); ?>" class="nav-link">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span>صورتحساب‌ها</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('financial/expenses'); ?>">
                                <a href="<?php echo url('financial/expenses'); ?>" class="nav-link">
                                    <i class="fas fa-receipt"></i>
                                    <span>هزینه‌ها</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- گزارشات -->
                <li class="nav-item">
                    <a href="#reportsCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['reports']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>گزارشات</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['reports']); ?>" id="reportsCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('reports/sales'); ?>">
                                <a href="<?php echo url('reports/sales'); ?>" class="nav-link">
                                    <i class="fas fa-chart-line"></i>
                                    <span>گزارش فروش</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('reports/inventory'); ?>">
                                <a href="<?php echo url('reports/inventory'); ?>" class="nav-link">
                                    <i class="fas fa-warehouse"></i>
                                    <span>گزارش انبار</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('reports/financial'); ?>">
                                <a href="<?php echo url('reports/financial'); ?>" class="nav-link">
                                    <i class="fas fa-money-check-alt"></i>
                                    <span>گزارش مالی</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- تنظیمات -->
                <li class="nav-item">
                    <a href="#settingsCollapse" class="nav-link collapsed" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo isMenuOpen(['settings']) ? 'true' : 'false'; ?>">
                        <i class="fas fa-cog"></i>
                        <span>تنظیمات</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="collapse <?php echo isMenuOpen(['settings']); ?>" id="settingsCollapse">
                        <ul class="nav-list">
                            <li class="nav-item <?php echo isMenuActive('settings/general'); ?>">
                                <a href="<?php echo url('settings/general'); ?>" class="nav-link">
                                    <i class="fas fa-sliders-h"></i>
                                    <span>تنظیمات عمومی</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('settings/users'); ?>">
                                <a href="<?php echo url('settings/users'); ?>" class="nav-link">
                                    <i class="fas fa-users-cog"></i>
                                    <span>کاربران</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('settings/roles'); ?>">
                                <a href="<?php echo url('settings/roles'); ?>" class="nav-link">
                                    <i class="fas fa-user-shield"></i>
                                    <span>نقش‌ها و دسترسی‌ها</span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo isMenuActive('settings/backup'); ?>">
                                <a href="<?php echo url('settings/backup'); ?>" class="nav-link">
                                    <i class="fas fa-database"></i>
                                    <span>پشتیبان‌گیری</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<!-- استایل‌های سایدبار -->
<style>
.main-sidebar {
    position: fixed;
    top: 60px;
    right: 0;
    bottom: 0;
    width: 260px;
    background: #ffffff;
    border-left: 1px solid #e5e9f2;
    transition: all 0.3s ease;
    z-index: 1000;
    overflow: hidden;
}

.sidebar-collapsed .main-sidebar {
    width: 70px;
}

.sidebar-wrapper {
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #e5e9f2 transparent;
}

.sidebar-wrapper::-webkit-scrollbar {
    width: 6px;
}

.sidebar-wrapper::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-wrapper::-webkit-scrollbar-thumb {
    background-color: #e5e9f2;
    border-radius: 3px;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin: 4px 8px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #506690;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.nav-link:hover {
    color: #2c3e50;
    background: #f8f9fa;
}

.nav-link.active {
    color: #3498db;
    background: #edf2f7;
}

.nav-link i:first-child {
    width: 20px;
    margin-left: 10px;
    font-size: 16px;
    text-align: center;
}

.nav-link i.fa-chevron-left {
    margin-right: auto;
    margin-left: 0;
    font-size: 12px;
    transition: transform 0.2s ease;
}

.nav-link[aria-expanded="true"] i.fa-chevron-left {
    transform: rotate(-90deg);
}

.nav-link span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-collapsed .nav-link span,
.sidebar-collapsed .nav-link i.fa-chevron-left {
    display: none;
}

.collapse {
    padding-right: 34px;
}

.sidebar-collapsed .collapse {
    display: none !important;
}

/* ریسپانسیو */
@media (max-width: 992px) {
    .main-sidebar {
        transform: translateX(100%);
    }
    
    .sidebar-open .main-sidebar {
        transform: translateX(0);
    }
}

/* تم تاریک */
[data-theme="dark"] .main-sidebar {
    background: #1a1c23;
    border-color: #2d3748;
}

[data-theme="dark"] .nav-link {
    color: #cbd5e0;
}

[data-theme="dark"] .nav-link:hover {
    color: #ffffff;
    background: #2d3748;
}

[data-theme="dark"] .nav-link.active {
    color: #60a5fa;
    background: #2d3748;
}
</style>

<!-- اسکریپت‌های سایدبار -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // بستن سایر منوها هنگام باز کردن یک منو
    const collapseElements = document.querySelectorAll('.collapse');
    collapseElements.forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', function() {
            collapseElements.forEach(otherCollapse => {
                if (otherCollapse !== collapse && bootstrap.Collapse.getInstance(otherCollapse)) {
                    bootstrap.Collapse.getInstance(otherCollapse).hide();
                }
            });
        });
    });

    // مدیریت کلیک روی دکمه تغییر وضعیت سایدبار در موبایل
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    }

    // بستن سایدبار در موبایل با کلیک خارج از آن
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 992 && 
            !event.target.closest('.main-sidebar') && 
            !event.target.closest('#sidebarToggle')) {
            document.body.classList.remove('sidebar-open');
        }
    });

    // تنظیم عرض محتوای اصلی
    function adjustMainContent() {
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            if (window.innerWidth > 992) {
                const sidebarWidth = document.body.classList.contains('sidebar-collapsed') ? 70 : 260;
                mainContent.style.marginRight = sidebarWidth + 'px';
            } else {
                mainContent.style.marginRight = '0';
            }
        }
    }

    // اجرای تابع تنظیم عرض در لود صفحه و تغییر سایز
    adjustMainContent();
    window.addEventListener('resize', adjustMainContent);
    
    // اجرای مجدد هنگام تغییر وضعیت سایدبار
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                adjustMainContent();
            }
        });
    });

    observer.observe(document.body, {
        attributes: true
    });
});
</script>