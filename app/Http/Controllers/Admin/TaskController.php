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

        $withRelations = ['status', 'priority', 'projects', 'users'];
        $taskQuery = Task::with($withRelations)->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();
            Log::info('Task filtering for user', ['user_id' => $user->id, 'role_ids' => $roleIds]);

            if (! in_array(1, $roleIds)) { // Not Superadmin
                $taskQuery->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                });

                if (in_array(3, $roleIds)) { // Directorate User
                    $directorateIds = $user->directorates ? $user->directorates->pluck('id') : collect();
                    if ($directorateIds->isEmpty()) {
                        Log::warning('No directorates assigned to user', ['user_id' => $user->id]);
                    }
                    $taskQuery->whereHas('projects', function ($query) use ($directorateIds) {
                        $query->whereIn('directorate_id', $directorateIds);
                    });
                }
            }
            // Superadmin (role_id = 1) sees all tasks
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
                ];
            })->all();
        }

        return view('admin.tasks.index', $data);
    }

    public function create(): View
    {
        abort_if(Gate::denies('task_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $directorates = Directorate::pluck('title', 'id');
        $projects = collect();
        $users = collect();
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');

        return view('admin.tasks.create', compact('statuses', 'priorities', 'directorates', 'projects', 'users'));
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
        $task->load(['status', 'priority', 'projects', 'users']);

        return view('admin.tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        abort_if(Gate::denies('task_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $task->load(['status', 'priority', 'projects', 'users']);
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');
        $directorates = Directorate::pluck('title', 'id');
        $projects = Project::pluck('title', 'id');
        $users = User::pluck('name', 'id');

        return view('admin.tasks.edit', compact('task', 'statuses', 'priorities', 'directorates', 'projects', 'users'));
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
        $query = Task::with(['projects.directorate', 'priority']);

        if ($request->filled('directorate_id')) {
            $query->whereHas('projects', function ($q) use ($request) {
                $q->where('directorate_id', $request->directorate_id);
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority_id', $request->priority);
        }

        $tasks = $query->get()->map(function ($task) {
            $directorateTitle = $task->projects->first()?->directorate?->title ?? 'N/A';
            $directorateId = $task->projects->first()?->directorate?->id ?? null;

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date->format('Y-m-d'),
                'end' => $task->due_date->format('Y-m-d'),
                'progress' => $task->progress ?? 0,
                'directorate' => $directorateTitle,
                'directorate_id' => $directorateId,
                'priority' => $task->priority_id ?? null,
                'priority_title' => $task->priority->title ?? 'N/A',
                'resourceId' => $task->id % 3 + 1,
            ];
        })->all();

        $availableDirectorates = Directorate::all()->pluck('title', 'id')->toArray();
        $priorities = Priority::all()->pluck('title', 'id')->toArray();

        if ($request->expectsJson()) {
            return response()->json([
                'tasks' => $tasks,
                'availableDirectorates' => $availableDirectorates,
                'priorities' => $priorities,
            ]);
        }

        return view('admin.analytics.tasks', compact('tasks', 'availableDirectorates', 'priorities'));
    }
}
