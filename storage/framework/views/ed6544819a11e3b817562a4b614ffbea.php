<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<h3><?php echo e(ui('feature_flags')); ?></h3>
<p class="text-muted"><?php echo e(ui('feature_flags_intro', ['code' => '<code>feature.manage</code>'])); ?></p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th><?php echo e(ui('feature')); ?></th><th><?php echo e(ui('slug')); ?></th><th><?php echo e(ui('status')); ?></th><th class="text-end"><?php echo e(ui('action')); ?></th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($feature['label']); ?></td>
                    <td><span class="text-muted small"><?php echo e($feature['slug']); ?></span></td>
                    <td>
                        <?php if($feature['enabled']): ?>
                            <span class="badge text-bg-success"><?php echo e(ui('enabled')); ?></span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary"><?php echo e(ui('disabled')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="form-check form-switch d-inline-flex align-items-center justify-content-end mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="feat-<?php echo e($feature['slug']); ?>" <?php echo e($feature['enabled'] ? 'checked' : ''); ?>

                                   aria-label="Toggle <?php echo e($feature['label']); ?>"
                                   data-bs-toggle="modal" data-bs-target="#featureToggleModal"
                                   data-action="<?php echo e(route('features.toggle', $feature['slug'])); ?>"
                                   data-enabled="<?php echo e($feature['enabled'] ? '0' : '1'); ?>">
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center text-muted py-4"><?php echo e(ui('no_features')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php echo $__env->make('partials.modals.feature-toggle-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/Laravel/issue-tracker/resources/views/settings/features/index.blade.php ENDPATH**/ ?>