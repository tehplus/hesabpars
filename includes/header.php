<?php
/**
 * هدر مشترک برای تمام صفحات
 * 
 * Current Date: 2025-05-01 15:52:19
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

// دریافت تعداد اعلان‌های خوانده نشده
$notifications_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE user_id = ? 
        AND is_read = 0
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications_count = $stmt->fetchColumn();
}

// دریافت پیام‌های خوانده نشده
$messages_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM messages 
        WHERE receiver_id = ? 
        AND is_read = 0
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages_count = $stmt->fetchColumn();
}

// دریافت تنظیمات کاربر
$user_settings = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("
        SELECT theme, sidebar_collapsed, notifications_enabled 
        FROM user_settings 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'theme' => 'light',
        'sidebar_collapsed' => false,
        'notifications_enabled' => true
    ];
}

// دریافت منوی دسترسی سریع
$quick_menu = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("
        SELECT title, url, icon 
        FROM quick_menu 
        WHERE user_id = ? 
        ORDER BY sort_order
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $quick_menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="<?php echo $user_settings['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo SITE_KEYWORDS; ?>">
    <meta name="author" content="<?php echo SITE_AUTHOR; ?>">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo $auth->getCSRFToken(); ?>">
    
    <title><?php echo isset($page_title) ? e($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- فویکون -->
    <link rel="icon" type="image/x-icon" href="<?php echo asset('img/favicon.ico'); ?>">
    
    <!-- استایل‌ها -->
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.rtl.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/select2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/select2-bootstrap5-theme.rtl.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/sweetalert2.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    
    <!-- اسکریپت‌های اصلی -->
    <script src="<?php echo asset('js/jquery.min.js'); ?>"></script>
    
    <!-- تنظیمات پایه JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const CSRF_TOKEN = '<?php echo $auth->getCSRFToken(); ?>';
        const USER_ID = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        const IS_RTL = true;
        const NOTIFICATIONS_ENABLED = <?php echo ($user_settings['notifications_enabled'] ?? true) ? 'true' : 'false'; ?>;
        
        // تنظیمات عمومی
        window.HesabPars = {
            debug: <?php echo DEBUG_MODE ? 'true' : 'false'; ?>,
            timezone: '<?php echo DEFAULT_TIMEZONE; ?>',
            dateFormat: '<?php echo DATE_FORMAT; ?>',
            timeFormat: '<?php echo TIME_FORMAT; ?>',
            currency: '<?php echo DEFAULT_CURRENCY; ?>',
            currencySymbol: '<?php echo DEFAULT_CURRENCY_SYMBOL; ?>',
            locale: '<?php echo DEFAULT_LANGUAGE; ?>',
            theme: '<?php echo $user_settings['theme'] ?? 'light'; ?>'
        };
    </script>
</head>
<body class="<?php echo $user_settings['sidebar_collapsed'] ? 'sidebar-collapsed' : ''; ?>">
    <!-- نوار بالای صفحه -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <!-- لوگو و دکمه تغییر وضعیت سایدبار -->
                <div class="header-right">
                    <button type="button" class="btn btn-link sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="<?php echo BASE_URL; ?>" class="navbar-brand">
                        <img src="<?php echo asset('img/logo.png'); ?>" alt="<?php echo e(SITE_NAME); ?>" height="40">
                    </a>
                </div>

                <!-- جستجوی سریع -->
                <form class="quick-search-form d-none d-lg-flex" action="<?php echo url('search'); ?>" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="جستجوی سریع..." 
                               name="q" id="quickSearch" autocomplete="off">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- منوی سمت چپ -->
                <div class="header-left">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- دسترسی سریع -->
                        <?php if (!empty($quick_menu)): ?>
                            <div class="nav-item dropdown">
                                <button type="button" class="btn btn-link nav-link dropdown-toggle" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-star"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end quick-menu-dropdown">
                                    <?php foreach ($quick_menu as $menu_item): ?>
                                        <a class="dropdown-item" href="<?php echo e($menu_item['url']); ?>">
                                            <i class="<?php echo e($menu_item['icon']); ?> fa-fw"></i>
                                            <?php echo e($menu_item['title']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo url('quick-menu/manage'); ?>">
                                        <i class="fas fa-cog fa-fw"></i>
                                        مدیریت دسترسی سریع
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- اعلان‌ها -->
                        <div class="nav-item dropdown">
                            <button type="button" class="btn btn-link nav-link dropdown-toggle" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if ($notifications_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $notifications_count; ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                                <div class="dropdown-header">
                                    <span>اعلان‌ها</span>
                                    <?php if ($notifications_count > 0): ?>
                                        <a href="<?php echo url('notifications/mark-all-read'); ?>" 
                                           class="mark-all-read">
                                            علامت‌گذاری همه به‌عنوان خوانده‌شده
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-body" id="notificationsContainer">
                                    <div class="loading">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                </div>
                                <div class="dropdown-footer">
                                    <a href="<?php echo url('notifications'); ?>">
                                        مشاهده همه اعلان‌ها
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- پیام‌ها -->
                        <div class="nav-item dropdown">
                            <button type="button" class="btn btn-link nav-link dropdown-toggle" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-envelope"></i>
                                <?php if ($messages_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $messages_count; ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end messages-dropdown">
                                <div class="dropdown-header">
                                    <span>پیام‌ها</span>
                                    <?php if ($messages_count > 0): ?>
                                        <a href="<?php echo url('messages/mark-all-read'); ?>" 
                                           class="mark-all-read">
                                            علامت‌گذاری همه به‌عنوان خوانده‌شده
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-body" id="messagesContainer">
                                    <div class="loading">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                </div>
                                <div class="dropdown-footer">
                                    <a href="<?php echo url('messages'); ?>">
                                        مشاهده همه پیام‌ها
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- منوی کاربری -->
                        <div class="nav-item dropdown">
                            <button type="button" class="btn btn-link nav-link dropdown-toggle" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo getUserAvatar($_SESSION['user_id']); ?>" 
                                     alt="<?php echo e($_SESSION['full_name']); ?>" 
                                     class="rounded-circle" width="32" height="32">
                                <span class="d-none d-lg-inline-block ms-2">
                                    <?php echo e($_SESSION['full_name']); ?>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end user-dropdown">
                                <div class="dropdown-header">
                                    <div class="user-info">
                                        <img src="<?php echo getUserAvatar($_SESSION['user_id']); ?>" 
                                             alt="<?php echo e($_SESSION['full_name']); ?>" 
                                             class="rounded-circle" width="64" height="64">
                                        <div class="user-details">
                                            <h6><?php echo e($_SESSION['full_name']); ?></h6>
                                            <span><?php echo e($_SESSION['username']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo url('profile'); ?>">
                                    <i class="fas fa-user fa-fw"></i>
                                    پروفایل
                                </a>
                                <a class="dropdown-item" href="<?php echo url('settings'); ?>">
                                    <i class="fas fa-cog fa-fw"></i>
                                    تنظیمات
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo url('logout'); ?>">
                                    <i class="fas fa-sign-out-alt fa-fw"></i>
                                    خروج
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- دکمه‌های ورود و ثبت‌نام -->
                        <a href="<?php echo url('login'); ?>" class="btn btn-outline-primary me-2">
                            ورود
                        </a>
                        <a href="<?php echo url('register'); ?>" class="btn btn-primary">
                            ثبت‌نام
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- محتوای اصلی -->
    <div class="main-wrapper">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- سایدبار -->
            <?php require_once INCLUDES_PATH . '/sidebar.php'; ?>
        <?php endif; ?>

        <!-- محتوا -->
        <main class="main-content">
            <!-- نوار بالای محتوا -->
            <?php if (isset($breadcrumbs)): ?>
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">
                                    <?php echo isset($page_title) ? e($page_title) : ''; ?>
                                </h1>
                            </div>
                            <div class="col-sm-6">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb float-sm-end">
                                        <?php foreach ($breadcrumbs as $breadcrumb): ?>
                                            <?php if (isset($breadcrumb['url'])): ?>
                                                <li class="breadcrumb-item">
                                                    <a href="<?php echo e($breadcrumb['url']); ?>">
                                                        <?php echo e($breadcrumb['title']); ?>
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="breadcrumb-item active">
                                                    <?php echo e($breadcrumb['title']); ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- پیام‌های سیستم -->
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">';
                echo e($_SESSION['error']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['error']);
            }

            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">';
                echo e($_SESSION['success']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['warning'])) {
                echo '<div class="alert alert-warning alert-dismissible fade show m-3" role="alert">';
                echo e($_SESSION['warning']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['warning']);
            }

            if (isset($_SESSION['info'])) {
                echo '<div class="alert alert-info alert-dismissible fade show m-3" role="alert">';
                echo e($_SESSION['info']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['info']);
            }
            ?>

            <!-- محتوای اصلی صفحه -->
            <div class="content">
                <div class="container-fluid">
                    <!-- اینجا محتوای اصلی هر صفحه قرار می‌گیرد -->
                </div>
            </div>
        </main>
    </div>

    <!-- اسکریپت‌های اصلی -->
    <script src="<?php echo asset('js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo asset('js/select2.min.js'); ?>"></script>
    <script src="<?php echo asset('js/sweetalert2.all.min.js'); ?>"></script>
    <script src="<?php echo asset('js/marked.min.js'); ?>"></script>

    <!-- اسکریپت‌های سفارشی -->
    <script>
        // تنظیم jQuery
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        // بارگذاری اعلان‌ها
        function loadNotifications() {
            if (!NOTIFICATIONS_ENABLED) return;

            $.get(BASE_URL + '/notifications/get-latest', function(data) {
                $('#notificationsContainer').html(data);
            });
        }

        // بارگذاری پیام‌ها
        function loadMessages() {
            $.get(BASE_URL + '/messages/get-latest', function(data) {
                $('#messagesContainer').html(data);
            });
        }

        // تغییر وضعیت سایدبار
        $('#sidebarToggle').on('click', function() {
            $('body').toggleClass('sidebar-collapsed');
            
            // ذخیره وضعیت در دیتابیس
            $.post(BASE_URL + '/settings/update-sidebar', {
                collapsed: $('body').hasClass('sidebar-collapsed')
            });
        });

        // راه‌اندازی Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            dir: 'rtl'
        });

        // تنظیمات SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // نمایش اعلان‌ها در صورت فعال بودن
        if (NOTIFICATIONS_ENABLED) {
            // بارگذاری اولیه
            loadNotifications();
            loadMessages();

            // بروزرسانی هر 30 ثانیه
            setInterval(function() {
                loadNotifications();
                loadMessages();
            }, 30000);
        }

        // مدیریت کلیک روی لینک‌های اعلان
        $(document).on('click', '.notification-link', function(e) {
            e.preventDefault();
            const $this = $(this);
            
            // علامت‌گذاری به عنوان خوانده شده
            $.post(BASE_URL + '/notifications/mark-read', {
                id: $this.data('id')
            });

            // انتقال به صفحه مورد نظر
            window.location.href = $this.attr('href');
        });

        // مدیریت جستجوی سریع
        let searchTimeout;
        $('#quickSearch').on('keyup', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();
            
            if (query.length < 2) return;

            searchTimeout = setTimeout(function() {
                $.get(BASE_URL + '/search/quick', { q: query }, function(data) {
                    // نمایش نتایج جستجو
                    // ...
                });
            }, 300);
        });

        // مدیریت تغییر تم
        function switchTheme(theme) {
            $('html').attr('data-theme', theme);
            
            $.post(BASE_URL + '/settings/update-theme', {
                theme: theme
            });
        }

        // مدیریت خطاهای AJAX
        $(document).ajaxError(function(event, jqXHR, settings, error) {
            if (jqXHR.status === 401) {
                // خطای عدم احراز هویت
                window.location.href = BASE_URL + '/login';
            } else if (jqXHR.status === 403) {
                // خطای عدم دسترسی
                Toast.fire({
                    icon: 'error',
                    title: 'شما دسترسی لازم برای انجام این عملیات را ندارید'
                });
            } else {
                // سایر خطاها
                Toast.fire({
                    icon: 'error',
                    title: 'خطایی رخ داده است. لطفاً دوباره تلاش کنید'
                });
            }
        });
    </script>

    <!-- اسکریپت‌های اضافی صفحه -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo asset('js/' . e($script)); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>