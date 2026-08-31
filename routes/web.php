<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BillingAdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LogViewerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectImageController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\IssueImageController;
use App\Http\Controllers\CommentImageController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Health check (no auth)
Route::get('/up', fn () => response()->json(['status' => 'ok']));

// Email verification (web) — built-in signed URL, name required by VerifyEmail notification
Route::get('/email/verify/{id}/{hash}', [LoginController::class, 'verify'])
    ->name('verification.verify');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:10,15')->name('login.store');

    // Password reset (guest only). Token state lives in DB table `password_reset_tokens`.
    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:10,15')->name('password.email');
    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class, 'update'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stub routes for sidebar links (filled in by later phases: users/roles/permissions/audit/profile/sessions/api-tokens)
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware(['can:user.view', 'feature:users']);
    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middleware(['can:user.view', 'feature:users']);
    Route::post('/users/bulk', [UserController::class, 'bulk'])->name('users.bulk')->middleware(['can:user.delete', 'feature:users']);
    Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->middleware(['can:user.restore', 'feature:users']);
    Route::post('/users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete')->middleware(['can:user.force-delete', 'feature:users']);
    Route::post('/users/{user}/lock', [UserController::class, 'lock'])->name('users.lock')->middleware(['can:user.lock', 'feature:users']);
    Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock')->middleware(['can:user.lock', 'feature:users']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'sendResetPassword'])->name('users.reset-password')->middleware(['can:user.edit', 'feature:users']);

    Route::resource('roles', RoleController::class)->middleware(['can:role.view', 'feature:roles']);
    Route::post('/roles/bulk', [RoleController::class, 'bulk'])->name('roles.bulk')->middleware(['can:role.delete', 'feature:roles']);
    Route::post('/roles/{role}/restore', [RoleController::class, 'restore'])->name('roles.restore')->middleware(['can:role.restore', 'feature:roles']);
    Route::post('/roles/{role}/force-delete', [RoleController::class, 'forceDelete'])->name('roles.forceDelete')->middleware(['can:role.force-delete', 'feature:roles']);

    Route::resource('permissions', PermissionController::class)->middleware(['can:permission.view', 'feature:permissions']);
    Route::post('/permissions/bulk', [PermissionController::class, 'bulk'])->name('permissions.bulk')->middleware(['can:permission.delete', 'feature:permissions']);
    Route::post('/permissions/{permission}/restore', [PermissionController::class, 'restore'])->name('permissions.restore')->middleware(['can:permission.restore', 'feature:permissions']);
    Route::post('/permissions/{permission}/force-delete', [PermissionController::class, 'forceDelete'])->name('permissions.forceDelete')->middleware(['can:permission.force-delete', 'feature:permissions']);

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index')->middleware(['can:session.view', 'feature:sessions']);
    Route::post('/sessions/logout-others', [SessionController::class, 'logoutOthers'])->name('sessions.logoutOthers')->middleware(['can:session.revoke', 'feature:sessions']);

    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index')->middleware(['can:audit.view', 'feature:audit']);
    Route::get('/audit/export', [AuditController::class, 'export'])->name('audit.export')->middleware(['can:audit.view', 'feature:audit']);
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware(['can:audit.view', 'feature:audit']);
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index')->middleware(['can:api-token.view', 'feature:api-tokens']);
    Route::resource('api-tokens', ApiTokenController::class)
        ->only(['store', 'destroy'])
        ->middleware(['can:api-token.create', 'feature:api-tokens']);

    // Web log viewer
    Route::get('/logs', [LogViewerController::class, 'index'])
        ->name('logs.index')->middleware(['can:logs.view', 'feature:logs']);

    // Feature flags management (self-gated: feature.manage permission)
    Route::get('/features', [FeatureController::class, 'index'])->name('features.index')->middleware('can:feature.manage');
    Route::post('/features/{slug}/toggle', [FeatureController::class, 'toggle'])->name('features.toggle')->middleware('can:feature.manage');

    // Plan management (full CRUD, custom slug/price/limits/features — doc §9b)
    Route::resource('plans', PlanController::class)->middleware(['can:feature.manage', 'feature:plans']);

    // Billing: user portal + checkout (dummy mode completes at once)
    Route::prefix('billing')->middleware('feature:billing')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/checkout', [BillingController::class, 'checkout'])
            ->name('billing.checkout')->middleware('throttle:10,1');
        Route::post('/cancel', [BillingController::class, 'cancel'])
            ->name('billing.cancel')->middleware('can:billing.cancel');
        Route::get('/invoice/{payment}', [BillingController::class, 'invoice'])
            ->name('billing.invoice');
    });

    // Billing admin: KPIs + analytics (separate popular page, gated by billing.view)
    Route::prefix('admin/billing')->middleware(['can:billing.view', 'feature:billing'])->group(function () {
        Route::get('/', [BillingAdminController::class, 'index'])->name('admin.billing.index');
    });

    // ── Issue Tracker module (feature:issues) ──────────────────────────────
    Route::prefix('projects')->middleware(['can:project.manage', 'feature:issues'])->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index')->withoutMiddleware('can:project.manage');
        Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show')->withoutMiddleware('can:project.manage');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        Route::post('/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
        Route::put('/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
        Route::delete('/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

        Route::post('/{project}/labels', [LabelController::class, 'store'])->name('projects.labels.store');
        Route::put('/{project}/labels/{label}', [LabelController::class, 'update'])->name('projects.labels.update');
        Route::delete('/{project}/labels/{label}', [LabelController::class, 'destroy'])->name('projects.labels.destroy');

        // rich-text description image upload (scoped to project folder)
        Route::post('/{project}/image', [ProjectImageController::class, 'store'])->name('projects.image.upload');
    });

    // Issues (project-scoped): read = any member; write = lead/member (enforced in Form Requests)
    Route::prefix('issues')->middleware(['can:issue.view', 'feature:issues'])->group(function () {
        Route::get('/', [IssueController::class, 'index'])->name('issues.index');
        Route::get('/board', [IssueController::class, 'board'])->name('issues.board');
        Route::get('/create', [IssueController::class, 'create'])->name('issues.create');
        Route::post('/', [IssueController::class, 'store'])->name('issues.store')->middleware('can:issue.create');
        Route::get('/{issue}', [IssueController::class, 'show'])->name('issues.show');
        Route::get('/{issue}/edit', [IssueController::class, 'edit'])->name('issues.edit')->middleware('can:issue.edit');
        Route::put('/{issue}', [IssueController::class, 'update'])->name('issues.update')->middleware('can:issue.edit');
        Route::post('/{issue}/status', [IssueController::class, 'changeStatus'])->name('issues.status')->middleware('can:issue.edit');
        Route::delete('/{issue}', [IssueController::class, 'destroy'])->name('issues.destroy')->middleware('can:issue.delete');
        // rich-text description image upload (scoped to issue folder)
        Route::post('/{issue}/image', [IssueImageController::class, 'store'])->name('issues.image.upload');
        Route::post('/comments/{comment}/image', [CommentImageController::class, 'store'])->name('comments.image.upload');

        // Comments (project-scoped, same gate as issues)
        Route::post('/{issue}/comments', [CommentController::class, 'store'])->name('issues.comments.store')->middleware('can:comment.create');
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('issues.comments.update')->middleware('can:comment.edit');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('issues.comments.destroy')->middleware('can:comment.delete');
        Route::post('/{issue}/attachments', [CommentController::class, 'attach'])->name('issues.attachments.store')->middleware('can:comment.create');
    });

    Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

    // Translations management (under Settings, gated by RBAC + feature flag)
    Route::prefix('settings')->middleware(['can:translation.view', 'feature:translations'])->group(function () {
        Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
        Route::get('/translations/{languageLine}/edit', [TranslationController::class, 'edit'])->name('translations.edit')->middleware('can:translation.edit');
        Route::put('/translations/{languageLine}', [TranslationController::class, 'update'])->name('translations.update')->middleware('can:translation.edit');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/email/verify/resend', [LoginController::class, 'resendVerification'])->name('verification.resend');
});

// PG webhook — outside auth (the gateway calls this, no session). CSRF excluded in bootstrap/app.php.
Route::post('/billing/webhook', [BillingController::class, 'webhook'])->name('billing.webhook');

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : view('auth.login'));
