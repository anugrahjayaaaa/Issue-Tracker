<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['bulkRoute', 'canSoft' => true, 'canForce' => true]));

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

foreach (array_filter((['bulkRoute', 'canSoft' => true, 'canForce' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($canSoft || $canForce): ?>
<style>
    /* soft-deleted rows: subtle, readable — not the harsh table-secondary */
    tr.row-deleted td { background: rgba(220, 53, 69, 0.06); }
    tr.row-deleted td:first-child { box-shadow: inset 3px 0 0 #dc3545; }
</style>
<form id="bulk-form" method="POST" action="<?php echo e($bulkRoute); ?>" class="mb-3">
    <?php echo csrf_field(); ?>
    <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
        <span class="text-muted small me-auto" id="bulk-count"></span>
        <select name="action" class="form-select form-select-sm" style="width:auto" required>
            <option value="" disabled selected><?php echo e(ui('bulk_action')); ?></option>
            <?php if($canSoft): ?><option value="soft"><?php echo e(ui('soft_delete')); ?></option><?php endif; ?>
            <?php if($canForce): ?><option value="force"><?php echo e(ui('permanently_delete')); ?></option><?php endif; ?>
        </select>
        <button type="button" id="bulk-apply" class="btn btn-sm btn-danger"><?php echo e(ui('apply')); ?></button>
    </div>
</form>


<div class="modal fade" id="bulkConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(ui('confirm_bulk_delete_title')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bulkConfirmBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(ui('cancel')); ?></button>
                <button type="button" class="btn btn-danger" id="bulkConfirmDelete"><?php echo e(ui('delete')); ?></button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Event delegation: immune to init-timing / element-not-found issues.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bulk-form');
    if (!form) return;
    const count = document.getElementById('bulk-count');
    const rows = () => document.querySelectorAll('input[name="ids[]"]');
    const applyBtn = document.getElementById('bulk-apply');
    const modalEl = document.getElementById('bulkConfirmModal');
    const modalBody = document.getElementById('bulkConfirmBody');
    const deleteBtn = document.getElementById('bulkConfirmDelete');

    const sync = () => {
        const boxes = rows();
        const checked = [...boxes].filter(b => b.checked).length;
        if (count) count.textContent = checked ? `(${checked} <?php echo e(ui('selected')); ?>)` : '';
        const all = document.getElementById('bulk-select-all');
        if (all) all.indeterminate = checked > 0 && checked < boxes.length;
        if (applyBtn) applyBtn.disabled = checked === 0;
    };

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'bulk-select-all') {
            rows().forEach(b => { b.checked = e.target.checked; });
            sync();
        } else if (e.target && e.target.name === 'ids[]') {
            sync();
        }
    });

    if (applyBtn && modalEl) {
        applyBtn.addEventListener('click', () => {
            const checked = [...rows()].filter(b => b.checked).length;
            if (checked === 0) return;
            const action = form.querySelector('select[name="action"]').value;
            const label = action === 'force' ? '<?php echo e(ui('permanently_delete')); ?>' : '<?php echo e(ui('soft_delete')); ?>';
            modalBody.textContent = `<?php echo e(ui('confirm_bulk_delete_body')); ?>`.replace(':count', checked).replace(':action', label);
            new bootstrap.Modal(modalEl).show();
        });
    }
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            form.submit();
        });
    }

    sync();
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/partials/bulk-actions.blade.php ENDPATH**/ ?>