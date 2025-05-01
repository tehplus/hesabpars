<?php
/**
 * سایدبار اصلی برنامه حسابپارس
 * 
 * @package HesabPars
 * @subpackage Layout
 * @version 2.0.0
 */

// جلوگیری از دسترسی مستقیم
defined('BASE_PATH') or die('دسترسی مستقیم به این فایل مجاز نیست.');

// تابع تشخیص منوی فعال
function isActive($path) {
    $current_path = $_SERVER['REQUEST_URI'];
    return (strpos($current_path, $path) !== false) ? 'active' : '';
}
?>

<!-- شروع سایدبار -->
<div class="sidebar">
    <!-- هدر سایدبار با لوگو -->
    <div class="sidebar-header">
        <div class="logo-box">
            <img src="<?php echo asset('images/logo.png'); ?>" alt="<?php echo SITE_NAME; ?>" class="logo">
        </div>
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- منوی اصلی -->
    <div class="sidebar-menu">
        <ul class="menu-items">
            <!-- داشبورد -->
            <li class="menu-item <?php echo isActive('dashboard'); ?>">
                <a href="<?php echo url('dashboard'); ?>" class="menu-link">
                    <i class="fas fa-home"></i>
                    <span>داشبورد</span>
                </a>
            </li>

            <!-- محصولات -->
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-box"></i>
                    <span>محصولات</span>
                    <i class="fas fa-angle-left submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo isActive('products/list'); ?>">
                        <a href="<?php echo url('products/list'); ?>">
                            <i class="fas fa-list"></i>
                            <span>لیست محصولات</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('products/add'); ?>">
                        <a href="<?php echo url('products/add'); ?>">
                            <i class="fas fa-plus"></i>
                            <span>افزودن محصول</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('products/categories'); ?>">
                        <a href="<?php echo url('products/categories'); ?>">
                            <i class="fas fa-tags"></i>
                            <span>دسته‌بندی‌ها</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- انبار -->
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-warehouse"></i>
                    <span>انبار</span>
                    <i class="fas fa-angle-left submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo isActive('inventory/stock'); ?>">
                        <a href="<?php echo url('inventory/stock'); ?>">
                            <i class="fas fa-boxes"></i>
                            <span>موجودی انبار</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('inventory/transactions'); ?>">
                        <a href="<?php echo url('inventory/transactions'); ?>">
                            <i class="fas fa-exchange-alt"></i>
                            <span>گردش انبار</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- فروش -->
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>فروش</span>
                    <i class="fas fa-angle-left submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo isActive('sales/list'); ?>">
                        <a href="<?php echo url('sales/list'); ?>">
                            <i class="fas fa-list"></i>
                            <span>لیست فروش</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('sales/add'); ?>">
                        <a href="<?php echo url('sales/add'); ?>">
                            <i class="fas fa-plus"></i>
                            <span>فروش جدید</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- مشتریان -->
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-users"></i>
                    <span>مشتریان</span>
                    <i class="fas fa-angle-left submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo isActive('customers/list'); ?>">
                        <a href="<?php echo url('customers/list'); ?>">
                            <i class="fas fa-list"></i>
                            <span>لیست مشتریان</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('customers/add'); ?>">
                        <a href="<?php echo url('customers/add'); ?>">
                            <i class="fas fa-user-plus"></i>
                            <span>مشتری جدید</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- گزارشات -->
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>گزارشات</span>
                    <i class="fas fa-angle-left submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo isActive('reports/sales'); ?>">
                        <a href="<?php echo url('reports/sales'); ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>گزارش فروش</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('reports/inventory'); ?>">
                        <a href="<?php echo url('reports/inventory'); ?>">
                            <i class="fas fa-box"></i>
                            <span>گزارش انبار</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('reports/customers'); ?>">
                        <a href="<?php echo url('reports/customers'); ?>">
                            <i class="fas fa-users"></i>
                            <span>گزارش مشتریان</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- تنظیمات -->
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-cog"></i>
                    <span>تنظیمات</span>
                    <i class="fas fa-angle-left submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="<?php echo isActive('settings/profile'); ?>">
                        <a href="<?php echo url('settings/profile'); ?>">
                            <i class="fas fa-user-cog"></i>
                            <span>پروفایل</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('settings/users'); ?>">
                        <a href="<?php echo url('settings/users'); ?>">
                            <i class="fas fa-users-cog"></i>
                            <span>کاربران</span>
                        </a>
                    </li>
                    <li class="<?php echo isActive('settings/backup'); ?>">
                        <a href="<?php echo url('settings/backup'); ?>">
                            <i class="fas fa-database"></i>
                            <span>پشتیبان‌گیری</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- فوتر سایدبار -->
    <div class="sidebar-footer">
        <div class="user-box">
            <img src="<?php echo asset('images/avatar.png'); ?>" alt="تصویر کاربر" class="user-avatar">
            <div class="user-info">
                <h5><?php echo $_SESSION['user_full_name'] ?? 'کاربر'; ?></h5>
                <p><?php echo $_SESSION['user_role'] ?? 'کاربر عادی'; ?></p>
            </div>
        </div>
        <div class="footer-actions">
            <a href="<?php echo url('settings/profile'); ?>" title="تنظیمات">
                <i class="fas fa-cog"></i>
            </a>
            <a href="<?php echo url('auth/logout'); ?>" title="خروج">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</div>

