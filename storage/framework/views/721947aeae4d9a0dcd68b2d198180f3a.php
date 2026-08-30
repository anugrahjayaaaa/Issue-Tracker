<?php if(isset($items) && method_exists($items, 'firstItem')): ?>
<div class="text-muted small mb-2">
    Showing <?php echo e($items->firstItem()); ?>–<?php echo e($items->lastItem()); ?> <?php echo e(ui('of')); ?> <?php echo e($items->total()); ?>

</div>
<?php endif; ?>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/partials/pagination-info.blade.php ENDPATH**/ ?>