<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?php echo e(ui('users')); ?></h3>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('user.create')): ?>
    <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> <?php echo e(ui('new_user')); ?></a>
    <?php endif; ?>
</div>

<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
    <form method="GET" class="d-flex flex-grow-1" style="max-width:420px">
        <div class="input-group input-group-sm shadow-sm w-100">
            <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control bg-body border-0" placeholder="<?php echo e(ui('search_name_username_email')); ?>" value="<?php echo e(request('q')); ?>">
            <button class="btn btn-primary px-3" type="submit"><?php echo e(ui('search')); ?></button>
        </div>
    </form>
    <?php echo $__env->make('partials.bulk-actions', ['bulkRoute' => route('users.bulk'), 'canSoft' => auth()->user()->can('user.delete'), 'canForce' => auth()->user()->can('user.force-delete')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle m-0">
            <thead>
                <tr>
                    <th style="width:38px"><input class="form-check-input" type="checkbox" form="bulk-form" id="bulk-select-all"></th>
                    <th>#</th>
                    <?php if (isset($component)) { $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sortable-th','data' => ['label' => ''.e(ui('user')).'','column' => 'name','sort' => request('sort'),'dir' => request('dir', 'asc')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(ui('user')).'','column' => 'name','sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('sort')),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('dir', 'asc'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07)): ?>
<?php $attributes = $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07; ?>
<?php unset($__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c1df23c66879bbdd25946c6c08cdc07)): ?>
<?php $component = $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07; ?>
<?php unset($__componentOriginal3c1df23c66879bbdd25946c6c08cdc07); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sortable-th','data' => ['label' => ''.e(ui('username')).'','column' => 'username','sort' => request('sort'),'dir' => request('dir', 'asc')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(ui('username')).'','column' => 'username','sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('sort')),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('dir', 'asc'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07)): ?>
<?php $attributes = $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07; ?>
<?php unset($__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c1df23c66879bbdd25946c6c08cdc07)): ?>
<?php $component = $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07; ?>
<?php unset($__componentOriginal3c1df23c66879bbdd25946c6c08cdc07); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sortable-th','data' => ['label' => ''.e(ui('email')).'','column' => 'email','sort' => request('sort'),'dir' => request('dir', 'asc')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(ui('email')).'','column' => 'email','sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('sort')),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('dir', 'asc'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07)): ?>
<?php $attributes = $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07; ?>
<?php unset($__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c1df23c66879bbdd25946c6c08cdc07)): ?>
<?php $component = $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07; ?>
<?php unset($__componentOriginal3c1df23c66879bbdd25946c6c08cdc07); ?>
<?php endif; ?>
                    <th><?php echo e(ui('roles')); ?></th><th><?php echo e(ui('status')); ?></th><th class="text-end"><?php echo e(ui('action')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($user->trashed() ? 'row-deleted' : ''); ?>">
                    <td><input class="form-check-input" type="checkbox" form="bulk-form" name="ids[]" value="<?php echo e($user->id); ?>"></td>
                    <td class="text-muted"><?php echo e($users->firstItem() + $loop->index); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px"><?php echo e(strtoupper(substr($user->name,0,1))); ?></span>
                            <div>
                                <div class="fw-medium"><?php echo e($user->name); ?></div>
                                <?php if($user->trashed()): ?><span class="badge text-bg-danger"><?php echo e(ui('deleted')); ?></span><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e($user->username); ?></td>
                    <td class="text-muted"><?php echo e($user->email); ?></td>
                    <td>
                        <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge text-bg-info me-1"><?php echo e($role->name); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($user->roles->isEmpty()): ?><span class="text-muted">-</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if($user->isPermanentlyLocked()): ?>
                            <span class="badge text-bg-danger"><?php echo e(ui('perm_locked')); ?></span>
                        <?php elseif($user->isLocked()): ?>
                            <span class="badge text-bg-warning"><?php echo e(ui('locked')); ?></span>
                        <?php else: ?>
                            <span class="badge text-bg-success"><?php echo e(ui('active')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <?php if (isset($component)) { $__componentOriginalf9332b595ad3d3a806f9da4dda8769dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-buttons','data' => ['edit' => !$user->trashed() ? route('users.edit', $user) : null,'restore' => $user->trashed() && auth()->user()->can('user.restore') ? route('users.restore', $user->id) : null,'delete' => !$user->trashed() && $user->id !== auth()->id() ? route('users.destroy', $user) : null,'forceDelete' => $user->trashed() && auth()->user()->can('user.force-delete') ? route('users.forceDelete', $user->id) : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['edit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$user->trashed() ? route('users.edit', $user) : null),'restore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->trashed() && auth()->user()->can('user.restore') ? route('users.restore', $user->id) : null),'delete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$user->trashed() && $user->id !== auth()->id() ? route('users.destroy', $user) : null),'forceDelete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->trashed() && auth()->user()->can('user.force-delete') ? route('users.forceDelete', $user->id) : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd)): ?>
<?php $attributes = $__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd; ?>
<?php unset($__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf9332b595ad3d3a806f9da4dda8769dd)): ?>
<?php $component = $__componentOriginalf9332b595ad3d3a806f9da4dda8769dd; ?>
<?php unset($__componentOriginalf9332b595ad3d3a806f9da4dda8769dd); ?>
<?php endif; ?>
                            <?php if(!$user->trashed() && $user->id !== auth()->id() && auth()->user()->can('user.edit')): ?>
                            <form method="POST" action="<?php echo e(route('users.reset-password', $user)); ?>" class="d-inline"><?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-light border rounded-2" data-bs-toggle="tooltip" data-bs-title="Send reset password" aria-label="Send reset password" style="min-width:38px">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if(!$user->trashed() && $user->id !== auth()->id() && auth()->user()->can('user.lock')): ?>
                            <?php if($user->isLocked()): ?>
                            <form method="POST" action="<?php echo e(route('users.unlock', $user)); ?>" class="d-inline"><?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-light border rounded-2 text-warning" data-bs-toggle="tooltip" data-bs-title="Unlock account" aria-label="Unlock account" style="min-width:38px">
                                    <i class="bi bi-unlock-fill"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="<?php echo e(route('users.lock', $user)); ?>" class="d-inline"><?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-light border rounded-2 text-danger" data-bs-toggle="tooltip" data-bs-title="Lock account" aria-label="Lock account" style="min-width:38px">
                                    <i class="bi bi-lock-fill"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4"><?php echo e(ui('no_users_found')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php echo $__env->make('partials.pagination-info', ['items' => $users], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo e($users->links()); ?>

<?php echo $__env->make('partials.modals.delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.modals.force-delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/access/users/index.blade.php ENDPATH**/ ?>