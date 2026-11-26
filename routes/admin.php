<?php

use App\Admin\Registry;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\InsightController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PeopleController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\ResourceController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin.surface')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:login');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('two-factor', [AuthController::class, 'challenge'])->name('two-factor.challenge');
        Route::post('two-factor', [AuthController::class, 'verify'])->middleware('throttle:login')->name('two-factor.verify');
        Route::get('two-factor/setup', [AuthController::class, 'setup'])->name('two-factor.setup');
        Route::post('two-factor/confirm', [AuthController::class, 'confirm'])->name('two-factor.confirm');

        Route::middleware('admin.2fa')->group(function () {
            Route::get('/', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

            Route::middleware('permission:users.view')->group(function () {
                Route::get('users', [PeopleController::class, 'users'])->name('users.index');
                Route::get('users/{user}', [PeopleController::class, 'show'])->name('users.show');
            });
            Route::post('users/{user}', [PeopleController::class, 'update'])->middleware('permission:users.manage')->name('users.update');
            Route::post('users/{user}/suspend', [PeopleController::class, 'suspend'])->middleware('permission:users.suspend')->name('users.suspend');
            Route::post('users/{user}/activate', [PeopleController::class, 'activate'])->middleware('permission:users.suspend')->name('users.activate');
            Route::post('users/{user}/staff', [PeopleController::class, 'staff'])->middleware('permission:users.manage')->name('users.staff');
            Route::post('users/{user}/deletion', [PeopleController::class, 'deletion'])->middleware('permission:users.delete')->name('users.deletion');

            Route::middleware('permission:roles.manage')->group(function () {
                Route::get('roles', [PeopleController::class, 'roles'])->name('roles.index');
                Route::post('roles', [PeopleController::class, 'storeRole'])->name('roles.store');
                Route::put('roles/{role}', [PeopleController::class, 'updateRole'])->name('roles.update');
            });

            Route::middleware('permission:support.manage')->prefix('support')->name('support.')->group(function () {
                Route::get('/', [PeopleController::class, 'tickets'])->name('index');
                Route::get('{ticket}', [PeopleController::class, 'ticket'])->name('show');
                Route::post('{ticket}', [PeopleController::class, 'reply'])->name('reply');
            });

            Route::middleware('permission:community.moderate')->prefix('community')->name('community.')->group(function () {
                Route::get('/', [ModerationController::class, 'index'])->name('index');
                Route::post('reports/{report}', [ModerationController::class, 'report'])->name('reports.update');
                Route::post('restrictions/{restriction}/lift', [ModerationController::class, 'lift'])->name('restrictions.lift');
                Route::post('appeals/{appeal}', [ModerationController::class, 'appeal'])->name('appeals.update');
                Route::post('ai-reports/{report}', [ModerationController::class, 'aiReport'])->name('ai-reports.update');
            });

            Route::middleware('permission:notifications.manage')->prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', [PlatformController::class, 'notifications'])->name('index');
                Route::post('preview', [PlatformController::class, 'preview'])->name('preview');
                Route::post('/', [PlatformController::class, 'storeBroadcast'])->name('store');
            });

            Route::get('ai', [PlatformController::class, 'ai'])->middleware('permission:ai.usage')->name('ai.index');

            Route::middleware('permission:payments.view')->get('payments', [FinanceController::class, 'payments'])->name('payments.index');
            Route::post('payments/refunds/{refund}', [FinanceController::class, 'refund'])->middleware('permission:payments.refund')->name('payments.refund');
            Route::post('payments/adjust', [FinanceController::class, 'adjust'])->middleware('permission:finance.adjust')->name('payments.adjust');
            Route::get('subscriptions', [FinanceController::class, 'subscriptions'])->middleware('permission:subscriptions.manage')->name('subscriptions.index');

            Route::middleware('permission:reports.view')->prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [InsightController::class, 'reports'])->name('index');
                Route::get('export/{type}', [InsightController::class, 'export'])->name('export');
            });
            Route::get('audit', [InsightController::class, 'audit'])->middleware('permission:audit.view')->name('audit.index');
            Route::middleware('permission:settings.manage')->prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [PlatformController::class, 'settings'])->name('index');
                Route::post('/', [PlatformController::class, 'saveSettings'])->name('update');
            });

            foreach (['quran' => 'quran.view', 'worship' => 'worship.manage', 'academy' => 'academy.manage', 'kids' => 'kids.manage', 'events' => 'events.manage', 'directories' => 'directory.manage'] as $module => $permission) {
                Route::get($module, [ModuleController::class, 'home'])->defaults('module', $module)->middleware('permission:'.$permission)->name($module.'.index');
            }
            Route::post('quran/verify', [ModuleController::class, 'verifyQuran'])->middleware('permission:quran.verify')->name('quran.verify');
            Route::post('events/attend', [ModuleController::class, 'attend'])->middleware('permission:events.manage')->name('events.attend');
            Route::post('directories/reports/{report}', [ModuleController::class, 'directoryReport'])->middleware('permission:directory.manage')->name('directories.reports');

            foreach (Registry::all() as $key => $def) {
                Route::middleware('permission:'.$def['permission'])->prefix($def['module'].'/'.$key)->name($def['module'].'.'.$key.'.')->group(function () use ($def, $key) {
                    $with = fn ($route) => $route->defaults('module', $def['module'])->defaults('resource', $key);
                    $with(Route::get('/', [ResourceController::class, 'index']))->name('index');
                    $with(Route::get('create', [ResourceController::class, 'create']))->name('create');
                    $with(Route::post('/', [ResourceController::class, 'store']))->name('store');
                    $with(Route::get('{id}/edit', [ResourceController::class, 'edit'])->whereNumber('id'))->name('edit');
                    $with(Route::put('{id}', [ResourceController::class, 'update'])->whereNumber('id'))->name('update');
                    $with(Route::delete('{id}', [ResourceController::class, 'destroy'])->whereNumber('id'))->name('destroy');
                    $with(Route::post('{id}/{action}', [ResourceController::class, 'action'])->whereNumber('id'))->name('action');
                });
            }
        });
    });
});
