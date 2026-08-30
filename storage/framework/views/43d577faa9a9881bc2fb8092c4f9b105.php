<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<h3><?php echo e(ui('api_tokens')); ?></h3>

<?php if($newToken): ?>
<div class="alert alert-success d-flex align-items-start justify-content-between gap-2" id="newTokenAlert">
    <div>
        <strong><?php echo e(ui('new_token_copy_now')); ?></strong>
        <code class="d-block mt-1" id="newTokenValue"><?php echo e($newToken); ?></code>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="copyTokenBtn" data-clipboard-target="#newTokenValue">
        <i class="bi bi-clipboard"></i> <?php echo e(ui('copy')); ?>

    </button>
</div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('api-tokens.store')); ?>" class="row g-2 mb-3">
    <?php echo csrf_field(); ?>
    <div class="col-md-4">
        <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo e(ui('token_name')); ?>" required>
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary"><i class="bi bi-plus-circle"></i> <?php echo e(ui('create')); ?></button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th><?php echo e(ui('name')); ?></th><th><?php echo e(ui('abilities')); ?></th><th><?php echo e(ui('created')); ?></th><th><?php echo e(ui('last_used')); ?></th><th></th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $tokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $token): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($token->name); ?></td>
                    <td><?php echo e(implode(', ', $token->abilities)); ?></td>
                    <td><?php echo e($token->created_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                    <td><?php echo e($token->last_used_at?->diffForHumans() ?? 'never'); ?></td>
                    <td>
                        <form method="POST" action="<?php echo e(route('api-tokens.destroy', $token)); ?>" onsubmit="return confirm('<?php echo e(ui('revoke_this_token')); ?>')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-muted"><?php echo e(ui('no_tokens_yet')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('copyTokenBtn')?.addEventListener('click', function () {
    const text = document.getElementById('newTokenValue').textContent.trim();
    const btn = this;
    const done = () => {
        const icon = btn.querySelector('i');
        icon.className = 'bi bi-check2';
        btn.textContent = ' <?php echo e(ui('copied')); ?>';
        setTimeout(() => { icon.className = 'bi bi-clipboard'; btn.textContent = ' <?php echo e(ui('copy')); ?>'; }, 1500);
    };
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => {});
    } else {
        const ta = document.createElement('textarea'); // ponytail: fallback for non-secure context
        ta.value = text; document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        ta.remove();
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/settings/api-tokens/index.blade.php ENDPATH**/ ?>