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

class TaskController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('task_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activeView = session('task_view_preference', 'board');
        $user = Auth::user();

        $statuses = Cache::remember('task_statuses', 86400, fn() => Status::all());
        $priorities = Cache::remember('task_priorities', 86400, fn() => Priority::all());
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
            'projectsForFilter' => Cache::remember('projects_for_filter', 86400, fn() => Project::whereNull('deleted_at')->get()),
            'statusColors' => $statusColors,
            'priorityColors' => $priorityColors,
            'routePrefix' => 'admin.task',
            'deleteConfirmationMessage' => 'Are you sure you want to delete this task?',
            'actions' => ['view', 'edit', 'delete'],
        ];

        $withRelations = ['priority', 'projects' => fn($query) => $query->withPivot('status_id', 'progress'), 'users', 'directorate'];
        $taskQuery = Task::with($withRelations)->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();
            Log::info('Task filtering for user', ['user_id' => $user->id, 'role_ids' => $roleIds]);

            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                $taskQuery->where(function ($query) use ($user, $roleIds) {
                    if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                        $query->where('directorate_id', $user->directorate_id);
                        Log::info('Filtering tasks by directorate', ['user_id' => $user->id, 'directorate_id' => $user->directorate_id]);
                    } elseif (in_array(Role::PROJECT_USER, $roleIds)) {
                        $projectIds = $user->projects()->whereNull('deleted_at')->pluck('id');
                        $query->whereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds)->whereNull('deleted_at'));
                        Log::info('Filtering tasks by project', ['user_id' => $user->id, 'project_ids' => $projectIds->toArray()]);
                    } else {
                        $query->whereRaw('1 = 0');
                        Log::warning('No valid role for task access', ['user_id' => $user->id, 'role_ids' => $roleIds]);
                    }
                });
            }

            $tasks = $taskQuery->get();
            Log::debug('Tasks fetched from query', ['count' => $tasks->count()]);

            // Common project-task mapping for all views
            $projectTasks = $tasks->flatMap(function ($task) use ($statusColors, $priorityColors) {
                return $task->projects->map(function ($project) use ($task, $statusColors, $priorityColors) {
                    $status = $project->pivot->status_id ? Status::find($project->pivot->status_id) : ($task->status_id ? Status::find($task->status_id) : null);
                    return (object) [
                        'task' => $task,
                        'project' => $project,
                        'status_id' => $project->pivot->status_id ?? $task->status_id,
                        'status' => $status,
                        'progress' => $project->pivot->progress ?? $task->progress,
                        'project_id' => $project->id,
                    ];
                });
            });

            if ($activeView === 'board') {
                $data['tasks'] = $projectTasks->groupBy(fn($projectTask) => $projectTask->status_id ?? 'null')->map(function ($group) use ($statusColors, $priorityColors) {
                    return $group->map(function ($projectTask) use ($statusColors, $priorityColors) {
                        $task = $projectTask->task;
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'description' => $task->description,
                            'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : null,
                            'status' => $projectTask->status ? ['id' => $projectTask->status->id, 'title' => $projectTask->status->title] : null,
                            'status_id' => $projectTask->status_id,
                            'status_color' => $projectTask->status ? ($statusColors[$projectTask->status->id] ?? 'gray') : 'gray',
                            'view_url' => route('admin.task.show', [$task->id, $projectTask->project_id]),
                            'priority_id' => $task->priority_id,
                            'project_id' => $projectTask->project_id,
                            'project_name' => $projectTask->project->title ?? 'N/A',
                            'progress' => $projectTask->progress ?? 'N/A',
                        ];
                    })->values();
                });
                $data['taskCounts'] = $data['tasks']->map->count()->toArray();
                Log::info('Tasks fetched for board view', [
                    'user_id' => $user->id,
                    'count' => $projectTasks->count(),
                    'counts_by_status' => $data['taskCounts'],
                ]);
            } elseif ($activeView === 'list') {
                $data['tasksFlat'] = $projectTasks->map(function ($projectTask) use ($statusColors, $priorityColors) {
                    $task = $projectTask->task;
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $projectTask->status ? $projectTask->status->title : 'N/A',
                        'status_id' => $projectTask->status_id,
                        'progress' => $projectTask->progress ?? 'N/A',
                        'priority' => $task->priority ? $task->priority->title : 'N/A',
                        'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : 'N/A',
                        'projects' => [$projectTask->project->title ?? 'N/A'],
                        'users' => $task->users->pluck('name')->all(),
                        'directorate' => $task->directorate?->title ?? 'N/A',
                        'project_id' => $projectTask->project_id,
                        'view_url' => route('admin.task.show', [$task->id, $projectTask->project_id]),
                    ];
                })->values();
                Log::info('Tasks fetched for list view', ['user_id' => $user->id, 'count' => $data['tasksFlat']->count()]);
            } elseif ($activeView === 'calendar') {
                $data['calendarData'] = $projectTasks->map(function ($projectTask) use ($statusColors) {
                    $task = $projectTask->task;
                    $startDate = $task->start_date ? Carbon::parse($task->start_date) : ($task->due_date ? Carbon::parse($task->due_date) : null);
                    $endDate = $task->due_date ? Carbon::parse($task->due_date) : null;
                    return [
                        'id' => $task->id,
                        'title' => $task->title ?? 'Untitled Task',
                        'start' => $startDate ? $startDate->format('Y-m-d') : null,
                        'end' => $endDate ? $endDate->addDay()->format('Y-m-d') : null,
                        'color' => $projectTask->status ? ($statusColors[$projectTask->status->id] ?? 'gray') : 'gray',
                        'url' => route('admin.task.show', [$task->id, $projectTask->project_id]),
                        'extendedProps' => [
                            'status' => $projectTask->status ? $projectTask->status->title : 'N/A',
                            'progress' => $projectTask->progress ?? 'N/A',
                            'priority' => $task->priority ? $task->priority->title : 'N/A',
                            'project' => $projectTask->project->title ?? 'N/A',
                            'users' => $task->users->pluck('name')->all(),
                            'directorate' => $task->directorate?->title ?? 'N/A',
                            'project_id' => $projectTask->project_id,
                        ],
                    ];
                })->filter(fn($event) => $event['start'] !== null)->values()->all();
                Log::info('Tasks fetched for calendar view', ['user_id' => $user->id, 'count' => count($data['calendarData'])]);
            } elseif ($activeView === 'table') {
                $data['tableHeaders'] = [
                    trans('global.task.fields.id'),
                    trans('global.task.fields.title'),
                    trans('global.task.fields.status_id'),
                    trans('global.task.fields.progress'),
                    trans('global.task.fields.priority_id'),
                    trans('global.task.fields.due_date'),
                    trans('global.task.fields.project_id'),
                    trans('global.task.fields.user_id'),
                    trans('global.task.fields.directorate_id'),
                ];
                $data['tableData'] = $projectTasks->map(function ($projectTask) use ($statusColors) {
                    $task = $projectTask->task;
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $projectTask->status ? $projectTask->status->title : 'N/A',
                        'progress' => $projectTask->progress ?? 'N/A',
                        'priority' => $task->priority ? $task->priority->title : 'N/A',
                        'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : 'N/A',
                        'project' => $projectTask->project->title ?? 'N/A',
                        'users' => $task->users->pluck('name')->join(', '),
                        'directorate' => $task->directorate?->title ?? 'N/A',
                        'project_id' => $projectTask->project_id,
                    ];
                })->all();
                Log::info('Tasks fetched for table view', ['user_id' => $user->id, 'count' => count($data['tableData'])]);
            }
        } catch (\Exception $e) {
            Log::error('Error in task filtering', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $data['error'] = 'Unable to load tasks due to an error: ' . $e->getMessage();
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
        $validated = $request->validated();

        $taskData = array_diff_key($validated, array_flip(['status_id', 'progress', 'projects', 'users']));
        $task = Task::create($validated);

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

        $task->users()->sync($validated['users'] ?? []);

        // Notify users (uncomment if needed)
        // foreach ($task->users as $user) {
        //     $user->notify(new TaskCreated($task));
        // }

        return redirect()->route('admin.task.index')
            ->with('message', 'Task created successfully.');
    }

    public function show(Task $task, Project $project): View
    {
        abort_if(Gate::denies('task_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $projectTask = $task->projects()->where('project_id', $project->id)->first();
        if (!$projectTask) {
            abort(404, 'Task not associated with this project');
        }

        $task->load([
            'priority',
            'users',
            'comments.user',
            'comments.replies.user',
            'projects' => fn($query) => $query->withPivot('status_id', 'progress'),
        ]);

        $status = $projectTask->status_id ? Status::find($projectTask->status_id) : ($task->status_id ? Status::find($task->status_id) : null);
        $statusColors = Status::all()->pluck('color', 'id')->toArray();
        $priorityColors = config('panel.priority_colors', []);

        $taskData = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : null,
            'status' => $status ? ['id' => $status->id, 'title' => $status->title, 'color' => $statusColors[$status->id] ?? 'gray'] : null,
            'status_id' => $projectTask->status_id ?? $task->status_id,
            'progress' => $projectTask->progress,
            'project_id' => $project->id,
            'project_name' => $project->title,
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
            'start_date' => $task->start_date,
            'due_date' => $task->due_date,
            'completion_date' => $task->completion_date,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
        ];

        $comments = Comment::forTaskProject($task->id, $project->id)
            ->with(['user', 'replies' => fn($query) => $query->orderBy('created_at', 'asc')])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Comments loaded for task show', [
            'task_id' => $task->id,
            'project_id' => $project->id,
            'comment_count' => $comments->count(),
            'comments' => $comments->pluck('id')->toArray(),
        ]);

        $user = Auth::user();
        $commentIds = $user->comments()
            ->where('commentable_type', Task::class)
            ->where('commentable_id', $task->id)
            ->where('project_id', $project->id)
            ->whereNull('comment_user.read_at')
            ->pluck('comments.id');

        foreach ($commentIds as $commentId) {
            $user->comments()->updateExistingPivot($commentId, ['read_at' => now()]);
        }

        return view('admin.tasks.show', [
            'task' => $taskData,
            'comments' => $comments,
            'statusColors' => $statusColors,
            'priorityColors' => $priorityColors,
            'statuses' => Status::all(),
        ]);
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

        try {
            $validated = $request->validated();

            $task->update($validated);

            // Sync projects with pivot data
            if (!empty($validated['projects'])) {
                $pivotData = collect($validated['projects'])->mapWithKeys(function ($projectId) use ($task) {
                    // Preserve existing pivot status_id and progress if project already associated
                    $existingPivot = $task->projects()->where('projects.id', $projectId)->first();
                    return [$projectId => [
                        'status_id' => $existingPivot ? $existingPivot->pivot->status_id : null,
                        'progress' => $existingPivot ? $existingPivot->pivot->progress : null,
                    ]];
                })->toArray();
                $task->projects()->sync($pivotData);
            } else {
                $task->projects()->sync([]);
            }

            $task->users()->sync($validated['users'] ?? []);

            // Optionally notify users (uncomment if needed)
            // foreach ($task->users as $user) {
            //     $user->notify(new TaskUpdated($task));
            // }

            return redirect()->route('admin.task.show', $task)
                ->with('message', 'Task updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating task', [
                'error' => $e->getMessage(),
                'data' => $validated,
                'task_id' => $task->id,
            ]);
            return back()->with('error', 'Failed to update task: ' . $e->getMessage());
        }
    }

    public function destroy(Task $task): RedirectResponse
    {
        abort_if(Gate::denies('task_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            // Optionally notify users (uncomment if needed)
            // foreach ($task->users as $user) {
            //     $user->notify(new TaskDeleted($task));
            // }

            $task->delete();

            return redirect()->route('admin.task.index')
                ->with('message', 'Task deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
            ]);
            return back()->with('error', 'Failed to delete task: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $task = Task::findOrFail($request->input('task_id'));
            $projectId = $request->input('project_id');
            $statusId = $request->input('status_id');

            if (!$projectId || !Project::where('id', $projectId)->exists()) {
                return response()->json(['message' => 'Invalid project ID'], 400);
            }

            // Update pivot table for project-specific status
            $task->projects()->updateExistingPivot($projectId, [
                'status_id' => $statusId,
                'progress' => $request->input('progress'), // Optional: include progress if sent
            ]);

            // Optionally notify users (uncomment if needed)
            // foreach ($task->users as $user) {
            //     $user->notify(new TaskUpdated($task));
            // }

            return response()->json(['message' => 'Task status updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating task status', [
                'error' => $e->getMessage(),
                'task_id' => $request->input('task_id'),
                'project_id' => $projectId,
                'status_id' => $statusId,
            ]);
            return response()->json(['message' => 'Failed to update task status: ' . $e->getMessage()], 500);
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
                // No additional filtering needed for SUPERADMIN
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

    public function getGanttChart(Request $request)
    {
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

        $tasks = $rawTasks->flatMap(function ($task) use ($request) {
            return $task->projects->map(function ($project) use ($task, $request) {
                $status = $project->pivot->status_id ? Status::find($project->pivot->status_id) : ($task->status_id ? Status::find($task->status_id) : null);
                return [
                    'id' => $task->id . '-' . $project->id, // Unique ID for task-project pair
                    'title' => $task->title . ' (' . ($project->title ?? 'N/A') . ')',
                    'start' => $task->start_date ? Carbon::parse($task->start_date)->format('Y-m-d') : null,
                    'end' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null,
                    'progress' => $project->pivot->progress ?? $task->progress ?? 0,
                    'directorate' => $project->directorate?->title ?? 'N/A',
                    'directorate_id' => $project->directorate?->id ?? null,
                    'priority' => $task->priority_id ?? null,
                    'priority_title' => $task->priority?->title ?? 'N/A',
                    'project_id' => $project->id,
                    'resourceId' => $task->id % 3 + 1,
                ];
            });
        })->filter(function ($task) use ($request) {
            $include = !$request->filled('directorate_id') || $task['directorate_id'] == $request->directorate_id;
            return $include && $task['start'] && $task['end'];
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

    public function loadMore(Request $request): JsonResponse
    {
        try {
            $statusId = $request->input('status_id');
            $offset = $request->input('offset', 0);
            $limit = 10;

            if (!$statusId || !Status::where('id', $statusId)->exists()) {
                Log::warning('Invalid status ID provided for load more', [
                    'user_id' => Auth::id(),
                    'status_id' => $statusId,
                ]);
                return response()->json(['message' => 'Invalid status ID'], 400);
            }

            $user = Auth::user();
            $roleIds = $user->roles->pluck('id')->toArray();
            $withRelations = ['priority', 'projects' => fn($query) => $query->withPivot('status_id', 'progress'), 'users', 'directorate'];

            // Query tasks with project-specific status from project_task pivot
            $taskQuery = Task::with($withRelations)
                ->whereHas('projects', function ($query) use ($statusId) {
                    $query->where('project_task.status_id', $statusId);
                })
                ->latest();

            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                $taskQuery->where(function ($query) use ($user, $roleIds) {
                    if (in_array(Role::DIRECTORATE_USER, $roleIds) && $user->directorate_id) {
                        $query->where('directorate_id', $user->directorate_id);
                        Log::info('Filtering load more tasks by directorate', [
                            'user_id' => $user->id,
                            'directorate_id' => $user->directorate_id,
                        ]);
                    } elseif (in_array(Role::PROJECT_USER, $roleIds)) {
                        $projectIds = $user->projects()->whereNull('deleted_at')->pluck('id');
                        $query->whereHas('projects', fn($q) => $q->whereIn('projects.id', $projectIds)->whereNull('deleted_at'));
                        Log::info('Filtering load more tasks by project', [
                            'user_id' => $user->id,
                            'project_ids' => $projectIds->toArray(),
                        ]);
                    } else {
                        $query->whereRaw('1 = 0');
                        Log::warning('No valid role for load more tasks', [
                            'user_id' => $user->id,
                            'role_ids' => $roleIds,
                        ]);
                    }
                });
            }

            $tasks = $taskQuery->skip($offset)->take($limit)->get();
            $statusColors = Cache::remember('task_statuses', 86400, fn() => Status::all())->pluck('color', 'id')->toArray();
            $priorityColors = [
                'Urgent' => '#EF4444',
                'High' => '#F59E0B',
                'Medium' => '#10B981',
                'Low' => '#6B7280',
            ];

            return response()->json([
                'tasks' => $tasks->flatMap(function ($task) use ($statusColors, $priorityColors) {
                    return $task->projects->map(function ($project) use ($task, $statusColors, $priorityColors) {
                        $status = $project->pivot->status_id ? Status::find($project->pivot->status_id) : ($task->status_id ? Status::find($task->status_id) : null);
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'description' => $task->description,
                            'priority' => $task->priority ? ['title' => $task->priority->title, 'color' => $priorityColors[$task->priority->title] ?? 'gray'] : null,
                            'status' => $status ? ['id' => $status->id, 'title' => $status->title] : null,
                            'status_id' => $project->pivot->status_id ?? $task->status_id,
                            'status_color' => $status ? ($statusColors[$status->id] ?? 'gray') : 'gray',
                            'view_url' => route('admin.task.show', $task->id),
                            'priority_id' => $task->priority_id,
                            'project_id' => $project->id,
                            'project_name' => $project->title ?? 'N/A',
                            'progress' => $project->pivot->progress ?? $task->progress ?? 'N/A',
                        ];
                    });
                })->values()->all(),
                'has_more' => $tasks->count() === $limit,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading more tasks', [
                'error' => $e->getMessage(),
                'status_id' => $statusId,
                'offset' => $offset,
                'user_id' => Auth::id(),
            ]);
            return response()->json(['message' => 'Failed to load more tasks: ' . $e->getMessage()], 500);
        }
    }
}
