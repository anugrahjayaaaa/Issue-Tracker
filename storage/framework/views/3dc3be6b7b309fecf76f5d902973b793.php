<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? config('app.name', 'Laravel')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/css/adminlte.min.css')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/app-theme.css')); ?>">
    <style>
        /* keep header user dropdown above header bar (AdminLTE can clip it) */
        .app-header { overflow: visible; }
        .app-header .dropdown-menu { z-index: 1030; }
        /* native <details> user menu (no Bootstrap/Popper dependency) */
        .user-menu > summary { list-style: none; cursor: pointer; }
        .user-menu > summary::-webkit-details-marker { display: none; }
        .user-menu .dropdown-menu { display: none; position: absolute; right: 0; z-index: 1030; }
        .user-menu[open] .dropdown-menu { display: block; }
    </style>
</head>
<body class="layout-fixed sidebar-open">
<div class="app-wrapper">

    <?php echo $__env->make('partials.layout.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.layout.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"><?php echo e($title ?? ''); ?></h3></div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </main>

    <?php echo $__env->make('partials.layout.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php echo $__env->make('partials.layout.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/anugrahjayasakti/Projects/Laravel/issue-tracker/resources/views/layouts/app.blade.php ENDPATH**/ ?>