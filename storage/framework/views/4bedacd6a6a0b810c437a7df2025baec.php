<div class="modal fade" id="featureToggleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(ui('confirm_feature_change')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="featureToggleBody"><?php echo e(ui('confirm_feature_change_body')); ?></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(ui('cancel')); ?></button>
                <form id="featureToggleForm" method="POST"><?php echo csrf_field(); ?>
                    <input type="hidden" name="enabled" id="featureToggleEnabled" value="1">
                    <button type="submit" class="btn btn-primary" id="featureToggleSubmit"><?php echo e(ui('confirm')); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/partials/modals/feature-toggle-modal.blade.php ENDPATH**/ ?>