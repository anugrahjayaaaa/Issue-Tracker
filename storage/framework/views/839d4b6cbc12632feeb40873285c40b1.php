<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'column', 'sort' => '', 'dir' => 'asc']));

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

foreach (array_filter((['label', 'column', 'sort' => '', 'dir' => 'asc']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $active = $sort === $column;
    $nextDir = ($active && $dir === 'asc') ? 'desc' : 'asc';
    $qs = request()->except(['page']);
    $qs['sort'] = $column;
    $qs['dir'] = $nextDir;
?>
<th>
    <a href="<?php echo e(url()->current() . '?' . http_build_query($qs)); ?>" class="text-decoration-none text-reset">
        <?php echo e($label); ?>

        <?php if($active): ?>
            <i class="bi bi-caret-<?php echo e($dir === 'asc' ? 'up' : 'down'); ?>-fill small"></i>
        <?php else: ?>
            <i class="bi bi-caret-up small text-muted opacity-50"></i>
        <?php endif; ?>
    </a>
</th>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/components/sortable-th.blade.php ENDPATH**/ ?>