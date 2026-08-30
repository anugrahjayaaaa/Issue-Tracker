<div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ui('permanently_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">{{ ui('force_delete_confirm_body') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ ui('cancel') }}</button>
                <form id="forceDeleteModalForm" method="POST">@csrf
                    <button type="submit" class="btn btn-danger">{{ ui('permanently_delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
