<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطای سرور | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.rtl.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center p-5">
                        <h1 class="text-danger mb-4">
                            <i class="fas fa-exclamation-triangle"></i>
                            خطای سرور
                        </h1>
                        <p class="mb-4">
                            متأسفانه خطایی در سرور رخ داده است.
                            لطفاً دوباره تلاش کنید.
                        </p>
                        <a href="<?php echo url('/'); ?>" class="btn btn-primary">
                            بازگشت به صفحه اصلی
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>