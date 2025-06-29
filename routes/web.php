<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\ProjectBudgetController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DirectorateController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\FiscalYearController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
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

        Route::get('/projects/analytics', [ProjectController::class, 'analytics'])->name('projects.analytics');
        Route::get('projects/{project}/progress/chart', [ProjectController::class, 'progressChart'])->name('projects.progress.chart');
        Route::post('projects/{project}/comments', [CommentController::class, 'storeForProject'])->name('projects.comments.store');
        Route::get('/projects/users/{directorate_id}', [ProjectController::class, 'getUsers'])->name('projects.users');
        Route::get('/projects/departments/{directorate_id}', [ProjectController::class, 'getDepartments'])->name('projects.departments');
        Route::get('/projects/budget/create', [ProjectController::class, 'createBudget'])->name('project.budget.create');
        Route::resource('project', ProjectController::class);

        Route::get('/contracts/projects/{directorate_id}', [ContractController::class, 'getProjects'])->name('contracts.projects');
        Route::resource('contract', ContractController::class);

        Route::get('/tasks/analytics/export', [TaskController::class, 'exportAnalytics'])->name('tasks.analytics.export');
        Route::get('/tasks/analytics', [TaskController::class, 'analytics'])->name('tasks.analytics');
        Route::post('tasks/{task}/comments', [CommentController::class, 'storeForTask'])->name('tasks.comments.store');
        Route::get('/tasks/gantt-chart', [TaskController::class, 'getGanttChart'])->name('tasks.ganttChart');
        Route::get('/tasks/users-by-projects', [TaskController::class, 'getUsersByProjects'])->name('tasks.users_by_projects');
        Route::get('/tasks/projects/{directorate_id}', [TaskController::class, 'getProjects'])->name('tasks.projects');
        Route::post('/admin/task/updateStatus', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
        Route::post('/admin/tasks/filter', [TaskController::class, 'filter'])->name('tasks.filter');
        Route::post('/admin/tasks/set-view', [TaskController::class, 'setViewPreference'])->name('task.set-view');
        Route::resource('task', TaskController::class);

        Route::resource('event', EventController::class);

        // Route::resource('fiscalYear', FiscalYearController::class);

        Route::resource('projectBudget', ProjectBudgetController::class);

        Route::get('fiscal-years/by-date', [ExpenseController::class, 'byDate'])->name('fiscal-years.by-date');
        Route::get('budgets/available', [ExpenseController::class, 'availableBudget'])->name('budgets.available');
        Route::resource('expense', ExpenseController::class);

        Route::get('files', [FileController::class, 'index'])->name('file.index');
        Route::post('{model}/{id}/files', [FileController::class, 'store'])->name('files.store');
        Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
        Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

        Route::get('notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    }
);

require __DIR__ . '/auth.php';
