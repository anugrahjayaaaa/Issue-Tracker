<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Auth'); ?> · <?php echo e(config('app.name', 'Laravel')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/css/adminlte.min.css')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/app-theme.css')); ?>">
    <style>
        body.login-page { background: var(--lbp-bg); display: flex; align-items: center; min-height: 100vh; }
        .login-box { width: 100%; max-width: 400px; margin: auto; }
        .login-box .card { border-radius: var(--lbp-radius); border: 1px solid var(--lbp-border); box-shadow: var(--lbp-shadow); }
        .login-box .card-header { background: transparent; border-bottom: 1px solid var(--lbp-border); padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .input-group-text { background: var(--lbp-surface-2); border-color: var(--lbp-border); color: var(--lbp-muted); }
        .form-control { background: var(--lbp-surface-2); border-color: var(--lbp-border); color: var(--lbp-text); }
        .form-control:focus { background: var(--lbp-surface-2); border-color: var(--lbp-primary); color: var(--lbp-text); box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--lbp-primary) 25%, transparent); }
        .login-box-msg { color: var(--lbp-muted); }
    </style>
</head>
<body class="login-page">
    <?php echo $__env->yieldContent('content'); ?>
    <script src="<?php echo e(asset('vendor/bootstrap/js/bootstrap.min.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/anugrahjayasakti/Projects/Laravel/issue-tracker/resources/views/layouts/auth.blade.php ENDPATH**/ ?>