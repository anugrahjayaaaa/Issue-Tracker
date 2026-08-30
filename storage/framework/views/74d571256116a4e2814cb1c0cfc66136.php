
<nav class="app-header navbar navbar-expand bg-body border-bottom">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="sidebar" role="button" title="Toggle sidebar">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>
        </ul>

        
        <ul class="navbar-nav ms-auto flex-grow-1 px-2 d-none d-md-flex">
            <li class="nav-item w-100 position-relative" style="max-width:420px">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
                    <input type="search" id="menu-search" class="form-control bg-body border-0" placeholder="<?php echo e(ui('search_menu')); ?>" autocomplete="off" aria-label="<?php echo e(ui('search_menu')); ?>" aria-expanded="false" aria-controls="menu-search-results">
                </div>
                <ul id="menu-search-results" class="dropdown-menu w-100 py-1 shadow-sm" style="display:none;max-height:320px;overflow:auto"></ul>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            
            <li class="nav-item">
                <button class="btn btn-link nav-link px-2" id="theme-toggle" type="button" title="Toggle light/dark" aria-label="Toggle theme">
                    <i class="bi bi-moon-stars fs-5" id="theme-icon"></i>
                </button>
            </li>

            
            <li class="nav-item dropdown">
                <a class="nav-link position-relative px-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:36px;height:36px;background:var(--lbp-surface-2);border-color:var(--lbp-border)">
                        <i class="bi bi-bell fs-5"></i>
                    </span>
                    <?php if(($notifications['unread'] ?? 0) > 0): ?>
                        <span class="position-absolute badge rounded-pill text-bg-danger" style="top:2px;right:2px;font-size:10px;padding:2px 5px"><?php echo e($notifications['unread']); ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg py-0" style="width:320px">
                    <span class="dropdown-item dropdown-header d-flex justify-content-between"><span>Recent activity</span><span class="badge bg-primary-subtle text-primary"><?php echo e($notifications['items']->count()); ?></span></span>
                    <div class="dropdown-divider my-0"></div>
                    <div style="max-height:280px;overflow:auto">
                    <?php $__empty_1 = true; $__currentLoopData = $notifications['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('audit.index')); ?>" class="dropdown-item py-2 border-bottom" style="white-space:normal">
                            <div class="d-flex gap-2">
                                <i class="bi bi-activity text-primary mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="small"><?php echo e(\Illuminate\Support\Str::limit($n->data['label'] ?? ($n->data['action'] ?? 'notification'), 40)); ?></div>
                                    <div class="text-muted" style="font-size:11px"><?php echo e($n->created_at->diffForHumans()); ?></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="dropdown-item text-muted">No activity yet.</span>
                    <?php endif; ?>
                    </div>
                    <div class="dropdown-divider my-0"></div>
                    <a href="<?php echo e(route('notifications.index')); ?>" class="dropdown-item dropdown-footer text-center text-primary">View all notifications</a>
                </div>
            </li>

            
            <?php if(auth()->guard()->check()): ?>
            <details class="nav-item dropdown user-menu">
                <summary class="nav-link d-flex align-items-center gap-2" aria-label="User menu">
                    <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:34px;height:34px"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>
                    <span class="d-none d-md-inline fw-medium"><?php echo e(auth()->user()->name); ?></span>
                    <i class="bi bi-chevron-down small opacity-75"></i>
                </summary>
                <ul class="dropdown-menu dropdown-menu-end py-1" style="min-width:220px">
                    <li class="dropdown-item-text d-flex align-items-center gap-2 pb-2">
                        <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px;height:38px"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>
                        <div class="text-truncate">
                            <div class="fw-medium"><?php echo e(auth()->user()->name); ?></div>
                            <div class="text-muted small text-truncate" style="max-width:150px"><?php echo e(auth()->user()->email); ?></div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>
                    <li><a href="<?php echo e(route('profile.show')); ?>" class="dropdown-item py-2"><i class="bi bi-person me-2"></i> Profile</a></li>
                    <li><a href="<?php echo e(route('sessions.index')); ?>" class="dropdown-item py-2"><i class="bi bi-pc-display me-2"></i> <?php echo e(__('messages.sessions')); ?></a></li>
                    <li><hr class="dropdown-divider my-0"></li>
                    <li class="dropdown-item-text pb-1">
                        <div class="text-muted small text-uppercase px-2"><?php echo e(__('messages.language')); ?></div>
                        <div class="d-flex gap-1 px-2 pt-1">
                            <form method="POST" action="<?php echo e(route('locale.update')); ?>" class="flex-fill">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="locale" value="en">
                                <button type="submit" class="btn btn-sm <?php echo e(app()->getLocale() === 'en' ? 'btn-primary' : 'btn-outline-secondary'); ?> w-100"><?php echo e(__('messages.english')); ?></button>
                            </form>
                            <form method="POST" action="<?php echo e(route('locale.update')); ?>" class="flex-fill">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="locale" value="id">
                                <button type="submit" class="btn btn-sm <?php echo e(app()->getLocale() === 'id' ? 'btn-primary' : 'btn-outline-secondary'); ?> w-100"><?php echo e(__('messages.indonesian')); ?></button>
                            </form>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="dropdown-item py-2 text-danger w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </details>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<?php /**PATH /Users/anugrahjayasakti/Projects/laravel/issue-tracker/resources/views/partials/layout/header.blade.php ENDPATH**/ ?>