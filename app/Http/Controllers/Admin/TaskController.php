<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Priority;
use Illuminate\View\View;
use App\Models\Department;
use App\Models\Directorate;
use App\Exports\TasksExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Notifications\TaskCreated;
use App\Notifications\TaskDeleted;
use App\Notifications\TaskUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(Gate::denies('task_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activeView = session('task_view_preference', 'board');
        $user = Auth::user();

        $statuses = Cache::remember('task_statuses', 86400, fn() => Status::all());
        $priorities = Cache::remember('task_priorities', 86400, fn() => Priority::all());
        $directorates = $user->roles->pluck('id')->contains(Role::SUPERADMIN) ? Directorate::all() : collect();
        $statusColors = $statuses->pluck('color', 'id')->toArray();
        $priorityColors = config('colors.priority');

        $data = [
            'activeView' => $activeView,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'directorates' => $directorates,
            'departments' => Cache::remember('departments', 86400, fn() => Department::all()),
            'projectsForFilter' => Cache::remember('projects_for_filter', 86400, fn() => Project::whereNull('deleted_at')->get()),
            'statusColors' => $statusColors,
            'priorityColors' => $priorityColors,
            'routePrefix' => 'admin.task',
            'deleteConfirmationMessage' => 'Are you sure you want to delete this task?',
            'actions' => ['view', 'edit', 'delete'],
        ];

        $withRelations = [
            'priority',
            'projects' => fn($query) => $query->withPivot('status_id', 'progress'),
            'users',
            'directorate',
            'parent',
            'subTasks'
        ];
        $taskQuery = Task::with($withRelations)->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();

            // Apply filters
            if ($request->filled('directorate_id') && in_array(Role::SUPERADMIN, $roleIds)) {
                $taskQuery->where('directorate_id', $request->input('directorate_id'));
            }
            if ($request->filled('department_id')) {
                $taskQuery->where('department_id', $request->input('department_id'));
            }
            if ($request->filled('priority_id')) {
                $taskQuery->where('priority_id', $request->input('priority_id'));
            }
            if ($request->filled('project_id')) {
                if ($request->input('project_id') === 'none') {
                    $taskQuery->whereDoesntHave('projects');
                } else {
                    $taskQuery->whereHas('projects', fn($q) => $q->where('projects.id', $request->input('project_id')));
                }
            }
            if ($request->filled('date_start') && $request->filled('date_end')) {
                $startDate = Carbon::parse($request->input('date_start'))->startOfDay();
                $endDate = Carbon::parse($request->input('date_end'))->endOfDay();
                $taskQuery->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('due_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('start_date', '<=', $endDate)
                                ->where('due_date', '>=', $startDate);
                        });
                });
            }

            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                $taskQuery->where(function ($query) use ($user, $roleIds) {
                    if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                        $query->where('directorate_id', $user->directorate_id);
                    }
                    if (in_array(Role::PROJECT_USER, $roleIds)) {
                        $projectIds = $user->projects()->whereNull('deleted_at')->pluck('id');
                        $query->orWhereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds)->whereNull('deleted_at'));
                    }
                    if (in_array(Role::DEPARTMENT_USER, $roleIds) && $user->directorate_id) {
                        $departmentIds = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                            ->pluck('id');
                        $query->orWhereIn('department_id', $departmentIds);
                    }
                });
            }

            $tasks = $taskQuery->get();

            $allTasks = $tasks->flatMap(function ($task) use ($statusColors, $priorityColors) {
                $results = [];
                if ($task->projects->isNotEmpty()) {
                    $results = $task->projects->filter(function ($project) {
                        return !is_null($project->id);
                    })->map(function ($project) use ($task, $statusColors, $priorityColors) {
                        $status = $project->pivot->status_id ? Status::find($project->pivot->status_id) : ($task->status_id ? Status::find($task->status_id) : null);
                        return (object) [
                            'task' => $task,
                            'project' => $project,
                            'status_id' => $project->pivot->status_id ?? $task->status_id,
                            'status' => $status,
                            'progress' => $project->pivot->progress ?? $task->progress,
                            'project_id' => $project->id,
                        ];
                    })->toArray();
                } else {
                    $status = $task->status_id ? Status::find($task->status_id) : null;
                    $results[] = (object) [
                        'task' => $task,
                        'project' => null,
                        'status_id' => $task->status_id,
                        'status' => $status,
                        'progress' => $task->progress,
                        'project_id' => null,
                    ];
                }
                return $results;
            })->filter(function ($taskItem) {
                return !is_null($taskItem->task->id);
            });

            if ($activeView === 'board') {
                $data['tasks'] = $allTasks->groupBy(function ($taskItem) {
                    return $taskItem->status_id ?? 'none';
                })->map(function ($group) use ($statusColors, $priorityColors) {
                    return $group->map(function ($taskItem) use ($statusColors, $priorityColors) {
                        $task = $taskItem->task;
                        return [
                            'id' => $task->id,
                            'title' => $task->title ?? 'Untitled Task',
                            'description' => $task->description ?? 'No description',
                            'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : null,
                            'priority_id' => $task->priority_id,
                            'status' => $taskItem->status ? ['id' => $taskItem->status->id, 'title' => $taskItem->status->title] : null,
                            'status_id' => $taskItem->status_id,
                            'status_color' => $taskItem->status ? ($statusColors[$taskItem->status->id] ?? 'gray') : 'gray',
                            'view_url' => $taskItem->project_id ? route('admin.task.show', [$task->id, $taskItem->project_id]) : route('admin.task.show', $task->id),
                            'project_id' => $taskItem->project_id,
                            'project_name' => $taskItem->project?->title ?? 'N/A',
                            'directorate_id' => $task->directorate_id ? (string) $task->directorate_id : '',
                            'directorate_name' => $task->directorate?->title ?? 'N/A',
                            'department_id' => $task->department_id ? (string) $task->department_id : '',
                            'department_name' => $task->department?->title ?? 'N/A',
                            'progress' => $taskItem->progress ?? 'N/A',
                            'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                            'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                            'parent_id' => $task->parent_id ? (string) $task->parent_id : null,
                            'parent_title' => $task->parent ? $task->parent->title : null,
                            'sub_tasks' => $task->subTasks->map(function ($subTask) use ($statusColors, $priorityColors, $taskItem) {
                                return [
                                    'id' => $subTask->id,
                                    'title' => $subTask->title ?? 'Untitled Sub-task',
                                    'description' => $subTask->description ?? 'No description',
                                    'priority' => $subTask->priority ? ['title' => $subTask->priority->title, 'color' => $priorityColors[$subTask->priority->title] ?? 'gray'] : null,
                                    'priority_id' => $subTask->priority_id,
                                    'status' => $subTask->status ? ['id' => $subTask->status->id, 'title' => $subTask->status->title] : null,
                                    'status_id' => $subTask->status_id,
                                    'status_color' => $subTask->status ? ($statusColors[$subTask->status->id] ?? 'gray') : 'gray',
                                    'view_url' => $taskItem->project_id ? route('admin.task.show', [$subTask->id, $taskItem->project_id]) : route('admin.task.show', $subTask->id),
                                    'project_id' => $taskItem->project_id,
                                    'project_name' => $taskItem->project?->title ?? 'N/A',
                                    'directorate_id' => $subTask->directorate_id ? (string) $subTask->directorate_id : '',
                                    'directorate_name' => $subTask->directorate?->title ?? 'N/A',
                                    'department_id' => $subTask->department_id ? (string) $subTask->department_id : '',
                                    'department_name' => $subTask->department?->title ?? 'N/A',
                                    'start_date' => $subTask->start_date ? $subTask->start_date->format('Y-m-d') : null,
                                    'due_date' => $subTask->due_date ? $subTask->due_date->format('Y-m-d') : null,
                                ];
                            })->values()->toArray(),
                        ];
                    })->values();
                });
                $data['taskCounts'] = $data['tasks']->map->count()->toArray();
            } elseif ($activeView === 'list') {
                $data['tasksFlat'] = $allTasks->map(function ($taskItem) use ($statusColors, $priorityColors) {
                    $task = $taskItem->task;
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $taskItem->status ? $taskItem->status->title : 'N/A',
                        'status_id' => $taskItem->status_id,
                        'progress' => $taskItem->progress ?? 'N/A',
                        'priority' => $task->priority ? $task->priority->title : 'N/A',
                        'priority_id' => $task->priority_id,
                        'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : 'N/A',
                        'projects' => $taskItem->project ? [$taskItem->project->title] : ['N/A'],
                        'users' => $task->users->pluck('name')->all(),
                        'directorate' => $task->directorate?->title ?? 'N/A',
                        'directorate_id' => $task->directorate_id ? (string) $task->directorate_id : '',
                        'department_id' => $task->department_id ? (string) $task->department_id : '',
                        'project_id' => $taskItem->project_id,
                        'view_url' => $taskItem->project_id ? route('admin.task.show', [$task->id, $taskItem->project_id]) : route('admin.task.show', $task->id),
                        'parent_id' => $task->parent_id ? (string) $task->parent_id : null,
                        'parent_title' => $task->parent ? $task->parent->title : null,
                    ];
                })->values();
            } elseif ($activeView === 'calendar') {
                $data['calendarData'] = $allTasks->map(function ($taskItem) use ($statusColors) {
                    $task = $taskItem->task;
                    $startDate = $task->start_date ? Carbon::parse($task->start_date) : ($task->due_date ? Carbon::parse($task->due_date) : null);
                    $endDate = $task->due_date ? Carbon::parse($task->due_date) : null;
                    return [
                        'id' => $task->id,
                        'title' => $task->title ?? 'Untitled Task',
                        'start' => $startDate ? $startDate->format('Y-m-d') : null,
                        'end' => $endDate ? $endDate->addDay()->format('Y-m-d') : null,
                        'color' => $taskItem->status ? ($statusColors[$taskItem->status->id] ?? 'gray') : 'gray',
                        'url' => $taskItem->project_id ? route('admin.task.show', [$task->id, $taskItem->project_id]) : route('admin.task.show', $task->id),
                        'extendedProps' => [
                            'status' => $taskItem->status ? $taskItem->status->title : 'N/A',
                            'progress' => $taskItem->progress ?? 'N/A',
                            'priority' => $task->priority ? $task->priority->title : 'N/A',
                            'priority_id' => $task->priority_id,
                            'project' => $taskItem->project?->title ?? 'N/A',
                            'users' => $task->users->pluck('name')->all(),
                            'directorate' => $task->directorate?->title ?? 'N/A',
                            'directorate_id' => $task->directorate_id ? (string) $task->directorate_id : '',
                            'department_id' => $task->department_id ? (string) $task->department_id : '',
                            'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                            'project_id' => $taskItem->project_id,
                            'parent_id' => $task->parent_id ? (string) $task->parent_id : null,
                            'parent_title' => $task->parent ? $task->parent->title : null,
                        ],
                    ];
                })->filter(fn($event) => $event['start'] !== null)->values()->all();
            } elseif ($activeView === 'table') {
                $data['tableHeaders'] = [
                    trans('global.task.fields.id'),
                    trans('global.task.fields.title'),
                    trans('global.task.fields.project_id'),
                    trans('global.task.fields.parent_id'),
                    trans('global.details'),
                ];
                $data['tableData'] = $allTasks->map(function ($taskItem) use ($statusColors, $priorityColors) {
                    $task = $taskItem->task;
                    $viewUrl = $taskItem->project_id ? route('admin.task.show', [$task->id, $taskItem->project_id]) : route('admin.task.show', $task->id);
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'project' => $taskItem->project?->title ?? 'N/A',
                        'parent_id' => $task->parent_id ? (string) $task->parent_id : null,
                        'parent_title' => $task->parent ? $task->parent->title : null,
                        'details' => [
                            'status' => $taskItem->status ? ['title' => $taskItem->status->title, 'color' => $statusColors[$taskItem->status->id] ?? 'gray'] : ['title' => 'N/A', 'color' => 'gray'],
                            'progress' => $taskItem->progress ?? 'N/A',
                            'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : ['title' => 'N/A', 'color' => 'gray'],
                            'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : 'N/A',
                            'users' => $task->users->pluck('name')->toArray(),
                            'directorate' => $task->directorate ? ['title' => $task->directorate->title, 'color' => $statusColors[$task->directorate->id] ?? 'gray'] : ['title' => 'N/A', 'color' => 'gray'],
                        ],
                        'project_id' => $taskItem->project_id,
                        'directorate_id' => $task->directorate_id ? (string) $task->directorate_id : '',
                        'department_id' => $task->department_id ? (string) $task->department_id : '',
                        'search_data' => strtolower(
                            $task->title . ' ' .
                                ($taskItem->status ? $taskItem->status->title : '') . ' ' .
                                ($task->priority ? $task->priority->title : '') . ' ' .
                                ($task->due_date ? $task->due_date->format('Y-m-d') : '') . ' ' .
                                ($taskItem->project?->title ?? '') . ' ' .
                                ($task->users->pluck('name')->join(' ') ?? '') . ' ' .
                                ($task->directorate?->title ?? '') . ' ' .
                                ($task->directorate_id ?? '') . ' ' .
                                ($task->department_id ?? '') . ' ' .
                                ($task->parent ? $task->parent->title : '')
                        ),
                    ];
                })->values()->toArray();
            }
        } catch (\Exception $e) {
            $data['error'] = 'Unable to load tasks due to an error: ' . $e->getMessage();
        }

        return view('admin.tasks.index', $data);
    }

    public function create(Request $request): View
    {
        abort_if(Gate::denies('task_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        $projectId = $request->query('project_id');
        $directorates = collect();
        $departments = collect();
        $projects = collect();
        $users = collect();
        $tasks = collect();
        $preselectedData = null;

        if ($projectId) {
            $project = Project::where('id', $projectId)
                ->whereNull('deleted_at')
                ->when(!in_array(Role::SUPERADMIN, $roleIds), function ($query) use ($user, $roleIds) {
                    if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                        $query->where('directorate_id', $user->directorate_id);
                    } elseif (in_array(Role::PROJECT_USER, $roleIds)) {
                        $query->whereIn('id', $user->projects()->whereNull('deleted_at')->pluck('id'));
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                })
                ->first();

            if ($project) {
                $directorateId = $project->directorate_id;
                $directorate = $project->directorate;
                $directorates = $directorate ? collect([$directorate->id => $directorate->title]) : collect();
                $department = $project->department_id ? Department::find($project->department_id) : null;
                $departments = $project->department_id ? collect([$project->department_id => $department?->title ?? 'N/A']) : collect();
                $projects = collect([$project->id => $project->title]);
                $users = $project->users()->pluck('name', 'id');
                $tasks = Task::whereHas('projects', fn($q) => $q->where('projects.id', $projectId))->pluck('title', 'id');
                $preselectedData = [
                    'directorate_id' => $directorateId ? (string) $directorateId : null,
                    'department_id' => $project->department_id ? (string) $project->department_id : null,
                    'projects' => [(string) $project->id],
                    'users' => $users->keys()->map(fn($id) => (string) $id)->toArray(),
                ];
            } else {
                $projectId = null;
            }
        }

        if (!$projectId) {
            if (in_array(Role::SUPERADMIN, $roleIds)) {
                $directorates = Directorate::pluck('title', 'id');
                $departments = Department::pluck('title', 'id');
                $projects = Project::whereNull('deleted_at')->pluck('title', 'id');
                $users = User::pluck('name', 'id');
                $tasks = Task::pluck('title', 'id');
            } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
                $departments = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                    ->pluck('title', 'id');
                $projects = Project::where('directorate_id', $user->directorate_id)
                    ->whereNull('deleted_at')
                    ->pluck('title', 'id');
                $users = User::where('directorate_id', $user->directorate_id)
                    ->whereHas('roles', fn($q) => $q->where('id', Role::DIRECTORATE_USER))
                    ->pluck('name', 'id');
                $tasks = Task::where('directorate_id', $user->directorate_id)->pluck('title', 'id');
            } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
                $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
                $departments = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                    ->pluck('title', 'id');
                $projects = $user->projects()
                    ->whereNull('deleted_at')
                    ->pluck('title', 'id');
                $users = User::where(function ($q) use ($projects, $user) {
                    $q->whereHas('projects', fn($pq) => $pq->whereIn('projects.id', $projects->keys()->toArray()))
                        ->orWhere(function ($q2) use ($user) {
                            $q2->where('directorate_id', $user->directorate_id)
                                ->whereHas('roles', fn($rq) => $rq->where('id', Role::DIRECTORATE_USER));
                        });
                })->pluck('name', 'id');
                $tasks = Task::whereHas('projects', fn($q) => $q->whereIn('projects.id', $projects->keys()->toArray()))->pluck('title', 'id');
            }
        }

        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');

        return view('admin.tasks.create', compact('directorates', 'departments', 'projects', 'users', 'statuses', 'priorities', 'preselectedData', 'tasks'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('task_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validated();
        $validated['assigned_by'] = Auth::id();
        $taskData = array_diff_key($validated, array_flip(['progress', 'projects', 'users']));
        $task = Task::create($taskData);

        if (!empty($validated['projects'])) {
            $projectSyncData = [];
            foreach ($validated['projects'] as $projectId) {
                $projectSyncData[$projectId] = [
                    'status_id' => $validated['status_id'],
                    'progress' => $validated['progress'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $task->projects()->sync($projectSyncData);
        }

        $task->users()->sync($validated['users'] ?? []);

        $notifiedUsers = collect();
        if (!empty($validated['projects'])) {
            foreach ($validated['projects'] as $projectId) {
                $project = Project::findOrFail($projectId);
                $users = $project->users;
                foreach ($users as $user) {
                    if (!$notifiedUsers->contains($user->id)) {
                        $user->notify(new TaskCreated($task, $projectId));
                        $notifiedUsers->push($user->id);
                    }
                }
            }
        }

        if (!empty($validated['department_id'])) {
            $department = Department::findOrFail($validated['department_id']);
            $directorateIds = $department->directorates()->pluck('directorates.id');
            $users = User::whereIn('directorate_id', $directorateIds)
                ->whereHas('roles', fn($q) => $q->where('id', Role::DEPARTMENT_USER))
                ->get();
            foreach ($users as $user) {
                if (!$notifiedUsers->contains($user->id)) {
                    $user->notify(new TaskCreated($task, null));
                    $notifiedUsers->push($user->id);
                }
            }
        } elseif (!empty($validated['directorate_id']) && empty($validated['projects'])) {
            $users = User::where('directorate_id', $validated['directorate_id'])
                ->whereHas('roles', fn($q) => $q->where('id', Role::DIRECTORATE_USER))
                ->get();
            foreach ($users as $user) {
                if (!$notifiedUsers->contains($user->id)) {
                    $user->notify(new TaskCreated($task, null));
                    $notifiedUsers->push($user->id);
                }
            }
        }

        if (!empty($validated['parent_id'])) {
            $parentTask = Task::findOrFail($validated['parent_id']);
            foreach ($parentTask->users as $user) {
                if (!$notifiedUsers->contains($user->id)) {
                    $user->notify(new TaskCreated($task, null));
                    $notifiedUsers->push($user->id);
                }
            }
        }

        return redirect()->route('admin.task.index')
            ->with('message', 'Task created successfully.');
    }

    public function getDepartments($directorateId): JsonResponse
    {
        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        try {
            if (!Directorate::where('id', $directorateId)->exists()) {
                return response()->json(['message' => 'Invalid directorate ID'], 400);
            }

            $query = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $directorateId));

            if (in_array(Role::SUPERADMIN, $roleIds)) {
                // No additional filtering needed
            } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                if ($user->directorate_id != $directorateId) {
                    return response()->json(['message' => 'Unauthorized: You can only fetch departments in your directorate.'], 403);
                }
            } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
                if ($user->directorate_id != $directorateId) {
                    return response()->json(['message' => 'Unauthorized: You can only fetch departments in your directorate.'], 403);
                }
            } else {
                return response()->json(['message' => 'Unauthorized: No valid role or directorate assigned.'], 403);
            }

            $departments = $query->get()->map(function ($department) {
                return [
                    'value' => (string) $department->id,
                    'label' => $department->title,
                ];
            })->values()->toArray();

            return response()->json($departments);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch departments: ' . $e->getMessage()], 500);
        }
    }

    public function getUsersByDirectorateOrDepartment(Request $request): JsonResponse
    {
        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        $directorateId = $request->query('directorate_id');
        $departmentId = $request->query('department_id');

        try {
            if (!$directorateId && !$departmentId) {
                return response()->json([]);
            }

            $query = User::query();

            if (in_array(Role::SUPERADMIN, $roleIds)) {
                // SUPERADMIN can see all users
            } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                $query->where('directorate_id', $user->directorate_id);
            } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
                $query->where('directorate_id', $user->directorate_id);
            } else {
                return response()->json(['message' => 'Unauthorized: No valid role or directorate assigned.'], 403);
            }

            if ($departmentId) {
                $department = Department::findOrFail($departmentId);
                $directorateIds = $department->directorates()->pluck('directorates.id');
                $query->whereIn('directorate_id', $directorateIds)
                    ->whereHas('roles', fn($q) => $q->where('id', Role::DEPARTMENT_USER));
            } elseif ($directorateId) {
                $query->where('directorate_id', $directorateId)
                    ->whereHas('roles', fn($q) => $q->where('id', Role::DIRECTORATE_USER));
            }

            $users = $query->get()->map(function ($user) {
                return [
                    'value' => (string) $user->id,
                    'label' => $user->name,
                ];
            })->values()->toArray();

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch users: ' . $e->getMessage()], 500);
        }
    }

    public function show(Task $task, $project_id = null): View
    {
        abort_if(Gate::denies('task_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $task->load(['priority', 'projects' => fn($query) => $query->withPivot('status_id', 'progress'), 'users', 'directorate', 'department', 'subTasks', 'parent']);

        $project = null;
        if ($project_id !== null) {
            if (!is_numeric($project_id)) {
                abort(404, 'Invalid project ID');
            }
            $project = Project::find($project_id);
            if (!$project) {
                abort(404, 'Project not found');
            }
            $projectTask = $task->projects()->where('project_id', $project->id)->first();
            if (!$projectTask) {
                abort(404, 'Task not associated with this project');
            }
        }

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        if ($project) {
            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                $canAccess = false;
                if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id && $project->directorate_id == $user->directorate_id) {
                    $canAccess = true;
                } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->projects()->where('id', $project->id)->exists()) {
                    $canAccess = true;
                }
                if (!$canAccess) {
                    abort(403, 'Unauthorized: You do not have access to this project.');
                }
            }
        }

        $statusId = $project ? ($task->projects()->where('project_id', $project->id)->first()->pivot->status_id ?? $task->status_id) : $task->status_id;
        $status = Status::find($statusId);
        $progress = $project ? ($task->projects()->where('project_id', $project->id)->first()->pivot->progress ?? $task->progress) : $task->progress;

        $statusColors = Status::all()->pluck('color', 'id')->toArray();
        $priorityColors = config('colors.priority', []);

        $taskData = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : null,
            'status' => $status ? ['id' => $status->id, 'title' => $status->title, 'color' => $statusColors[$status->id] ?? 'gray'] : null,
            'status_id' => $statusId,
            'progress' => $progress,
            'project_id' => $project?->id,
            'project_name' => $project?->title ?? 'N/A',
            'projects' => $task->projects->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'status_id' => $p->pivot->status_id,
                'progress' => $p->pivot->progress,
            ])->toArray(),
            'users' => $task->users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'initials' => $user->initials(),
            ])->toArray(),
            'directorate_id' => $task->directorate_id ? (string) $task->directorate_id : '',
            'directorate_name' => $task->directorate?->title ?? 'N/A',
            'department_id' => $task->department_id ? (string) $task->department_id : '',
            'department_name' => $task->department?->title ?? 'N/A',
            'start_date' => $task->start_date,
            'due_date' => $task->due_date,
            'completion_date' => $task->completion_date,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
            'parent_id' => $task->parent_id,
            'parent_title' => $task->parent ? $task->parent->title : 'N/A',
            'sub_tasks' => $task->subTasks->map(fn($subTask) => [
                'id' => $subTask->id,
                'title' => $subTask->title,
                'description' => $subTask->description,
                'status' => $subTask->status ? ['id' => $subTask->status->id, 'title' => $subTask->status->title, 'color' => $statusColors[$subTask->status->id] ?? 'gray'] : null,
                'progress' => $subTask->progress ?? 'N/A',
                'priority' => $subTask->priority ? ['title' => $subTask->priority->title, 'color' => $priorityColors[$subTask->priority->title] ?? 'gray'] : null,
                'start_date' => $subTask->start_date ? $subTask->start_date->format('Y-m-d') : null,
                'due_date' => $subTask->due_date ? $subTask->due_date->format('Y-m-d') : null,
                'view_url' => $project ? route('admin.task.show', [$subTask->id, $project->id]) : route('admin.task.show', $subTask->id),
            ])->toArray(),
        ];

        $comments = Comment::forTaskProject($task->id, $project?->id)
            ->with(['user', 'replies' => fn($query) => $query->orderBy('created_at', 'asc')])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        try {
            $commentIds = $user->comments()
                ->where('commentable_type', Task::class)
                ->where('commentable_id', $task->id)
                ->where('project_id', $project?->id)
                ->whereNull('comment_user.read_at')
                ->pluck('comments.id');

            foreach ($commentIds as $commentId) {
                $user->comments()->updateExistingPivot($commentId, ['read_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to mark comments as read for task {$task->id}, project " . ($project?->id ?? 'null'), [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }

        return view('admin.tasks.show', [
            'task' => $taskData,
            'comments' => $comments,
            'statusColors' => $statusColors,
            'priorityColors' => $priorityColors,
            'statuses' => Status::all(),
        ]);
    }

    public function edit(Task $task, $project_id = null): View
    {
        abort_if(Gate::denies('task_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $task->load(['priority', 'projects' => fn($query) => $query->withPivot('status_id', 'progress'), 'users', 'directorate', 'department', 'subTasks', 'parent']);

        $project = null;
        if ($project_id !== null) {
            if (!is_numeric($project_id)) {
                abort(404, 'Invalid project ID');
            }
            $project = Project::find($project_id);
            if (!$project) {
                abort(404, 'Project not found');
            }
            $projectTask = $task->projects()->where('project_id', $project->id)->first();
            if (!$projectTask) {
                abort(404, 'Task not associated with this project');
            }
        }

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        if ($project) {
            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                $canAccess = false;
                if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id && $project->directorate_id == $user->directorate_id) {
                    $canAccess = true;
                } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->projects()->where('id', $project->id)->exists()) {
                    $canAccess = true;
                }
                if (!$canAccess) {
                    abort(403, 'Unauthorized: You do not have access to this project.');
                }
            }
        }

        $directorates = collect();
        $departments = collect();
        $projects = collect();
        $users = collect();
        $tasks = collect();
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');

        if (in_array(Role::SUPERADMIN, $roleIds)) {
            $directorates = Directorate::pluck('title', 'id');
            $departments = Department::pluck('title', 'id');
            $projects = Project::whereNull('deleted_at')->pluck('title', 'id');
            $users = User::pluck('name', 'id');
            $tasks = Task::where('id', '!=', $task->id)->pluck('title', 'id');
        } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
            $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
            $departments = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                ->pluck('title', 'id');
            $projects = Project::where('directorate_id', $user->directorate_id)
                ->whereNull('deleted_at')
                ->pluck('title', 'id');
            $users = User::where('directorate_id', $user->directorate_id)
                ->whereHas('roles', fn($q) => $q->where('id', Role::DIRECTORATE_USER))
                ->pluck('name', 'id');
            $tasks = Task::where('directorate_id', $user->directorate_id)
                ->where('id', '!=', $task->id)
                ->pluck('title', 'id');
            if ($task->directorate_id && $task->directorate_id != $user->directorate_id) {
                $directorates->put($task->directorate_id, Directorate::find($task->directorate_id)?->title ?? 'N/A');
            }
        } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
            $directorates = collect([$user->directorate_id => Directorate::find($user->directorate_id)?->title ?? 'N/A']);
            $departments = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                ->pluck('title', 'id');
            $projects = $user->projects()
                ->whereNull('deleted_at')
                ->pluck('title', 'id');
            $users = User::whereHas('projects', fn($q) => $q->whereIn('projects.id', $projects->keys()->toArray()))
                ->pluck('name', 'id');
            $tasks = Task::whereHas('projects', fn($q) => $q->whereIn('projects.id', $projects->keys()->toArray()))
                ->where('id', '!=', $task->id)
                ->pluck('title', 'id');
            if ($task->directorate_id && $task->directorate_id != $user->directorate_id) {
                $directorates->put($task->directorate_id, Directorate::find($task->directorate_id)?->title ?? 'N/A');
            }
        }

        $statusId = $project ? ($task->projects()->where('project_id', $project->id)->first()->pivot->status_id ?? $task->status_id) : $task->status_id;
        $progress = $project ? ($task->projects()->where('project_id', $project->id)->first()->pivot->progress ?? $task->progress) : $task->progress;

        return view('admin.tasks.edit', compact('task', 'project', 'directorates', 'departments', 'projects', 'users', 'statuses', 'priorities', 'statusId', 'progress', 'tasks'));
    }

    public function update(UpdateTaskRequest $request, Task $task, ?Project $project = null): RedirectResponse
    {
        abort_if(Gate::denies('task_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $validated = $request->validated();

            $taskData = array_diff_key($validated, array_flip(['status_id', 'progress', 'projects', 'users']));
            $task->update($taskData);

            if (!empty($validated['projects'])) {
                $projectSyncData = [];
                foreach ($validated['projects'] as $projectId) {
                    $projectSyncData[$projectId] = [
                        'status_id' => $project && $project->id == $projectId ? ($validated['status_id'] ?? $task->projects()->where('project_id', $projectId)->first()->pivot->status_id) : $task->status_id,
                        'progress' => $project && $project->id == $projectId ? ($validated['progress'] ?? $task->projects()->where('project_id', $projectId)->first()->pivot->progress) : $task->progress,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $task->projects()->sync($projectSyncData);
            } else {
                $task->projects()->detach();
            }

            if ($project && $project->id) {
                $projectTask = $task->projects()->where('project_id', $project->id)->first();
                if ($projectTask) {
                    $pivotData = [
                        'status_id' => $validated['status_id'] ?? $projectTask->pivot->status_id,
                        'progress' => $validated['progress'] ?? $projectTask->pivot->progress,
                        'updated_at' => now(),
                    ];
                    $task->projects()->updateExistingPivot($project->id, $pivotData);
                }
            } else {
                $task->update([
                    'status_id' => $validated['status_id'] ?? $task->status_id,
                    'progress' => $validated['progress'] ?? $task->progress,
                ]);
            }

            if (isset($validated['users'])) {
                $task->users()->sync($validated['users']);
            }

            if (isset($validated['parent_id']) && $task->parent_id != $validated['parent_id']) {
                if ($validated['parent_id']) {
                    $parentTask = Task::findOrFail($validated['parent_id']);
                    foreach ($parentTask->users as $user) {
                        $user->notify(new TaskUpdated($task));
                    }
                }
            }

            return redirect()->route('admin.task.show', [$task->id, $project?->id])
                ->with('message', 'Task updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update task: ' . $e->getMessage());
        }
    }

    public function destroy(Task $task): RedirectResponse
    {
        abort_if(Gate::denies('task_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $task->delete();

        return redirect()->route('admin.task.index')
            ->with('message', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'status_id' => 'required|exists:statuses,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $task = Task::findOrFail($request->task_id);
        $statusId = $request->status_id;
        $projectId = $request->project_id;

        try {
            if ($projectId) {
                $task->projects()->updateExistingPivot($projectId, ['status_id' => $statusId]);
            } else {
                $task->update(['status_id' => $statusId]);
            }

            return response()->json(['message' => 'Task status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating task status'], 500);
        }
    }

    public function setViewPreference(Request $request): JsonResponse
    {
        $request->session()->put('task_view_preference', $request->input('task_view_preference'));

        return response()->json(['success' => true]);
    }

    public function getProjects($directorateId): JsonResponse
    {
        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        try {
            if (!Directorate::where('id', $directorateId)->exists()) {
                return response()->json(['message' => 'Invalid directorate ID'], 400);
            }

            $query = Project::where('directorate_id', $directorateId)->whereNull('deleted_at');

            if (in_array(Role::SUPERADMIN, $roleIds)) {
                // No additional filtering needed
            } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                if ($user->directorate_id != $directorateId) {
                    return response()->json(['message' => 'Unauthorized: You can only fetch projects in your directorate.'], 403);
                }
            } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
                if ($user->directorate_id != $directorateId) {
                    return response()->json(['message' => 'Unauthorized: You can only fetch projects in your directorate.'], 403);
                }
                $userProjectIds = $user->projects()->whereNull('deleted_at')->pluck('id')->toArray();
                $query->whereIn('id', $userProjectIds);
            } else {
                return response()->json(['message' => 'Unauthorized: No valid role or directorate assigned.'], 403);
            }

            $projects = $query->get()->map(function ($project) {
                return [
                    'value' => (string) $project->id,
                    'label' => $project->title,
                ];
            })->values()->toArray();

            return response()->json($projects);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch projects: ' . $e->getMessage()], 500);
        }
    }

    public function getUsersByProjects(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleIds = $user->roles->pluck('id')->toArray();
            $projectIds = array_filter((array) $request->query('project_ids', []));

            if (empty($projectIds)) {
                return response()->json([]);
            }

            if (!Project::whereIn('id', $projectIds)->exists()) {
                return response()->json(['message' => 'Invalid project IDs'], 400);
            }

            $query = User::whereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds)->whereNull('projects.deleted_at'));

            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                    $validProjectIds = Project::whereIn('id', $projectIds)
                        ->where('directorate_id', $user->directorate_id)
                        ->pluck('id')
                        ->toArray();
                    if (count($validProjectIds) !== count($projectIds)) {
                        return response()->json(['message' => 'Unauthorized: You can only fetch users for projects in your directorate.'], 403);
                    }
                    $query->where('directorate_id', $user->directorate_id);
                } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->directorate_id) {
                    $userProjectIds = $user->projects()->whereNull('deleted_at')->pluck('id')->toArray();
                    $validProjectIds = Project::whereIn('id', $projectIds)
                        ->whereIn('id', $userProjectIds)
                        ->where('directorate_id', $user->directorate_id)
                        ->pluck('id')
                        ->toArray();
                    if (count($validProjectIds) !== count($projectIds)) {
                        return response()->json(['message' => 'Unauthorized: You can only fetch users for your assigned projects in your directorate.'], 403);
                    }
                    $query->whereHas('projects', fn($q) => $q->whereIn('projects.id', $userProjectIds));
                } else {
                    return response()->json(['message' => 'Unauthorized: No valid role or directorate assigned.'], 403);
                }
            }

            $users = $query->select('id', 'name')
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => (string) $user->id,
                        'label' => $user->name,
                    ];
                })
                ->values()
                ->toArray();

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch users: ' . $e->getMessage()], 500);
        }
    }

    public function getProjectInfo($projectId): JsonResponse
    {
        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        try {
            $project = Project::with('users')->findOrFail($projectId);

            $canAccess = false;

            if (in_array(Role::SUPERADMIN, $roleIds)) {
                $canAccess = true;
            } elseif (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id == $project->directorate_id) {
                $canAccess = true;
            } elseif (in_array(Role::PROJECT_USER, $roleIds) && $user->projects()->where('id', $projectId)->exists()) {
                $canAccess = true;
            }

            if (!$canAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $data = [
                'directorate_id' => (string) $project->directorate_id,
                'projects' => [
                    ['value' => (string) $project->id, 'label' => $project->title]
                ],
                'users' => $project->users->map(function ($user) {
                    return ['value' => (string) $user->id, 'label' => $user->name];
                })->toArray(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch project info: ' . $e->getMessage()], 500);
        }
    }

    public function getGanttChart(Request $request)
    {
        $query = Task::with(['projects.directorate', 'priority', 'assignedBy']);

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
            $projectName = $matchingProject?->name ?? 'N/A';
            $assignedUser = $task->assignedBy?->name ?? 'N/A';

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                'end' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'progress' => $task->progress ?? 0,
                'directorate' => $directorateTitle,
                'directorate_id' => $directorateId,
                'project_name' => $projectName,
                'assigned_user' => $assignedUser,
                'parent_id' => $task->parent_id,
            ];
        })->filter(function ($task) use ($request) {
            $include = !$request->filled('directorate_id') || $task['directorate_id'] == $request->directorate_id;
            return $include && $task['start'] && $task['end'];
        })->values()->all();

        // Prepare links for parent-to-child relationships
        $links = [];
        foreach ($tasks as $task) {
            if ($task['parent_id']) {
                $links[] = [
                    'id' => $task['id'] . '_link_' . $task['parent_id'],
                    'source' => $task['parent_id'],
                    'target' => $task['id'],
                    'type' => 0, // finish-to-start
                ];
            }
        }

        $availableDirectorates = Directorate::all()->pluck('title', 'id')->toArray();
        $priorities = Priority::all()->pluck('title', 'id')->toArray();

        if ($request->expectsJson()) {
            return response()->json([
                'tasks' => $tasks,
                'links' => $links,
                'availableDirectorates' => $availableDirectorates,
                'priorities' => $priorities,
            ]);
        }

        return view('admin.analytics.tasks-gantt-chart', compact('tasks', 'links', 'availableDirectorates', 'priorities'));
    }

    public function analytics(Request $request)
    {
        $user = Auth::user();
        $queryString = $request->getQueryString() ?? '';
        $cacheKey = 'task_analytics_' . $user->id . '_' . md5($queryString);

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request, $user) {
            $query = Task::with(['status', 'priority', 'projects.directorate', 'users']);

            if ($user->hasRole(Role::SUPERADMIN)) {
                if ($request->filled('directorate_id')) {
                    $query->whereHas('projects', fn($q) => $q->where('directorate_id', $request->directorate_id));
                }
            } elseif ($user->hasRole(Role::DIRECTORATE_USER)) {
                $query->whereHas('projects', fn($q) => $q->where('directorate_id', $user->directorate_id));
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

            return compact('tasks', 'summary', 'charts', 'directorates', 'projects', 'statuses', 'priorities');
        });

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($data);
        }

        return view('admin.analytics.tasks-analytics', $data);
    }

    public function exportAnalytics(Request $request)
    {
        $user = Auth::user();
        $taskQuery = Task::with(['status', 'priority', 'projects.directorate', 'users']);

        if ($user->hasRole('SuperAdmin')) {
            if ($request->filled('directorate_id')) {
                $taskQuery->whereHas('projects', fn($q) => $q->where('directorate_id', $request->directorate_id));
            }
        } elseif ($user->hasRole('Directorate User')) {
            $taskQuery->whereHas('projects', fn($q) => $q->where('directorate_id', $user->directorate_id));
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

    public function loadMore(Request $request)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'offset' => 'required|integer|min:0',
            'directorate_id' => 'nullable|exists:directorates,id',
            'project_id' => 'nullable|exists:projects,id',
            'priority_id' => 'nullable|exists:priorities,id',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
        ]);

        $query = Task::query()->with([
            'priority',
            'projects' => fn($query) => $query->withPivot('status_id', 'progress'),
            'users',
            'directorate',
            'parent',
            'subTasks',
        ])->whereNull('parent_id');

        $query->where('status_id', $validated['status_id']);

        if ($validated['directorate_id']) {
            $query->where('directorate_id', $validated['directorate_id']);
        }
        if ($validated['project_id']) {
            if ($validated['project_id'] === 'none') {
                $query->whereDoesntHave('projects');
            } else {
                $query->whereHas('projects', fn($q) => $q->where('projects.id', $validated['project_id']));
            }
        }
        if ($validated['priority_id']) {
            $query->where('priority_id', $validated['priority_id']);
        }
        if ($validated['date_start'] && $validated['date_end']) {
            $startDate = Carbon::parse($validated['date_start'])->startOfDay();
            $endDate = Carbon::parse($validated['date_end'])->endOfDay();
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('due_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $endDate)
                            ->where('due_date', '>=', $startDate);
                    });
            });
        }

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        if (!in_array(Role::SUPERADMIN, $roleIds)) {
            $query->where(function ($query) use ($user, $roleIds) {
                if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                    $query->where('directorate_id', $user->directorate_id);
                }
                if (in_array(Role::PROJECT_USER, $roleIds)) {
                    $projectIds = $user->projects()->whereNull('deleted_at')->pluck('id');
                    $query->orWhereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds)->whereNull('deleted_at'));
                }
                if (in_array(Role::DEPARTMENT_USER, $roleIds) && $user->directorate_id) {
                    $departmentIds = Department::whereHas('directorates', fn($q) => $q->where('directorates.id', $user->directorate_id))
                        ->pluck('id');
                    $query->orWhereIn('department_id', $departmentIds);
                }
            });
        }

        $tasks = $query->skip($validated['offset'])->take(5)->get()->map(function ($task) use ($validated) {
            $statusColors = Status::all()->pluck('color', 'id')->toArray();
            $priorityColors = config('colors.priority');
            $project = $task->projects->first();
            $status = $project && $project->pivot->status_id ? Status::find($project->pivot->status_id) : ($task->status_id ? Status::find($task->status_id) : null);

            return [
                'id' => $task->id,
                'title' => $task->title ?? 'Untitled Task',
                'description' => $task->description ?? 'No description',
                'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : null,
                'priority_id' => $task->priority_id,
                'status' => $status ? ['id' => $status->id, 'title' => $status->title] : null,
                'status_id' => $status ? $status->id : $task->status_id,
                'status_color' => $status ? ($statusColors[$status->id] ?? 'gray') : 'gray',
                'view_url' => $project ? route('admin.task.show', [$task->id, $project->id]) : route('admin.task.show', $task->id),
                'project_id' => $project ? $project->id : null,
                'project_name' => $project ? $project->title : 'N/A',
                'directorate_id' => $task->directorate_id ? (string) $task->directorate_id : '',
                'directorate_name' => $task->directorate?->title ?? 'N/A',
                'department_id' => $task->department_id ? (string) $task->department_id : '',
                'department_name' => $task->department?->title ?? 'N/A',
                'progress' => $project ? ($project->pivot->progress ?? $task->progress) : $task->progress,
                'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'parent_id' => $task->parent_id ? (string) $task->parent_id : null,
                'parent_title' => $task->parent ? $task->parent->title : null,
                'sub_tasks' => $task->subTasks->map(function ($subTask) use ($statusColors, $priorityColors, $project) {
                    return [
                        'id' => $subTask->id,
                        'title' => $subTask->title ?? 'Untitled Sub-task',
                        'description' => $subTask->description ?? 'No description',
                        'priority' => $subTask->priority ? ['title' => $subTask->priority->title, 'color' => $priorityColors[$subTask->priority->title] ?? 'gray'] : null,
                        'priority_id' => $subTask->priority_id,
                        'status' => $subTask->status ? ['id' => $subTask->status->id, 'title' => $subTask->status->title] : null,
                        'status_id' => $subTask->status_id,
                        'status_color' => $subTask->status ? ($statusColors[$subTask->status->id] ?? 'gray') : 'gray',
                        'view_url' => $project ? route('admin.task.show', [$subTask->id, $project->id]) : route('admin.task.show', $subTask->id),
                        'project_id' => $project ? $project->id : null,
                        'project_name' => $project ? $project->title : 'N/A',
                        'directorate_id' => $subTask->directorate_id ? (string) $subTask->directorate_id : '',
                        'directorate_name' => $subTask->directorate?->title ?? 'N/A',
                        'department_id' => $subTask->department_id ? (string) $subTask->department_id : '',
                        'department_name' => $subTask->department?->title ?? 'N/A',
                        'start_date' => $subTask->start_date ? $subTask->start_date->format('Y-m-d') : null,
                        'due_date' => $subTask->due_date ? $subTask->due_date->format('Y-m-d') : null,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return response()->json([
            'tasks' => $tasks,
            'has_more' => $query->skip($validated['offset'] + 5)->take(1)->exists(),
        ]);
    }
}
