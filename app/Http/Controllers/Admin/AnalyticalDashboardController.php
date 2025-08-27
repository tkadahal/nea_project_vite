<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Task;
use App\Models\Status;
use App\Models\Project;
use App\Models\Priority;
use Illuminate\View\View;
use App\Models\Department;
use App\Models\Directorate;
use App\Exports\TasksExport;
use App\Exports\ProjectsExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;

class AnalyticalDashboardController extends Controller
{
    public function taskAnalytics(Request $request): View|JsonResponse
    {
        try {
            $user = Auth::user();
            $roles = $user->roles->pluck('id')->toArray();

            // Initialize query with necessary relations
            $query = Task::with([
                'priority',
                'projects' => fn($q) => $q->withPivot('status_id', 'progress')->with('directorate'),
                'users',
                'directorate',
                'department',
            ]);

            // Apply role-based filters
            if (in_array(Role::SUPERADMIN, $roles)) {
                // Superadmin: Allow directorate filter from request
                if ($request->filled('directorate_id')) {
                    $query->where('directorate_id', $request->directorate_id);
                }
            } elseif (in_array(Role::DIRECTORATE_USER, $roles) && $user->directorate_id) {
                // Directorate User: Filter by user's directorate
                $query->where('directorate_id', $user->directorate_id);
            } elseif (in_array(Role::DEPARTMENT_USER, $roles) && $user->directorate_id) {
                // Department User: Filter by departments under user's directorate
                $departmentIds = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                    ->pluck('id');
                if ($departmentIds->isEmpty()) {
                    $query->whereRaw('1 = 0'); // Force empty result if no departments
                } else {
                    $query->whereIn('department_id', $departmentIds);
                }
            } elseif (in_array(Role::PROJECT_USER, $roles)) {
                // Project User: Filter by user's projects
                $projectIds = $user->projects()->pluck('id');
                if ($projectIds->isEmpty()) {
                    $query->whereRaw('1 = 0'); // Force empty result if no projects
                } else {
                    $query->whereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds));
                }
            } else {
                // Fallback: Limit to tasks assigned to the user
                $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
            }

