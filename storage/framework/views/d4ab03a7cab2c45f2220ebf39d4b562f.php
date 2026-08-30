<?php $__env->startSection('title', 'Translations'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 h3"><?php echo e(__('messages.translations')); ?></h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if(session('status')): ?>
            <div class="alert alert-success"><?php echo e(session('status')); ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo e(ui('group')); ?></th>
                                <th><?php echo e(ui('key')); ?></th>
                                <th>EN</th>
                                <th>ID</th>
                                <th class="text-end"><?php echo e(ui('action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><span class="badge text-bg-secondary"><?php echo e($line->group); ?></span></td>
                                    <td><code><?php echo e($line->key); ?></code></td>
                                    <td><?php echo e($line->text['en'] ?? ''); ?></td>
                                    <td><?php echo e($line->text['id'] ?? ''); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo e(route('translations.edit', $line)); ?>" class="btn btn-sm btn-outline-primary"><?php echo e(ui('edit')); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4"><?php echo e(ui('no_translations')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($lines->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($lines->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/settings/translations/index.blade.php ENDPATH**/ ?>