<!-- استایل سایدبار -->
<style>
.sidebar {
    position: fixed;
    top: 0;
    right: 0;
    width: 280px;
    height: 100vh;
    background: #ffffff;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.sidebar.collapsed {
    width: 80px;
}

/* هدر سایدبار */
.sidebar-header {
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f0f0f0;
}

.logo-box {
    display: flex;
    align-items: center;
}

.logo {
    height: 40px;
    width: auto;
}

.sidebar-toggle {
    width: 30px;
    height: 30px;
    border: none;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    background: #e9ecef;
}

.sidebar.collapsed .sidebar-toggle i {
    transform: rotate(180deg);
}

/* منوی اصلی */
.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
}

.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    margin: 5px 15px;
    border-radius: 8px;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #495057;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.menu-link:hover {
    background: #f8f9fa;
    color: #2196f3;
}

.menu-link i:first-child {
    width: 20px;
    margin-left: 10px;
    font-size: 18px;
}

.menu-link span {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.submenu-arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
}

/* زیرمنو */
.submenu {
    list-style: none;
    padding: 5px 0;
    margin: 5px 0 0 0;
    background: #f8f9fa;
    border-radius: 8px;
    display: none;
}

.has-submenu.open .submenu {
    display: block;
}

.has-submenu.open .submenu-arrow {
    transform: rotate(-90deg);
}

.submenu li {
    padding: 0 15px;
}

.submenu a {
    display: flex;
    align-items: center;
    padding: 8px 15px;
    color: #6c757d;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9em;
    transition: all 0.3s ease;
}

.submenu a:hover {
    background: #e9ecef;
    color: #2196f3;
}

.submenu i {
    width: 18px;
    margin-left: 8px;
    font-size: 14px;
}

/* منوی فعال */
.menu-item.active > .menu-link,
.submenu li.active > a {
    background: #e3f2fd;
    color: #2196f3;
}

/* فوتر سایدبار */
.sidebar-footer {
    padding: 15px;
    border-top: 1px solid #f0f0f0;
    background: #ffffff;
}

.user-box {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-left: 10px;
}

.user-info h5 {
    margin: 0;
    font-size: 14px;
    color: #495057;
}

.user-info p {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
}

.footer-actions {
    display: flex;
    justify-content: space-between;
    padding: 0 10px;
}

.footer-actions a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-actions a:hover {
    color: #2196f3;
}

/* حالت جمع شده */
.sidebar.collapsed .logo-box span,
.sidebar.collapsed .menu-link span,
.sidebar.collapsed .submenu,
.sidebar.collapsed .user-info {
    display: none;
}

.sidebar.collapsed .menu-item {
    margin: 5px 10px;
}

.sidebar.collapsed .menu-link {
    padding: 12px;
    justify-content: center;
}

.sidebar.collapsed .menu-link i:first-child {
    margin: 0;
}

.sidebar.collapsed .submenu-arrow {
    display: none;
}

/* ریسپانسیو */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(100%);
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        z-index: 999;
        display: none;
    }
    
    .sidebar.open + .sidebar-backdrop {
        display: block;
    }
}
</style>

<!-- اسکریپت سایدبار -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // دکمه‌های تاگل سایدبار
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    // بازیابی وضعیت قبلی سایدبار
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
    
    // منوهای کشویی
    const submenuItems = document.querySelectorAll('.has-submenu > .menu-link');
    
    submenuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const parent = this.parentElement;
            const isOpen = parent.classList.contains('open');
            
            // بستن همه منوهای باز
            document.querySelectorAll('.has-submenu').forEach(el => {
                if (el !== parent) {
                    el.classList.remove('open');
                }
            });
            
            // تاگل منوی فعلی
            parent.classList.toggle('open');
            
            // ذخیره وضعیت منو
            if (!isOpen) {
                localStorage.setItem('last_open_menu', parent.querySelector('.menu-link span').textContent);
            }
        });
    });
    
    // بازیابی آخرین منوی باز
    const lastOpenMenu = localStorage.getItem('last_open_menu');
    if (lastOpenMenu) {
        document.querySelectorAll('.has-submenu').forEach(item => {
            if (item.querySelector('.menu-link span').textContent === lastOpenMenu) {
                item.classList.add('open');
            }
        });
    }
    
    // حالت موبایل
    const mobileToggle = document.querySelector('.mobile-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.add('open');
        });
    }
    
    // بستن سایدبار در موبایل با کلیک بیرون
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && 
            !e.target.closest('.sidebar') && 
            !e.target.closest('.mobile-toggle')) {
            sidebar.classList.remove('open');
        }
    });
    
    // تنظیم ارتفاع اسکرول
    function adjustMenuHeight() {
        const header = document.querySelector('.sidebar-header');
        const footer = document.querySelector('.sidebar-footer');
        const menu = document.querySelector('.sidebar-menu');
        
        if (header && footer && menu) {
            const height = window.innerHeight - header.offsetHeight - footer.offsetHeight;
            menu.style.height = height + 'px';
        }
    }
    
    adjustMenuHeight();
    window.addEventListener('resize', adjustMenuHeight);
});
</script>