            // Apply additional filters
            if ($request->filled('project_id')) {
                $query->whereHas('projects', fn($q) => $q->where('id', $request->project_id));
            }
            if ($request->filled('status_id')) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('projects', fn($q) => $q->wherePivot('status_id', $request->status_id))
                        ->orWhere('tasks.status_id', $request->status_id);
                });
            }
            if ($request->filled('priority_id')) {
                $query->where('priority_id', $request->priority_id);
            }

            // Optional: Exclude subtasks (uncomment to enable)
            // $query->whereNull('tasks.parent_id');

            // Fetch tasks and join with project_task for status
            $projectTasks = $query->get()->flatMap(function ($task) {
                $taskProjects = $task->projects->map(function ($project) use ($task) {
                    return (object) [
                        'task' => $task,
                        'project' => $project,
                        'status_id' => $project->pivot->status_id ?? $task->status_id,
                        'status' => Status::find($project->pivot->status_id ?? $task->status_id),
                        'progress' => $project->pivot->progress ?? $task->progress,
                        'project_id' => $project->id,
                        'entity' => $project->title,
                    ];
                });
                // Include tasks without projects
                if ($taskProjects->isEmpty() && ($task->directorate_id || $task->department_id)) {
                    $taskProjects->push((object) [
                        'task' => $task,
                        'project' => null,
                        'status_id' => $task->status_id,
                        'status' => Status::find($task->status_id),
                        'progress' => $task->progress,
                        'project_id' => null,
                        'entity' => $task->department?->title ?? $task->directorate?->title ?? 'N/A',
                    ]);
                }
                return $taskProjects;
            })->filter(fn($projectTask) => !is_null($projectTask->status_id));

            // Paginate project tasks
            $perPage = 10;
            $currentPage = $request->input('page', 1);
            $paginatedTasks = $projectTasks->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $tasks = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedTasks,
                $projectTasks->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Calculate summary
            $statusCounts = $projectTasks->groupBy('status_id')->map->count()->toArray();
            $completedStatusId = Status::where('title', 'Completed')->first()?->id;
            $summary = [
                'total_tasks' => $projectTasks->count(),
                'completed_tasks' => $statusCounts[$completedStatusId] ?? 0,
                'overdue_tasks' => $projectTasks->filter(function ($projectTask) use ($completedStatusId) {
                    return $projectTask->task->due_date && $projectTask->task->due_date->isPast() && $projectTask->status_id != $completedStatusId;
                })->count(),
                'average_progress' => round($projectTasks->avg('progress') ?? 0, 1),
            ];

            // Use Status model colors (hex codes)
            $statusColors = Status::pluck('color', 'id')->toArray();
            $priorityColors = [
                'Urgent' => '#EF4444',
                'High' => '#F59E0B',
                'Medium' => '#10B981',
                'Low' => '#6B7280',
            ];

            // Prepare chart data
            $charts = [
                'status' => [
                    'labels' => Status::pluck('title')->toArray(),
                    'data' => Status::pluck('id')->map(fn($id) => $statusCounts[$id] ?? 0)->toArray(),
                    'colors' => Status::pluck('id')->map(fn($id) => $statusColors[$id] ?? '#6B7280')->toArray(),
                ],
                'priority' => [
                    'labels' => Priority::pluck('title')->toArray(),
                    'data' => Priority::pluck('title')->map(fn($title) => $projectTasks->filter(fn($pt) => $pt->task->priority?->title === $title)->count())->toArray(),
                    'colors' => Priority::pluck('title')->map(fn($title) => $priorityColors[$title] ?? '#6B7280')->toArray(),
                ],
            ];

            // Fetch filter options
            $directorates = in_array(Role::SUPERADMIN, $roles) ? Directorate::all() : collect();
            $projectIds = $projectTasks->pluck('project_id')->unique()->filter();
            $projects = Project::whereIn('id', $projectIds)->get();
            $statuses = Status::all();
            $priorities = Priority::all();

            // Prepare table data
            $tableData = $paginatedTasks->map(function ($projectTask) use ($statusColors, $priorityColors) {
                $task = $projectTask->task;
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'entity' => $projectTask->entity,
                    'status' => $projectTask->status ? ['title' => $projectTask->status->title, 'color' => $statusColors[$projectTask->status->id] ?? 'gray'] : ['title' => 'N/A', 'color' => 'gray'],
                    'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : ['title' => 'N/A', 'color' => 'gray'],
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                    'users' => $task->users->map(fn($user) => [
                        'initials' => $user->initials(),
                    ])->toArray(),
                    'project_id' => $projectTask->project_id,
                ];
            })->values()->toArray();

            $data = compact('tasks', 'summary', 'charts', 'directorates', 'projects', 'statuses', 'priorities', 'tableData');

            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json($data);
            }

            return view('admin.analytics.tasks-analytics', $data);
        } catch (\Exception $e) {
            abort(500, 'Error loading analytics data: ' . $e->getMessage());
        }
    }

    public function projectAnalytics(Request $request): View|JsonResponse|RedirectResponse
    {
        try {
            $directorates = Directorate::all();
            $departments = Department::all();
            $statuses = Status::all();
            $priorities = Priority::all();

            $query = Project::withoutGlobalScopes()->with(['directorate', 'status', 'priority', 'tasks', 'contracts', 'expenses', 'budgets', 'users']);
            $user = Auth::user();

            if ($user && !$user->roles->contains('id', 3) && !$user->roles->contains('id', 4)) {
                $query->withTrashed();
            }

            if ($user && $user->roles->contains('id', 3)) {
                $query->where('directorate_id', $user->directorate_id);
            } elseif ($user && $user->roles->contains('id', 4)) {
                $query->whereIn('id', function ($query) use ($user) {
                    $query->select('project_id')
                        ->from('project_user')
                        ->where('user_id', $user->id);
                });
            }

            if ($request->filled('directorate_id') && Directorate::where('id', $request->directorate_id)->exists()) {
                $query->where('directorate_id', $request->directorate_id);
            }
            if ($request->filled('department_id') && Department::where('id', $request->department_id)->exists() && Project::where('department_id', $request->department_id)->exists()) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('status_id') && Status::where('id', $request->status_id)->exists()) {
                $query->where('status_id', $request->status_id);
            }
            if ($request->filled('priority_id') && Priority::where('id', $request->priority_id)->exists()) {
                $query->where('priority_id', $request->priority_id);
            }

            $projects = $query->paginate(10);

            $projects->getCollection()->transform(function ($project) {
                $totalBudget = $project->total_budget ?? 0;
                $project->remaining_budget = $totalBudget - ($project->expenses->sum('amount') + $project->contracts->sum('contract_amount'));
                $project->days_remaining = $project->end_date ? max(0, $project->end_date->diffInDays(now()) * ($project->end_date > now() ? 1 : -1)) : 0;
                $project->financial_progress = $totalBudget > 0 ? (($project->expenses->sum('amount') + $project->contracts->sum('contract_amount')) / $totalBudget * 100) : 0;
                $project->progress = (float) $project->progress;
                return $project;
            });

            $completedStatusId = Status::where('title', 'Completed')->value('id') ?? 0;
            $averageProgress = $query->count() > 0 ? $query->selectRaw('AVG(CAST(progress AS DECIMAL)) AS avg_progress')->value('avg_progress') : 0;
            $averageProgress = round((float) $averageProgress, 2);
            $summary = [
                'total_projects' => $query->count(),
                'completed_projects' => $completedStatusId ? $query->where('status_id', $completedStatusId)->count() : 0,
                'overdue_projects' => $query->where('end_date', '<', now())->where('status_id', '!=', $completedStatusId)->count(),
                'average_progress' => $averageProgress,
            ];

            $chartQuery = Project::withoutGlobalScopes()->with(['directorate', 'status', 'priority', 'tasks', 'contracts', 'expenses', 'budgets', 'users']);
            if ($user && !$user->roles->contains('id', 3) && !$user->roles->contains('id', 4)) {
                $chartQuery->withTrashed();
            }
            if ($user && $user->roles->contains('id', 3)) {
                $chartQuery->where('directorate_id', $user->directorate_id);
            } elseif ($user && $user->roles->contains('id', 4)) {
                $chartQuery->whereIn('id', function ($query) use ($user) {
                    $query->select('project_id')
                        ->from('project_user')
                        ->where('user_id', $user->id);
                });
            }
            if ($request->filled('directorate_id') && Directorate::where('id', $request->directorate_id)->exists()) {
                $chartQuery->where('directorate_id', $request->directorate_id);
            }
            if ($request->filled('department_id') && Department::where('id', $request->department_id)->exists() && Project::where('department_id', $request->department_id)->exists()) {
                $chartQuery->where('department_id', $request->department_id);
            }
            if ($request->filled('status_id') && Status::where('id', $request->status_id)->exists()) {
                $chartQuery->where('status_id', $request->status_id);
            }
            if ($request->filled('priority_id') && Priority::where('id', $request->priority_id)->exists()) {
                $chartQuery->where('priority_id', $request->priority_id);
            }

            $chartProjects = $chartQuery->get();
            $chartProjects->transform(function ($project) {
                $project->progress = (float) $project->progress;
                return $project;
            });

            $charts = [
                'progress' => [
                    'labels' => $chartProjects->isNotEmpty() ? ["{$chartProjects->count()} Projects Available"] : ['No Projects Available'],
                    'physical' => $chartProjects->isNotEmpty() ? $chartProjects->pluck('progress')->all() : [0],
                    'financial' => $chartProjects->isNotEmpty() ? $chartProjects->pluck('financial_progress')->all() : [0],
                ],
                'task_contract' => [
                    'labels' => ['Tasks', 'Contracts'],
                    'data' => $chartProjects->isNotEmpty() ? [
                        $chartProjects->sum(fn($project) => $project->tasks->count()),
                        $chartProjects->sum(fn($project) => $project->contracts->count()),
                    ] : [0, 0],
                ],
            ];

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'summary' => $summary,
                    'charts' => $charts,
                    'projects' => $projects->items(),
                    'pagination' => [
                        'current_page' => $projects->currentPage(),
                        'last_page' => $projects->lastPage(),
                        'per_page' => $projects->perPage(),
                        'total' => $projects->total(),
                    ],
                ]);
            }

            return view('admin.analytics.project-analytics', compact('projects', 'summary', 'charts', 'directorates', 'departments', 'statuses', 'priorities'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'An error occurred. Please try again.'], 500);
            }
            return redirect()->back()->withErrors(['error' => 'An error occurred. Please check the logs.']);
        }
    }

    public function exportTaskAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $roles = $user->roles->pluck('id')->toArray();

            $taskQuery = Task::query()
                ->select('tasks.*')
                ->with([
                    'projects' => fn($q) => $q->with('directorate'),
                    'priority',
                    'users' => fn($q) => $q->select('users.id', 'users.name'),
                    'directorate',
                    'department',
                ])
                ->leftJoin('project_task', 'tasks.id', '=', 'project_task.task_id')
                ->addSelect(DB::raw('COALESCE(project_task.status_id, tasks.status_id) as status_id'));

            // Role-based filtering
            if (in_array(Role::SUPERADMIN, $roles)) {
                // Superadmin: Allow directorate filter from request
                if ($request->filled('directorate_id')) {
                    $taskQuery->where('directorate_id', $request->directorate_id)
                        ->orWhereHas('projects', fn($q) => $q->where('directorate_id', $request->directorate_id));
                }
            } elseif (in_array(Role::DIRECTORATE_USER, $roles) && $user->directorate_id) {
                // Directorate User: Filter by user's directorate
                $taskQuery->where('directorate_id', $user->directorate_id)
                    ->orWhereHas('projects', fn($q) => $q->where('directorate_id', $user->directorate_id));
            } elseif (in_array(Role::DEPARTMENT_USER, $roles) && $user->directorate_id) {
                // Department User: Filter by departments under user's directorate
                $departmentIds = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                    ->pluck('id');
                if ($departmentIds->isEmpty()) {
                    $taskQuery->whereRaw('1 = 0'); // Force empty result if no departments
                } else {
                    $taskQuery->whereIn('department_id', $departmentIds);
                }
            } elseif (in_array(Role::PROJECT_USER, $roles)) {
                // Project User: Filter by user's projects
                $projectIds = $user->projects()->pluck('id');
                if ($projectIds->isEmpty()) {
                    $taskQuery->whereRaw('1 = 0'); // Force empty result if no projects
                } else {
                    $taskQuery->whereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds));
                }
            } else {
                // Fallback: Limit to tasks assigned to the user
                $taskQuery->whereHas('users', fn($q) => $q->where('users.id', $user->id));
            }

            // Request-based filtering
            if ($request->filled('project_id')) {
                $taskQuery->whereHas('projects', fn($q) => $q->where('id', $request->project_id));
            }
            if ($request->filled('status_id')) {
                $taskQuery->where(function ($q) use ($request) {
                    $q->where('project_task.status_id', $request->status_id)
                        ->orWhere('tasks.status_id', $request->status_id);
                });
            }
            if ($request->filled('priority_id')) {
                $taskQuery->where('tasks.priority_id', $request->priority_id);
            }

            // Optional: Exclude subtasks (uncomment to enable)
            // $taskQuery->whereNull('tasks.parent_id');

            // Ensure unique tasks
            $taskQuery->distinct('tasks.id');

            return Excel::download(new TasksExport($taskQuery), 'tasks_' . now()->format('Y-m-d_H-i-s') . '.csv');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export tasks: ' . $e->getMessage());
        }
    }

    public function exportProjectAnalytics(Request $request)
    {
        $user = Auth::user();
        $projectQuery = Project::withoutGlobalScopes()->with(['directorate', 'status', 'priority', 'tasks', 'contracts', 'expenses', 'budgets', 'users']);

        if ($user && !$user->roles->contains('id', 3) && !$user->roles->contains('id', 4)) {
            $projectQuery->withTrashed();
        }

        if ($user && $user->roles->contains('id', 3)) {
            $projectQuery->where('directorate_id', $user->directorate_id);
        } elseif ($user && $user->roles->contains('id', 4)) {
            $projectQuery->whereIn('id', function ($query) use ($user) {
                $query->select('project_id')
                    ->from('project_user')
                    ->where('user_id', $user->id);
            });
        }

        if ($request->filled('directorate_id') && Directorate::where('id', $request->directorate_id)->exists()) {
            $projectQuery->where('directorate_id', $request->directorate_id);
        }
        if ($request->filled('department_id') && Department::where('id', $request->department_id)->exists() && Project::where('department_id', $request->department_id)->exists()) {
            $projectQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('status_id') && Status::where('id', $request->status_id)->exists()) {
            $projectQuery->where('status_id', $request->status_id);
        }
        if ($request->filled('priority_id') && Priority::where('id', $request->priority_id)->exists()) {
            $projectQuery->where('priority_id', $request->priority_id);
        }

        return Excel::download(new ProjectsExport($projectQuery), 'projects_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }
}
