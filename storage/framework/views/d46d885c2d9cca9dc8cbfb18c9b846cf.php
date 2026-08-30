<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-primary" style="width:48px;height:48px"><i class="bi bi-people fs-4"></i></span>
                <div><div class="text-muted small"><?php echo e(ui('users')); ?></div><div class="fs-4 fw-semibold"><?php echo e($userCount ?? 0); ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-success" style="width:48px;height:48px"><i class="bi bi-shield fs-4"></i></span>
                <div><div class="text-muted small"><?php echo e(ui('roles')); ?></div><div class="fs-4 fw-semibold"><?php echo e($roleCount ?? 0); ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-warning" style="width:48px;height:48px"><i class="bi bi-journal-text fs-4"></i></span>
                <div><div class="text-muted small"><?php echo e(ui('audit_entries')); ?></div><div class="fs-4 fw-semibold"><?php echo e($auditCount ?? 0); ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-info" style="width:48px;height:48px"><i class="bi bi-hdd-network fs-4"></i></span>
                <div><div class="text-muted small"><?php echo e(ui('database')); ?></div><div class="fs-6 fw-semibold"><?php echo e($dbName ?? 'n/a'); ?></div></div>
            </div>
        </div>
    </div>
</div>


<?php
    $licStatus = $licenseStatus ?? 'none';
    $licBadge = match ($licStatus) {
        'active' => 'text-bg-success',
        'expired', 'revoked' => 'text-bg-danger',
        'none' => 'text-bg-secondary',
        default => 'text-bg-warning',
    };
    $licText = match ($licStatus) {
        'active' => 'License: '.($licenseDaysLeft === null ? 'Lifetime' : $licenseDaysLeft.' days left'),
        'expired' => 'Expired — downgraded to Free',
        'revoked' => 'Revoked — downgraded to Free',
        'none' => 'No license — Free plan',
        default => 'License: '.$licStatus,
    };
?>
<div class="mb-3">
    <span class="badge <?php echo e($licBadge); ?> fs-6">
        <i class="bi bi-patch-check me-1"></i><?php echo e($licText); ?>

        <span class="opacity-75 ms-1">(<?php echo e($activePlan ?? 'free'); ?>)</span>
    </span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1"><?php echo e(ui('welcome', ['name' => auth()->user()->name])); ?> 👋</h5>
        <p class="text-muted mb-0"><?php echo e(ui('dashboard_subtitle')); ?></p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/dashboard.blade.php ENDPATH**/ ?>