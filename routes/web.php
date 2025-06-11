<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DirectorateController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
// use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('test', 'full-page');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\AuthGates::class])
    ->name('dashboard');

Route::middleware(['auth', \App\Http\Middleware\AuthGates::class])->group(function () {
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [AppearanceController::class, 'edit'])->name('settings.appearance.edit');
});

Route::group(
    [
        'middleware' => ['auth', \App\Http\Middleware\AuthGates::class],
        'namespace' => 'App\Http\Controllers\Admin',
        'prefix' => 'admin',
        'as' => 'admin.',
    ],
    function () {

        Route::resource('permission', PermissionController::class);
        Route::resource('role', RoleController::class);

        Route::get('/users/projects/{directorate_id}', [UserController::class, 'getProjects'])->name('users.projects');
        Route::resource('user', UserController::class);

        Route::resource('directorate', DirectorateController::class);
        Route::resource('department', DepartmentController::class);
        Route::resource('status', StatusController::class);
        Route::resource('priority', PriorityController::class);

        Route::get('/projects/users/{directorate_id}', [ProjectController::class, 'getUsers'])->name('projects.users');
        Route::get('/projects/departments/{directorate_id}', [ProjectController::class, 'getDepartments'])->name('projects.departments');
        Route::resource('project', ProjectController::class);

        Route::get('/contracts/projects/{directorate_id}', [ContractController::class, 'getProjects'])->name('contracts.projects');
        Route::resource('contract', ContractController::class);

        Route::get('/tasks/users-by-projects', [TaskController::class, 'getUsersByProjects'])->name('tasks.users_by_projects');
        Route::get('/tasks/projects/{directorate_id}', [TaskController::class, 'getProjects'])->name('tasks.projects');
        Route::post('/admin/task/updateStatus', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
        Route::post('/admin/tasks/filter', [TaskController::class, 'filter'])->name('tasks.filter');
        Route::post('/admin/tasks/set-view', [TaskController::class, 'setViewPreference'])->name('task.set-view');
        Route::resource('task', TaskController::class);

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');

        Route::get('notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    }
);

require __DIR__.'/auth.php';
