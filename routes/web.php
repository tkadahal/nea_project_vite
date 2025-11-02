<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\BudgetController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FiscalYearController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Admin\DirectorateController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Admin\ProjectExpenseController;
use App\Http\Controllers\Admin\ProjectActivityController;
use App\Http\Controllers\Admin\ContractExtensionController;
use App\Http\Controllers\Admin\AnalyticalDashboardController;
use App\Http\Controllers\Admin\BudgetQuaterAllocationController;

// Route::get('/', function () {
//     return redirect()->route('login');
// })->name('home');

// Route::view('test', 'full-page');

Route::permanentRedirect('/', '/login');

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

        Route::get('summary', [AnalyticalDashboardController::class, 'summary'])->name('summary');

        Route::get('analytics/task', [AnalyticalDashboardController::class, 'taskAnalytics'])->name('analytics.task');
        Route::get('analytics/project', [AnalyticalDashboardController::class, 'projectAnalytics'])->name('analytics.project');
        Route::get('/tasks/analytics/export', [AnalyticalDashboardController::class, 'exportTaskAnalytics'])->name('tasks.analytics.export');
        Route::get('/projects/analytics/export', [AnalyticalDashboardController::class, 'exportProjectAnalytics'])->name('projects.analytics.export');

        Route::get('/users/projects/{directorate_id}', [UserController::class, 'getProjects'])->name('users.projects');
        Route::resource('user', UserController::class);

        Route::resource('directorate', DirectorateController::class);
        Route::resource('department', DepartmentController::class);
        Route::resource('status', StatusController::class);
        Route::resource('priority', PriorityController::class);

        // Route::get('/projects/analytics', [ProjectController::class, 'analytics'])->name('projects.analytics');
        Route::get('projects/{project}/progress/chart', [ProjectController::class, 'progressChart'])->name('projects.progress.chart');
        Route::post('projects/{project}/comments', [CommentController::class, 'storeForProject'])->name('projects.comments.store');
        Route::get('/projects/users/{directorate_id}', [ProjectController::class, 'getUsers'])->name('projects.users');
        Route::get('/projects/departments/{directorate_id}', [ProjectController::class, 'getDepartments'])->name('projects.departments');
        Route::get('/projects/budget/create', [ProjectController::class, 'createBudget'])->name('project.budget.create');
        Route::resource('project', ProjectController::class);

        Route::get('/project-activities/template', [ProjectActivityController::class, 'downloadTemplate'])->name('projectActivity.template');
        Route::get('/project-activities/upload', [ProjectActivityController::class, 'showUploadForm'])
            ->name('projectActivity.uploadForm');
        Route::post('/project-activities/upload', [ProjectActivityController::class, 'uploadExcel'])
            ->name('projectActivity.upload');

        // Show route (GET)
        Route::get('/projectActivity/show/{projectId}/{fiscalYearId}', [ProjectActivityController::class, 'show'])
            ->name('projectActivity.show');

        // Edit route (GET)
        Route::get('/projectActivity/edit/{projectId}/{fiscalYearId}', [ProjectActivityController::class, 'edit'])
            ->name('projectActivity.edit');

        Route::get('/projectActivity/budgetData', [ProjectActivityController::class, 'getBudgetData'])
            ->name('projectActivity.budgetData');

        // Update route (PUT/PATCH)
        Route::put('/projectActivity/{projectId}/{fiscalYearId}', [ProjectActivityController::class, 'update'])
            ->name('projectActivity.update');
        Route::resource('projectActivity', ProjectActivityController::class)->except('show', 'edit', 'update');

        Route::get('/contracts/projects/{directorate_id}', [ContractController::class, 'getProjects'])->name('contracts.projects');
        Route::resource('contract', ContractController::class);

        Route::get('contract/{contract}/extensions/create', [ContractExtensionController::class, 'create'])->name('contract.extensions.create');
        Route::post('contract/{contract}/extensions', [ContractExtensionController::class, 'store'])->name('contract.extensions.store');
        Route::get('contract/{contract}/extensions/{extension}/edit', [ContractExtensionController::class, 'edit'])->name('contract.extensions.edit');
        Route::put('contract/{contract}/extensions/{extension}', [ContractExtensionController::class, 'update'])->name('contract.extensions.update');
        Route::delete('contract/{contract}/extensions/{extension}', [ContractExtensionController::class, 'destroy'])->name('contract.extensions.destroy');

        Route::get('/task/{task}/{project?}', [TaskController::class, 'show'])
            ->name('task.show')
            ->where(['task' => '[0-9]+', 'project' => '[0-9]+']);
        Route::get('/task/{task}/edit/{project?}', [TaskController::class, 'edit'])
            ->name('task.edit')
            ->where(['task' => '[0-9]+', 'project' => '[0-9]+']);
        Route::put('/task/{task}/update/{project?}', [TaskController::class, 'update'])
            ->name('task.update')
            ->where(['task' => '[0-9]+', 'project' => '[0-9]+']);
        Route::post('/task/load-more', [TaskController::class, 'loadMore'])->name('task.loadMore');
        Route::post('/task/updateStatus', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
        Route::post('/tasks/filter', [TaskController::class, 'filter'])->name('tasks.filter');
        Route::post('/tasks/set-view', [TaskController::class, 'setViewPreference'])->name('task.set-view');
        Route::get('/tasks/gantt-chart', [TaskController::class, 'getGanttChart'])->name('tasks.ganttChart');
        Route::get('/tasks/users-by-projects', [TaskController::class, 'getUsersByProjects'])->name('tasks.users_by_projects');
        Route::get('/tasks/users-by-directorate-or-department', [TaskController::class, 'getUsersByDirectorateOrDepartment'])->name('tasks.users_by_directorate_or_department');
        Route::get('/tasks/projects/{directorate_id}', [TaskController::class, 'getProjects'])->name('tasks.projects');
        Route::get('/tasks/departments/{directorate_id}', [TaskController::class, 'getDepartments'])->name('tasks.departments');
        Route::post('/tasks/{task}/comments', [CommentController::class, 'storeForTask'])
            ->name('tasks.comments.store');
        // Route::post('/tasks/{task}/{project?}/comments', [CommentController::class, 'storeForTask'])->name('tasks.comments.store');
        Route::resource('task', TaskController::class)->except(['show', 'edit', 'update']);

        Route::resource('event', EventController::class);

        Route::resource('fiscalYear', FiscalYearController::class);

        // Specific routes first
        Route::get('budget/download-template', [BudgetController::class, 'downloadTemplate'])->name('budget.download-template');
        Route::get('budget/upload', [BudgetController::class, 'uploadIndex'])->name('budget.upload.index');
        Route::post('budget/upload', [BudgetController::class, 'upload'])->name('budget.upload');
        Route::get('budget/{budget}/remaining', [BudgetController::class, 'remaining'])->name('budget.remaining');

        // Resource route last
        Route::get('budgets/duplicates', [BudgetController::class, 'listDuplicates'])->name('budget.duplicates');
        Route::post('budgets/clean-duplicates', [BudgetController::class, 'cleanDuplicates'])->name('budget.cleanDuplicates');
        Route::resource('budget', BudgetController::class);

        //Budget Quater Allocation Routes
        Route::post('/budget-quater-allocations/load-budgets', [BudgetQuaterAllocationController::class, 'loadBudgets'])->name('budgetQuaterAllocations.loadBudgets');
        Route::resource('budgetQuaterAllocation', BudgetQuaterAllocationController::class);

        Route::get('/project-activities/{projectId}/{fiscalYearId}', [ExpenseController::class, 'getForProject'])
            ->name('project-activities.get');
        Route::get('fiscal-years/by-date', [ExpenseController::class, 'byDate'])->name('fiscal-years.by-date');
        Route::get('budgets/available', [ExpenseController::class, 'availableBudget'])->name('budgets.available');


        Route::get('/expenses/0', [ExpenseController::class, 'testShow'])
            ->name('expense.testShow');
        Route::resource('expense', ExpenseController::class);


        Route::get('/projectExpense/show/{projectId}/{fiscalyearId}', [ProjectExpenseController::class, 'show'])
            ->name('projectExpense.show');
        Route::resource('projectExpense', ProjectExpenseController::class)->except('show');

        Route::get('files', [FileController::class, 'index'])->name('file.index');
        Route::post('{model}/{id}/files', [FileController::class, 'store'])->name('files.store');
        Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
        Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

        Route::get('notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    }
);

require __DIR__ . '/auth.php';
