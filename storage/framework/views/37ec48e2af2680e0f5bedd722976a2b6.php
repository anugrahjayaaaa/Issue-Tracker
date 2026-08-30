<div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(ui('permanently_delete')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><?php echo e(ui('force_delete_confirm_body')); ?></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(ui('cancel')); ?></button>
                <form id="forceDeleteModalForm" method="POST"><?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-danger"><?php echo e(ui('permanently_delete')); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/partials/modals/force-delete-modal.blade.php ENDPATH**/ ?>