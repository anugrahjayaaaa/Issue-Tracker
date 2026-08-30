<?php $__env->startSection('title', 'Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 h3"><?php echo e(__('messages.system_logs')); ?></h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                    <label class="mb-0"><?php echo e(ui('file')); ?></label>
                    <select name="file" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($f); ?>" <?php if($f === $current): ?> selected <?php endif; ?>><?php echo e($f); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <label class="mb-0 ms-2"><?php echo e(ui('level')); ?></label>
                    <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value=""><?php echo e(ui('all')); ?></option>
                        <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($l); ?>" <?php if($l === $activeLevel): ?> selected <?php endif; ?>><?php echo e(ucfirst($l)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
                <?php if($current): ?>
                <a href="?file=<?php echo e(urlencode($current)); ?>&dl=<?php echo e(urlencode($current)); ?>" class="btn btn-sm btn-outline-secondary ms-auto"><?php echo e(ui('download')); ?></a>
                <?php endif; ?>
            </div>
                <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh; overflow:auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:90px"><?php echo e(ui('level')); ?></th>
                                <th style="width:170px"><?php echo e(ui('date')); ?></th>
                                <th><?php echo e(ui('message')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $levelMap = [
                                        'error' => 'danger', 'critical' => 'danger',
                                        'alert' => 'danger', 'emergency' => 'danger',
                                        'warning' => 'warning', 'info' => 'info',
                                        'debug' => 'secondary', 'notice' => 'secondary',
                                    ];
                                    $cls = $levelMap[$log['level'] ?? 'info'] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><span class="badge text-bg-<?php echo e($cls); ?>"><?php echo e($log['level'] ?? '—'); ?></span></td>
                                    <td class="text-nowrap text-muted small"><?php echo e($log['date'] ?? ''); ?></td>
                                    <td style="word-break:break-word">
                                        <?php echo e(trim($log['text'] ?? '')); ?>

                                        <?php if(!empty($log['in_file'])): ?>
                                            <div class="text-muted small mt-1"><?php echo e(trim($log['in_file'])); ?></div>
                                        <?php endif; ?>
                                        <?php if(!empty($log['stack'])): ?>
                                            <details class="mt-1">
                                                <summary class="small text-primary" style="cursor:pointer">Stack trace</summary>
                                                <pre class="small bg-dark text-light p-2 mt-1 mb-0" style="white-space:pre-wrap;overflow:auto;max-height:300px"><?php echo e(trim($log['stack'])); ?></pre>
                                            </details>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-center text-muted py-4"><?php echo e(ui('no_log_entries')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/monitoring/logs/index.blade.php ENDPATH**/ ?>