<div class="modal fade" id="featureToggleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ui('confirm_feature_change') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="featureToggleBody">{{ ui('confirm_feature_change_body') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ ui('cancel') }}</button>
                <form id="featureToggleForm" method="POST">@csrf
                    <input type="hidden" name="enabled" id="featureToggleEnabled" value="1">
                    <button type="submit" class="btn btn-primary" id="featureToggleSubmit">{{ ui('confirm') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
