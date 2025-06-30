<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Models\Directorate;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Notifications\TaskCreated;
use App\Notifications\TaskDeleted;
use App\Notifications\TaskUpdated;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('task_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activeView = session('task_view_preference', 'board');
        $user = Auth::user();

        $statuses = Cache::remember('task_statuses', 86400, function () {
            return Status::all();
        });

        $priorities = Cache::remember('task_priorities', 86400, function () {
            return Priority::all();
        });

        $statusColors = $statuses->pluck('color', 'id')->toArray();

        $priorityColors = [
            'Urgent' => '#EF4444',
            'High' => '#F59E0B',
            'Medium' => '#10B981',
            'Low' => '#6B7280',
        ];

        $data = [
            'activeView' => $activeView,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'projectsForFilter' => Cache::remember('projects_for_filter', 86400, fn() => Project::all()),
            'statusColors' => $statusColors,
            'priorityColors' => $priorityColors,
            'routePrefix' => 'admin.task',
            'deleteConfirmationMessage' => 'Are you sure you want to delete this task?',
            'actions' => ['view', 'edit', 'delete'],
        ];

        $withRelations = ['status', 'priority', 'projects', 'users', 'directorate'];
        $taskQuery = Task::with($withRelations)->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();
            Log::info('Task filtering for user', ['user_id' => $user->id, 'role_ids' => $roleIds]);

            if (in_array(Role::SUPERADMIN, $roleIds)) {
                // Superadmin sees all tasks, no filtering
                Log::info('Superadmin access, showing all tasks', ['user_id' => $user->id]);
            } else {
                // Non-superadmin users
                $taskQuery->where(function ($query) use ($user, $roleIds) {
                    if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                        $query->where('directorate_id', $user->directorate_id)
                            ->orWhereHas('projects', function ($q) use ($user) {
                                $q->where('directorate_id', $user->directorate_id)->whereNull('deleted_at');
                            });
                        Log::info('Filtering tasks for Directorate User', [
                            'user_id' => $user->id,
                            'directorate_id' => $user->directorate_id,
                        ]);
                    } elseif (in_array(Role::PROJECT_USER, $roleIds)) {
                        $projectIds = $user->projects()->whereNull('deleted_at')->pluck('id');
                        $query->whereIn('directorate_id', function ($q) use ($projectIds) {
                            $q->select('directorate_id')->from('projects')->whereIn('id', $projectIds)->whereNull('deleted_at');
                        })->orWhereHas('projects', function ($q) use ($user) {
                            $q->whereIn('id', $user->projects()->whereNull('deleted_at')->pluck('id'))->whereNull('deleted_at');
                        });
                        Log::info('Filtering tasks for Project User', [
                            'user_id' => $user->id,
                            'project_count' => $projectIds->count(),
                        ]);
                    } else {
                        // Fallback for users with no valid role
                        $query->whereRaw('1 = 0'); // Return no tasks
                        Log::warning('No valid role for task access', ['user_id' => $user->id, 'role_ids' => $roleIds]);
                    }
                });
            }
        } catch (\Exception $e) {
            Log::error('Error in task filtering', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $data['error'] = 'Unable to load tasks due to an error.';
        }

        if ($activeView === 'board') {
            $tasks = $taskQuery->get();
            Log::info('Tasks fetched for board view', ['count' => $tasks->count()]);
            $data['tasks'] = $tasks->groupBy('status_id')->map(function ($group) {
                return $group->values();
            });
        }

        if ($activeView === 'list') {
            $data['tasksFlat'] = $taskQuery->get()->values();
            Log::info('Tasks fetched for list view', ['count' => $data['tasksFlat']->count()]);
        }

        if ($activeView === 'calendar') {
            $tasks = $taskQuery->get();
            Log::info('Tasks fetched for calendar view', ['count' => $tasks->count()]);
            $data['calendarData'] = $tasks->map(function ($task) use ($statusColors) {
                $startDate = $task->start_date ? Carbon::parse($task->start_date) : ($task->due_date ? Carbon::parse($task->due_date) : null);
                $endDate = $task->due_date ? Carbon::parse($task->due_date) : null;

                return [
                    'id' => $task->id,
                    'title' => $task->title ?? 'Untitled Task',
                    'start' => $startDate ? $startDate->format('Y-m-d') : null,
                    'end' => $endDate ? $endDate->copy()->addDay()->format('Y-m-d') : null,
                    'color' => $task->status ? ($statusColors[$task->status->id] ?? 'gray') : 'gray',
                    'url' => route('admin.task.show', $task->id),
                    'extendedProps' => [
                        'status' => $task->status->title ?? 'N/A',
                        'priority' => $task->priority->title ?? 'N/A',
                        'projects' => $task->projects->pluck('title')->all(),
                        'users' => $task->users->pluck('name')->all(),
                        'directorate' => $task->directorate?->title ?? 'N/A',
                    ],
                ];
            })->filter(fn($event) => $event['start'] !== null)->values()->all();
        }

        if ($activeView === 'table') {
            $tasks = $taskQuery->get();
            Log::info('Tasks fetched for table view', ['count' => $tasks->count()]);
            $data['tableHeaders'] = [
                trans('global.task.fields.id'),
                trans('global.task.fields.title'),
                trans('global.task.fields.status_id'),
                trans('global.task.fields.priority_id'),
                trans('global.task.fields.due_date'),
                trans('global.task.fields.projects'),
                trans('global.task.fields.users'),
                trans('global.task.fields.directorate'),
            ];
            $data['tableData'] = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status->title ?? 'N/A',
                    'priority' => $task->priority->title ?? 'N/A',
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                    'projects' => $task->projects->pluck('title')->join(', '),
                    'users' => $task->users->pluck('name')->join(', '),
                    'directorate' => $task->directorate?->title ?? 'N/A',
                ];
            })->all();
        }

        return view('admin.tasks.index', $data);
    }

    public function create(): View
    {
        abort_if(Gate::denies('task_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        $users = collect();

        if (in_array(Role::SUPERADMIN, $roleIds)) {
            $directorates = Directorate::pluck('title', 'id');
            $projects = Project::whereNull('deleted_at')->pluck('title', 'id');
        } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
            $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
            $projects = Project::where('directorate_id', $user->directorate_id)
                ->whereNull('deleted_at')
                ->pluck('title', 'id');
        } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
            $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
            $projects = $user->projects()
                ->whereNull('deleted_at')
                ->pluck('title', 'id');
        } else {
            $directorates = collect();
            $projects = collect();
            Log::warning('No valid role or directorate_id for user', ['user_id' => $user->id]);
        }

        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');

        return view('admin.tasks.create', compact('directorates', 'projects', 'users', 'statuses', 'priorities'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('task_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validated();

        $task = Task::create($validated);
        $task->projects()->sync($validated['projects'] ?? []);
        $task->users()->sync($validated['users'] ?? []);

        foreach ($task->users as $user) {
            $user->notify(new TaskCreated($task));
        }

        return redirect()->route('admin.task.index')
            ->with('message', 'Task created successfully.');
    }

    public function show(Task $task): View
    {
        $task->load(
            [
                'status',
                'priority',
                'projects',
                'users',
                'comments.user',
                'comments.replies.user',
            ]
        );

        $user = Auth::user();
        $commentIds = $user->comments()
            ->where('commentable_type', 'App\Models\Task')
            ->where('commentable_id', $task->id)
            ->whereNull('comment_user.read_at')
            ->pluck('comments.id');

        foreach ($commentIds as $commentId) {
            $user->comments()->updateExistingPivot($commentId, ['read_at' => now()]);
        }

        return view('admin.tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        abort_if(Gate::denies('task_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        $task->load(['status', 'priority', 'projects', 'users']);
        $directorates = collect();
        $projects = collect();
        $users = collect();
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');

        if (in_array(Role::SUPERADMIN, $roleIds)) {
            $directorates = Directorate::pluck('title', 'id');
            $projects = Project::whereNull('deleted_at')->pluck('title', 'id');
        } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
            $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
            $projects = Project::where('directorate_id', $user->directorate_id)
                ->whereNull('deleted_at')
                ->pluck('title', 'id');
            // Include task's directorate_id if different, but restrict editing
            if ($task->directorate_id && $task->directorate_id != $user->directorate_id) {
                $directorates->put($task->directorate_id, Directorate::find($task->directorate_id)?->title ?? 'N/A');
            }
        } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
            $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
            $projects = $user->projects()
                ->whereNull('deleted_at')
                ->pluck('title', 'id');
            // Include task's directorate_id if different, but restrict editing
            if ($task->directorate_id && $task->directorate_id != $user->directorate_id) {
                $directorates->put($task->directorate_id, Directorate::find($task->directorate_id)?->title ?? 'N/A');
            }
        } else {
            Log::warning('No valid role or directorate_id for user in edit', [
                'user_id' => $user->id,
                'role_ids' => $roleIds,
                'directorate_id' => $user->directorate_id,
                'task_id' => $task->id,
            ]);
        }

        return view('admin.tasks.edit', compact('task', 'directorates', 'projects', 'users', 'statuses', 'priorities'));
    }

    public function update(StoreTaskRequest $request, Task $task): RedirectResponse
    {
        abort_if(Gate::denies('task_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validated();

        $task->update($validated);
        $task->projects()->sync($validated['projects'] ?? []);
        $task->users()->sync($validated['users'] ?? []);

        // foreach ($task->users as $user) {
        //     $user->notify(new TaskUpdated($task));
        // }

        return redirect()->route('admin.task.show', $task)
            ->with('message', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        abort_if(Gate::denies('task_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // foreach ($task->users as $user) {
        //     $user->notify(new TaskDeleted($task));
        // }

        $task->delete();

        return redirect()->route('admin.task.index')
            ->with('message', 'Task deleted successfully.');
    }

    public function filter(Request $request): JsonResponse
    {
        $query = Task::with(['status', 'priority', 'projects', 'users']);

        if ($request->has('filter_status')) {
            $query->whereIn('status_id', $request->input('filter_status'));
        }
        if ($request->has('filter_priority')) {
            $query->whereIn('priority_id', $request->input('filter_priority'));
        }
        if ($request->has('filter_project')) {
            $query->whereHas('projects', fn($q) => $q->whereIn('id', (array) $request->input('filter_project')));
        }
        if ($request->has('filter_start_date')) {
            $query->whereDate('start_date', '>=', $request->input('filter_start_date'));
        }
        if ($request->has('filter_due_date')) {
            $query->whereDate('due_date', '<=', $request->input('filter_due_date'));
        }
        if ($request->has('filter_completed_date')) {
            $query->whereDate('completion_date', $request->input('filter_completed_date'));
        }

        $tasks = $query->get();
        $groupedTasks = $tasks->groupBy('status_id')->map(function ($group) {
            return $group->values();
        });
        $tasksFlat = $tasks->values();

        return response()->json([
            'tasks' => $tasksFlat,
            'groupedTasks' => $groupedTasks,
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $task = Task::findOrFail($request->input('task_id'));
        $task->status_id = $request->input('status_id');
        $task->save();

        // foreach ($task->users as $user) {
        //     $user->notify(new TaskUpdated($task));
        // }

        return response()->json(['message' => 'Task status updated successfully']);
    }

    public function setViewPreference(Request $request): JsonResponse
    {
        $request->session()->put('task_view_preference', $request->input('task_view_preference'));

        return response()->json(['success' => true]);
    }

    public function getProjects($directorateId): JsonResponse
    {
        try {
            $projects = Project::where('directorate_id', $directorateId)
                ->whereNull('deleted_at')
                ->get()
                ->map(function ($project) {
                    return [
                        'value' => (string) $project->id,
                        'label' => $project->title,
                    ];
                })
                ->toArray();

            return response()->json($projects);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch projects: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getUsersByProjects(Request $request): JsonResponse
    {
        try {
            $projectIds = $request->query('project_ids', []);
            if (! is_array($projectIds)) {
                $projectIds = [$projectIds];
            }
            $projectIds = array_filter($projectIds);

            if (empty($projectIds)) {
                Log::info('No project IDs provided, returning empty array');

                return response()->json([]);
            }

            $users = User::whereHas('projects', function ($query) use ($projectIds) {
                $query->whereIn('projects.id', $projectIds);
            })
                ->select('id', 'name')
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => (string) $user->id,
                        'label' => $user->name,
                    ];
                })
                ->toArray();

            Log::info('Users fetched for project_ids: ' . implode(',', $projectIds), ['count' => count($users)]);

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users: ' . $e->getMessage());

            return response()->json([], 500);
        }
    }

    public function getGanttChart(Request $request)
    {
        Log::info('getGanttChart called with parameters:', $request->all());

        $query = Task::with(['projects.directorate', 'priority']);

        if ($request->filled('directorate_id')) {
            $directorateId = $request->directorate_id;
            $query->whereHas('projects', function ($q) use ($directorateId) {
                $q->where('directorate_id', $directorateId);
            });
        }

        if ($request->filled('priority')) {
            $priorityId = $request->priority;
            $query->where('priority_id', $priorityId);
        }

        $rawTasks = $query->get();

        $tasks = $rawTasks->map(function ($task) use ($request) {
            $matchingProject = $request->filled('directorate_id')
                ? $task->projects->firstWhere('directorate_id', $request->directorate_id)
                : $task->projects->first();

            $directorateTitle = $matchingProject?->directorate?->title ?? 'N/A';
            $directorateId = $matchingProject?->directorate?->id ?? null;

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date->format('Y-m-d'),
                'end' => $task->due_date->format('Y-m-d'),
                'progress' => $task->progress ?? 0,
                'directorate' => $directorateTitle,
                'directorate_id' => $directorateId,
                'priority' => $task->priority_id ?? null,
                'priority_title' => $task->priority?->title ?? 'N/A',
                'resourceId' => $task->id % 3 + 1,
            ];
        })->filter(function ($task) use ($request) {
            $include = !$request->filled('directorate_id') || $task['directorate_id'] == $request->directorate_id;
            Log::info('Task filter check:', [
                'task_id' => $task['id'],
                'directorate_id' => $task['directorate_id'],
                'filter_directorate_id' => $request->directorate_id,
                'included' => $include,
            ]);
            return $include;
        })->values()->all();

        $availableDirectorates = Directorate::all()->pluck('title', 'id')->toArray();
        $priorities = Priority::all()->pluck('title', 'id')->toArray();

        if ($request->expectsJson()) {
            return response()->json([
                'tasks' => $tasks,
                'availableDirectorates' => $availableDirectorates,
                'priorities' => $priorities,
            ]);
        }

        return view('admin.analytics.tasks-gantt-chart', compact('tasks', 'availableDirectorates', 'priorities'));
    }

    public function analytics(Request $request)
    {
        $user = Auth::user();
        $queryString = $request->getQueryString() ?? '';
        $cacheKey = 'task_analytics_' . $user->id . '_' . md5($queryString);

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request, $user) {
            // Base query
            $query = Task::with(['status', 'priority', 'projects.directorate', 'users']);

            // Role-based filtering using Role constants
            if ($user->hasRole(Role::SUPERADMIN)) {
                Log::info('SuperAdmin role detected for user ID: ' . $user->id); // Debug log
                if ($request->filled('directorate_id')) {
                    $query->whereHas('projects.directorate', fn($q) => $q->where('id', $request->directorate_id));
                }
            } elseif ($user->hasRole(Role::DIRECTORATE_USER)) {
                $query->whereHas('projects.directorate', fn($q) => $q->where('id', $user->directorate_id));
            } elseif ($user->hasRole(Role::PROJECT_USER)) {
                $query->whereHas('projects', fn($q) => $q->whereIn('id', $user->projects->pluck('id')));
            }

            // Additional filters
            if ($request->filled('project_id')) {
                $query->whereHas('projects', fn($q) => $q->where('id', $request->project_id));
            }
            if ($request->filled('status_id')) {
                $query->where('status_id', $request->status_id);
            }
            if ($request->filled('priority_id')) {
                $query->where('priority_id', $request->priority_id);
            }

            // Fetch tasks
            $tasks = $query->latest()->paginate(10);

            // Summary metrics
            $summary = [
                'total_tasks' => $query->count(),
                'completed_tasks' => $query->clone()->whereHas('status', fn($q) => $q->where('title', 'Completed'))->count(),
                'overdue_tasks' => $query->clone()->where('due_date', '<', now())->whereHas('status', fn($q) => $q->where('title', '!=', 'Completed'))->count(),
                'average_progress' => round(
                    $query->clone()->select(DB::raw('avg(progress::integer) as avg_progress'))->withoutGlobalScopes()->reorder()->value('avg_progress') ?? 0,
                    1
                ),
            ];

            // Chart data
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

            // Filter options
            $directorates = $user->hasRole(Role::SUPERADMIN) ? Directorate::all() : collect();
            $projects = Project::whereIn('id', $query->clone()->with('projects')->get()->pluck('projects.*.id')->flatten()->unique())->get();
            $statuses = Status::all();
            $priorities = Priority::all();

            return compact('tasks', 'summary', 'charts', 'directorates', 'projects', 'statuses', 'priorities');
        });

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($data);
        }

        return view('admin.analytics.tasks-analytics', $data);
    }

    public function exportAnalytics(Request $request)
    {
        $user = Auth::user();
        $taskQuery = Task::with(['status', 'priority', 'projects.directorate', 'users']);

        // Apply same filters as analytics
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

        // Log::info('Exporting tasks:', ['query' => $taskQuery->toSql(), 'bindings' => $taskQuery->getBindings()]);

        return Excel::download(new TasksExport($taskQuery), 'tasks_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }
}
