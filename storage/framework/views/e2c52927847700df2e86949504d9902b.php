<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?php echo e(ui('permissions')); ?></h3>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('permission.create')): ?>
    <a href="<?php echo e(route('permissions.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> <?php echo e(ui('new_permission')); ?></a>
    <?php endif; ?>
</div>

<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
    <form method="GET" class="d-flex flex-grow-1" style="max-width:420px">
        <div class="input-group input-group-sm shadow-sm w-100">
            <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control bg-body border-0" placeholder="<?php echo e(ui('search_permission_name')); ?>" value="<?php echo e(request('q')); ?>" aria-label="<?php echo e(ui('search')); ?>">
            <button class="btn btn-primary px-3" type="submit">Search</button>
        </div>
    </form>
    <?php echo $__env->make('partials.bulk-actions', ['bulkRoute' => route('permissions.bulk'), 'canSoft' => auth()->user()->can('permission.delete'), 'canForce' => auth()->user()->can('permission.force-delete')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:38px"><input class="form-check-input" type="checkbox" form="bulk-form" id="bulk-select-all"></th>
                    <th>#</th>
                    <?php if (isset($component)) { $__componentOriginal3c1df23c66879bbdd25946c6c08cdc07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c1df23c66879bbdd25946c6c08cdc07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sortable-th','data' => ['label' => ''.e(ui('name')).'','column' => 'name','sort' => request('sort'),'dir' => request('dir', 'asc')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(ui('name')).'','column' => 'name','sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('sort')),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('dir', 'asc'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sortable-th','data' => ['label' => ''.e(ui('guard')).'','column' => 'guard_name','sort' => request('sort'),'dir' => request('dir', 'asc')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(ui('guard')).'','column' => 'guard_name','sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('sort')),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('dir', 'asc'))]); ?>
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
                    <th class="text-end"><?php echo e(ui('action')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($perm->trashed() ? 'row-deleted' : ''); ?>">
                    <td><input class="form-check-input" type="checkbox" form="bulk-form" name="ids[]" value="<?php echo e($perm->id); ?>"></td>
                    <td class="text-muted"><?php echo e($permissions->firstItem() + $loop->index); ?></td>
                    <td><?php echo e($perm->name); ?></td>
                    <td>
                        <span class="badge text-bg-secondary"><?php echo e($perm->guard_name); ?></span>
                                <?php if($perm->trashed()): ?><span class="badge text-bg-danger"><?php echo e(ui('deleted')); ?></span><?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if (isset($component)) { $__componentOriginalf9332b595ad3d3a806f9da4dda8769dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf9332b595ad3d3a806f9da4dda8769dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-buttons','data' => ['edit' => $perm->trashed() ? null : route('permissions.edit', $perm),'restore' => $perm->trashed() && auth()->user()->can('permission.restore') ? route('permissions.restore', $perm->id) : null,'delete' => $perm->trashed() ? null : route('permissions.destroy', $perm),'forceDelete' => $perm->trashed() && auth()->user()->can('permission.force-delete') ? route('permissions.forceDelete', $perm->id) : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['edit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($perm->trashed() ? null : route('permissions.edit', $perm)),'restore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($perm->trashed() && auth()->user()->can('permission.restore') ? route('permissions.restore', $perm->id) : null),'delete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($perm->trashed() ? null : route('permissions.destroy', $perm)),'forceDelete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($perm->trashed() && auth()->user()->can('permission.force-delete') ? route('permissions.forceDelete', $perm->id) : null)]); ?>
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
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center text-muted py-4"><?php echo e(ui('no_permissions')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php echo $__env->make('partials.pagination-info', ['items' => $permissions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo e($permissions->links()); ?>

<?php echo $__env->make('partials.modals.delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.modals.force-delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/access/permissions/index.blade.php ENDPATH**/ ?>