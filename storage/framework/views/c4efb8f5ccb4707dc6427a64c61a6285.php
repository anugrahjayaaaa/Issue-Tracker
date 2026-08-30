<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'edit' => null,
    'delete' => null,
    'restore' => null,
    'forceDelete' => null,
    'deleteDisabled' => false,
    'restoreLabel' => 'Restore',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'edit' => null,
    'delete' => null,
    'restore' => null,
    'forceDelete' => null,
    'deleteDisabled' => false,
    'restoreLabel' => 'Restore',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="btn-group" role="group" aria-label="Actions">
    <?php if($edit): ?>
        <a href="<?php echo e($edit); ?>" class="btn btn-sm btn-light border rounded-2" data-bs-toggle="tooltip" data-bs-title="Edit" aria-label="Edit" style="min-width:38px">
            <i class="bi bi-pencil"></i>
        </a>
    <?php endif; ?>

    <?php if($restore): ?>
        <form action="<?php echo e($restore); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-sm btn-light border text-success rounded-2" data-bs-toggle="tooltip" data-bs-title="<?php echo e($restoreLabel); ?>" aria-label="<?php echo e($restoreLabel); ?>" style="min-width:38px">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </form>
    <?php endif; ?>

    <?php if($delete && !$deleteDisabled): ?>
        <button type="button" class="btn btn-sm btn-light border text-danger rounded-2" title="Delete" aria-label="Delete"
                style="min-width:38px"
                data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="<?php echo e($delete); ?>">
            <i class="bi bi-trash"></i>
        </button>
    <?php endif; ?>

    <?php if($forceDelete): ?>
        <button type="button" class="btn btn-sm btn-light border text-danger rounded-2" title="Delete permanently" aria-label="Delete permanently"
                style="min-width:38px"
                data-bs-toggle="modal" data-bs-target="#forceDeleteModal" data-action="<?php echo e($forceDelete); ?>">
            <i class="bi bi-x-circle"></i>
        </button>
    <?php endif; ?>
</div>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/components/action-buttons.blade.php ENDPATH**/ ?>