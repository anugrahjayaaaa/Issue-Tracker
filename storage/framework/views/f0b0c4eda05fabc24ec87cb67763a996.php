<?php $__env->startSection('content'); ?>
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1"><?php echo e(config('app.name', 'Laravel')); ?></span></div>
        <div class="card-body">
            <p class="login-box-msg"><?php echo e(ui('sign_in_with')); ?></p>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger py-2"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <?php if(session('status')): ?>
                <div class="alert alert-success py-2"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="input-group mb-3">
                    <input type="text" name="identifier" class="form-control <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo e(ui('email_or_username')); ?>" value="<?php echo e(old('identifier')); ?>" required autofocus>
                    <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="input-group-text"><i class="bi bi-person"></i></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo e(ui('password')); ?>" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <button type="button" class="input-group-text" id="toggle-password" aria-label="Show password" style="cursor:pointer">
                        <i class="bi bi-eye" id="password-icon"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember"><?php echo e(ui('remember_me')); ?></label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary w-100"><?php echo e(ui('sign_in')); ?></button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2">
                <a href="<?php echo e(route('password.request')); ?>"><?php echo e(ui('forgot_your_password')); ?></a>
            </p>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Toggle password visibility. State is DOM-only (no persistence / no server state).
    (function () {
        const pwd = document.getElementById('password');
        const btn = document.getElementById('toggle-password');
        const icon = document.getElementById('password-icon');
        btn.addEventListener('click', function () {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/auth/login.blade.php ENDPATH**/ ?>