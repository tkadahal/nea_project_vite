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
        $user = Auth::user();

        $query = Task::with(['status', 'priority', 'projects.directorate', 'users']);

        if ($user->hasRole(Role::SUPERADMIN)) {
            if ($request->filled('directorate_id')) {
                $query->whereHas('projects.directorate', fn($q) => $q->where('id', $request->directorate_id));
            }
        } elseif ($user->hasRole(Role::DIRECTORATE_USER)) {
            $query->whereHas('projects.directorate', fn($q) => $q->where('id', $user->directorate_id));
        } elseif ($user->hasRole(Role::PROJECT_USER)) {
            $query->whereHas('projects', fn($q) => $q->whereIn('id', $user->projects->pluck('id')));
        }

        if ($request->filled('project_id')) {
            $query->whereHas('projects', fn($q) => $q->where('id', $request->project_id));
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }

        $tasks = $query->latest()->paginate(10);

        $summary = [
            'total_tasks' => $query->count(),
            'completed_tasks' => $query->clone()->whereHas('status', fn($q) => $q->where('title', 'Completed'))->count(),
            'overdue_tasks' => $query->clone()->where('due_date', '<', now())->whereHas('status', fn($q) => $q->where('title', '!=', 'Completed'))->count(),
            'average_progress' => round(
                $query->clone()->select(DB::raw('avg(progress::integer) as avg_progress'))->withoutGlobalScopes()->reorder()->value('avg_progress') ?? 0,
                1
            ),
        ];

        $charts = [
            'status' => [
                'labels' => Status::pluck('title')->toArray(),
                'data' => Status::pluck('title')->map(fn($title) => $query->clone()->whereHas('status', fn($q) => $q->where('title', $title))->count())->toArray(),
            ],
            'priority' => [
                'labels' => Priority::pluck('title')->toArray(),
                'data' => Priority::pluck('title')->map(fn($title) => $query->clone()->whereHas('priority', fn($q) => $q->where('title', $title))->count())->toArray(),
            ],
        ];

        $directorates = $user->hasRole(Role::SUPERADMIN) ? Directorate::all() : collect();
        $projects = Project::whereIn('id', $query->clone()->with('projects')->get()->pluck('projects.*.id')->flatten()->unique())->get();
        $statuses = Status::all();
        $priorities = Priority::all();

        $data = compact('tasks', 'summary', 'charts', 'directorates', 'projects', 'statuses', 'priorities');

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($data);
        }

        return view('admin.analytics.tasks-analytics', $data);
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
        $user = Auth::user();
        $taskQuery = Task::with(['status', 'priority', 'projects.directorate', 'users']);

        if ($user->hasRole('SuperAdmin')) {
            if ($request->filled('directorate_id')) {
                $taskQuery->whereHas('projects.directorate', fn($q) => $q->where('id', $request->directorate_id));
            }
        } elseif ($user->hasRole('Directorate User')) {
            $taskQuery->whereHas('projects.directorate', fn($q) => $q->where('id', $user->directorate_id));
        } elseif ($user->hasRole('Project User')) {
            $taskQuery->whereHas('projects', fn($q) => $q->whereIn('id', $user->projects->pluck('id')));
        }

        if ($request->filled('project_id')) {
            $taskQuery->whereHas('projects', fn($q) => $q->where('id', $request->project_id));
        }
        if ($request->filled('status_id')) {
            $taskQuery->where('status_id', $request->status_id);
        }
        if ($request->filled('priority_id')) {
            $taskQuery->where('priority_id', $request->priority_id);
        }

        return Excel::download(new TasksExport($taskQuery), 'tasks_' . now()->format('Y-m-d_H-i-s') . '.csv');
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
