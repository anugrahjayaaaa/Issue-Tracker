<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.flash-message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<h3><?php echo e(ui('audit_log')); ?></h3>
<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label small text-muted mb-1"><?php echo e(ui('action')); ?></label>
        <select name="action" class="form-select form-select-sm">
            <option value=""><?php echo e(ui('all_actions')); ?></option>
            <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($a); ?>" <?php echo e(request('action') == $a ? 'selected' : ''); ?>><?php echo e($a); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small text-muted mb-1"><?php echo e(ui('from')); ?></label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?php echo e(request('from')); ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label small text-muted mb-1"><?php echo e(ui('to')); ?></label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?php echo e(request('to')); ?>">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> <?php echo e(ui('filter')); ?></button>
        <a href="<?php echo e(route('audit.export', request()->only(['action','causer','from','to']))); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i> CSV</a>
        <?php if(request()->anyFilled(['action','from','to'])): ?>
            <a href="<?php echo e(route('audit.index')); ?>" class="btn btn-link btn-sm text-muted" title="Clear filters"><i class="bi bi-x-circle"></i></a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th><?php echo e(ui('time')); ?></th><th><?php echo e(ui('action')); ?></th><th><?php echo e(ui('causer')); ?></th><th><?php echo e(ui('subject')); ?></th><th>IP</th><th></th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-muted"><?php echo e($activities->firstItem() + $loop->index); ?></td>
                    <td class="text-muted small"><?php echo e($log->created_at->format('Y-m-d H:i')); ?></td>
                    <td>
                        <?php
                            $actionColor = [
                                'role_created' => 'success', 'permission_created' => 'success', 'user_created' => 'success', 'login_success' => 'success',
                                'role_updated' => 'warning', 'permission_updated' => 'warning', 'user_updated' => 'warning',
                                'role_deleted' => 'danger', 'permission_deleted' => 'danger', 'user_deleted' => 'danger', 'login_failed' => 'danger',
                                'user_restored' => 'info',
                                'logout' => 'secondary', 'password_reset' => 'secondary', 'email_verified' => 'secondary',
                            ];
                            $c = $actionColor[$log->description] ?? 'dark';
                        ?>
                        <span class="badge text-bg-<?php echo e($c); ?>"><?php echo e($log->description); ?></span>
                    </td>
                    <td><?php echo e($log->causer->username ?? ($log->properties['identifier'] ?? '-')); ?></td>
                    <td>
                        <span class="text-muted small"><?php echo e(class_basename($log->subject_type)); ?></span>
                        <?php if($log->subject): ?>
                            <span class="fw-semibold text-body"><?php echo e($log->subject->name ?? $log->subject->username ?? ''); ?></span>
                        <?php endif; ?>
                        <span class="text-muted">#<?php echo e($log->subject_id); ?></span>
                    </td>
                    <td class="text-muted small"><?php echo e($log->properties['ip'] ?? '-'); ?></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-light border rounded-2 audit-detail-btn"
                                data-bs-toggle="modal" data-bs-target="#auditDetailModal"
                                data-action="<?php echo e($log->description); ?>"
                                data-time="<?php echo e($log->created_at->format('Y-m-d H:i:s')); ?>"
                                data-causer="<?php echo e($log->causer->username ?? ($log->properties['identifier'] ?? '-')); ?>"
                                data-ip="<?php echo e($log->properties['ip'] ?? ''); ?>"
                                data-agent="<?php echo e($log->properties['user_agent'] ?? ''); ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <script type="application/json" class="audit-detail-data"><?php echo json_encode([
                            'old' => $log->properties['old'] ?? new \stdClass(), 'new' => $log->properties['new'] ?? new \stdClass(), ]) ?></script>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted py-4"><?php echo e(ui('no_activity')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php echo $__env->make('partials.pagination-info', ['items' => $activities], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo e($activities->links()); ?>



<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditDetailAction"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body small">
                <dl class="row mb-2">
                    <dt class="col-4 text-muted"><?php echo e(ui('time')); ?></dt><dd class="col-8" id="auditDetailTime"></dd>
                    <dt class="col-4 text-muted"><?php echo e(ui('causer')); ?></dt><dd class="col-8" id="auditDetailCauser"></dd>
                    <dt class="col-4 text-muted">IP</dt><dd class="col-8" id="auditDetailIp"></dd>
                    <dt class="col-4 text-muted"><?php echo e(ui('user_agent')); ?></dt><dd class="col-8 text-break" id="auditDetailAgent"></dd>
                </dl>
                <div id="auditDetailChanges"></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('auditDetailModal')?.addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;
    document.getElementById('auditDetailAction').textContent = b.dataset.action;
    document.getElementById('auditDetailTime').textContent = b.dataset.time;
    document.getElementById('auditDetailCauser').textContent = b.dataset.causer;
    document.getElementById('auditDetailIp').textContent = b.dataset.ip || '-';
    document.getElementById('auditDetailAgent').textContent = b.dataset.agent || '-';

    const changes = JSON.parse(b.parentElement.querySelector('.audit-detail-data')?.textContent || '{}');
    const oldMap = changes.old ?? {};
    const newMap = changes.new ?? {};
    const keys = [...new Set([...Object.keys(oldMap), ...Object.keys(newMap)])];
    const box = document.getElementById('auditDetailChanges');
    if (!keys.length) { box.innerHTML = '<p class="text-muted mb-0"><?php echo e(ui('no_field_changes')); ?></p>'; return; }

    const cell = (v) => v === null ? '<em class="text-muted">null</em>' : String(v);
    let html = '<table class="table table-sm mb-0"><thead><tr><th><?php echo e(ui('field')); ?></th><th>Old</th><th>New</th></tr></thead><tbody>';
    keys.forEach(k => {
        html += `<tr><td class="text-capitalize text-muted">${k}</td><td class="text-break">${cell(oldMap[k])}</td><td class="text-break">${cell(newMap[k])}</td></tr>`;
    });
    box.innerHTML = html + '</tbody></table>';
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/monitoring/audit/index.blade.php ENDPATH**/ ?>