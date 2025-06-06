<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Models\Task;
use App\Models\Status;
use App\Models\Priority;
use App\Models\Directorate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('task_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activeView = session('task_view_preference', 'board');

        // Cache statuses and priorities (for 1 day)
        $statuses = Cache::remember('task_statuses', 86400, function () {
            return Status::all();
        });

        $priorities = Cache::remember('task_priorities', 86400, function () {
            return Priority::all();
        });

        // Status colors from DB: id => hex color
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
            'projectsForFilter' => Project::all(),
            'statusColors' => $statusColors,
            'priorityColors' => $priorityColors,
            'routePrefix' => 'admin.task',
            'deleteConfirmationMessage' => 'Are you sure you want to delete this task?',
            'actions' => ['view', 'edit', 'delete'],
        ];

        // Common eager loads
        $withRelations = ['status', 'priority', 'projects', 'users'];

        if ($activeView === 'board') {
            $tasks = Task::with($withRelations)->latest()->get();
            $data['tasks'] = $tasks->groupBy('status_id')->map(function ($group) {
                return $group->values();
            });
        }

        if ($activeView === 'list') {
            $data['tasksFlat'] = Task::with($withRelations)->latest()->get()->values();
        }

        if ($activeView === 'calendar') {
            $tasks = Task::with($withRelations)->latest()->get();
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
                        'projects' => $task->projects->pluck('name')->all(),
                        'users' => $task->users->pluck('name')->all(),
                    ],
                ];
            })->filter(fn($event) => $event['start'] !== null)->values()->all();
        }

        if ($activeView === 'table') {
            $tasks = Task::with($withRelations)->latest()->get();
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
                    'status' => $task->status->title,
                    'priority' => $task->priority->title,
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                    'projects' => $task->projects->pluck('name')->join(', '),
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

        $task->projects()->sync($validated['projects']);
        $task->users()->sync($validated['users']);

        return redirect()->route(route: 'admin.task.index')
            ->with('message', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }

    public function filter(Request $request): JsonResponse
    {
        $query = Task::with(['status', 'priority', 'project']);

        if ($request->has('filter_status')) {
            $query->whereIn('status_id', $request->input('filter_status'));
        }
        if ($request->has('filter_priority')) {
            $query->whereIn('priority_id', $request->input('filter_priority'));
        }
        if ($request->has('filter_project')) {
            $query->where('project_id', $request->input('filter_project'));
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

        return response()->json(['message' => 'Task status updated successfully']);
    }

    // TaskController.php
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
            if (!is_array($projectIds)) {
                $projectIds = [$projectIds];
            }
            $projectIds = array_filter($projectIds); // Remove empty values

            if (empty($projectIds)) {
                Log::info('No project IDs provided, returning empty array');
                return response()->json([]);
            }

            // Assuming a many-to-many relationship between User and Project (e.g., project_user pivot table)
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
}
