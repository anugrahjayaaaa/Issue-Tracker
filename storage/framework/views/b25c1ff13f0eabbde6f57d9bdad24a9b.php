<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<h3><?php echo e(ui('notifications')); ?></h3>
<p class="text-muted"><?php echo e(ui('notifications_intro')); ?></p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item py-3 <?php echo e($n->read_at ? '' : 'list-group-item-primary'); ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="min-w-0">
                            <div class="fw-semibold text-break">
                                <i class="bi bi-activity text-primary me-1"></i><?php echo e($n->data['label'] ?? ($n->data['action'] ?? 'Notification')); ?>

                            </div>
                            <div class="text-muted small mt-1">
                                <?php if(!empty($n->data['ip'])): ?>
                                    <span class="me-2"><i class="bi bi-geo-alt"></i> <?php echo e($n->data['ip']); ?></span>
                                <?php endif; ?>
                                <?php if($n->read_at): ?>
                                    <span>read <?php echo e($n->read_at->diffForHumans()); ?></span>
                                <?php else: ?>
                                    <span class="text-primary fw-medium"><?php echo e(ui('unread')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-muted small text-nowrap ms-2"><?php echo e($n->created_at->diffForHumans()); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="list-group-item py-4 text-center text-muted"><?php echo e(ui('no_notifications_yet')); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if($notifications->hasPages()): ?>
    <div class="mt-3">
        <?php echo e($notifications->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/notifications/index.blade.php ENDPATH**/ ?>