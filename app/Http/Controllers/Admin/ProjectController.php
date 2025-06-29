<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Department;
use App\Models\Directorate;
use App\Models\FiscalYear;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('project_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $projectQuery = Project::with(['directorate', 'priority', 'projectManager', 'status', 'budgets'])->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();

            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                if (in_array(Role::DIRECTORATE_USER, $roleIds)) {
                    // Filter projects by user's directorate_id from users table
                    if ($user->directorate_id) {
                        $projectQuery->where('directorate_id', $user->directorate_id);
                    } else {
                        Log::warning('No directorate_id assigned to user', ['user_id' => $user->id]);
                        $projectQuery->where('id', 0); // Return empty result if no directorate_id
                    }
                } else {
                    // Filter projects where user is explicitly assigned
                    $projectQuery->whereHas('users', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in project filtering', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $data['error'] = 'Unable to load projects due to an error.';
        }

        $projects = $projectQuery->get();

        $directorateColors = [
            1 => 'red',
            2 => 'green',
            3 => 'blue',
            4 => 'yellow',
            5 => 'purple',
            6 => 'pink',
            7 => 'gray',
            8 => 'teal',
            9 => 'orange',
        ];

        $priorityColors = [
            'Urgent' => '#EF4444',
            'High' => '#F59E0B',
            'Medium' => '#10B981',
            'Low' => '#6B7280',
        ];

        $progressColor = 'green';
        $budgetColor = 'blue';

        $tableData = $projects->map(function ($project) use ($directorateColors, $priorityColors, $progressColor, $budgetColor) {
            $directorateTitle = $project->directorate?->title ?? 'N/A';
            $directorateId = $project->directorate?->id ?? null;
            $directorateDisplayColor = isset($directorateColors[$directorateId]) ? $directorateColors[$directorateId] : 'gray';

            $priorityValue = $project->priority?->title ?? 'N/A';
            $priorityDisplayColor = isset($priorityColors[$priorityValue]) ? $priorityColors[$priorityValue] : '#6B7280';

            $fieldsForTable = [];
            $fieldsForTable[] = ['title' => trans('global.project.fields.start_date') . ': ' . ($project->start_date?->format('Y-m-d') ?? 'N/A'), 'color' => 'gray'];
            $fieldsForTable[] = ['title' => trans('global.project.fields.end_date') . ': ' . ($project->end_date?->format('Y-m-d') ?? 'N/A'), 'color' => 'gray'];
            $fieldsForTable[] = ['title' => trans('global.project.fields.budget') . ': ' . (is_numeric($project->budget) ? number_format((float) $project->budget, 2) : 'N/A'), 'color' => $budgetColor];
            $fieldsForTable[] = ['title' => trans('global.project.fields.priority_id') . ': ' . $priorityValue, 'color' => $priorityDisplayColor];
            $fieldsForTable[] = ['title' => trans('global.project.fields.progress') . ': ' . (is_numeric($project->progress) ? $project->progress . '%' : 'N/A'), 'color' => $progressColor];
            $fieldsForTable[] = ['title' => trans('global.project.fields.project_manager') . ': ' . ($project->projectManager->name ?? 'N/A'), 'color' => 'gray'];

            return [
                'id' => $project->id,
                'title' => $project->title,
                'directorate' => [['title' => $directorateTitle, 'color' => $directorateDisplayColor]],
                'fields' => $fieldsForTable,
            ];
        })->all();

        $cardData = $projects->map(function ($project) use ($priorityColors) {
            $directorateTitle = $project->directorate?->title ?? 'N/A';
            $directorateId = $project->directorate?->id ?? null;

            $priorityValue = $project->priority?->title ?? 'N/A';
            $priorityColor = isset($priorityColors[$priorityValue]) ? $priorityColors[$priorityValue] : '#6B7280';

            $fields = [
                ['label' => trans('global.project.fields.start_date'), 'key' => 'start_date', 'value' => $project->start_date?->format('Y-m-d') ?? 'N/A'],
                ['label' => trans('global.project.fields.end_date'), 'key' => 'end_date', 'value' => $project->end_date?->format('Y-m-d') ?? 'N/A'],
                ['label' => trans('global.project.fields.budget'), 'key' => 'budget', 'value' => is_numeric($project->total_budget) ? number_format((float) $project->total_budget, 2) : 'N/A'],
                ['label' => trans('global.project.fields.priority_id'), 'key' => 'priority', 'value' => $priorityValue, 'color' => $priorityColor],
                ['label' => trans('global.project.fields.progress'), 'key' => 'progress', 'value' => is_numeric($project->progress) ? $project->progress . '%' : 'N/A'],
                ['label' => trans('global.project.fields.project_manager'), 'key' => 'project_manager', 'value' => $project->projectManager->name ?? 'N/A'],
            ];

            return [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description ?? 'No description available',
                'directorate' => ['title' => $directorateTitle, 'id' => $directorateId],
                'fields' => $fields,
            ];
        })->all();

        $tableHeaders = [
            trans('global.project.fields.id'),
            trans('global.project.fields.title'),
            trans('global.project.fields.directorate_id'),
        ];

        return view('admin.projects.index', [
            'data' => $cardData,
            'tableData' => $tableData,
            'projects' => $projects,
            'routePrefix' => 'admin.project',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this project?',
            'arrayColumnColor' => [
                'title' => '#9333EA',
                'progress' => 'green',
                'budget' => 'blue',
                'directorate' => $directorateColors,
                'priority' => $priorityColors,
            ],
            'tableHeaders' => $tableHeaders,
        ]);
    }

    public function create(): View
    {
        abort_if(Gate::denies('project_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();

        $roleIds = $user->roles->pluck('id')->toArray();

        $directorates = collect();
        $departments = collect();
        $users = collect();
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');
        $fiscalYears = FiscalYear::pluck('title', 'id');

        if (in_array(Role::SUPERADMIN, $roleIds)) {
            $directorates = Directorate::pluck('title', 'id');
        } elseif (in_array(Role::DIRECTORATE_USER, $roleIds)) {
            $fixedDirectorateId = $user->directorate_id;
            $directorates = Directorate::where('id', $fixedDirectorateId)->pluck('title', 'id');
        }

        return view('admin.projects.create', compact('directorates', 'departments', 'statuses', 'priorities', 'fiscalYears', 'users'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        try {
            /** @var \Illuminate\Http\Request $request */
            $data = $request->validated();

            $project = Project::create(\Illuminate\Support\Arr::except($data, ['budgets']));

            // foreach ($data['budgets'] as $budget) {
            //     $project->budgets()->create([
            //         'fiscal_year_id' => $budget['fiscal_year_id'],
            //         'total_budget' => $budget['total_budget'],
            //         'internal_budget' => $budget['internal_budget'],
            //         'foreign_loan_budget' => $budget['foreign_loan_budget'],
            //         'foreign_subsidy_budget' => $budget['foreign_subsidy_budget'],
            //     ]);
            // }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('files', 'public');
                    $project->files()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'file_type' => $file->extension(),
                        'file_size' => $file->getSize(),
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            if (Session::has('temp_files')) {
                foreach (Session::get('temp_files', []) as $tempPath) {
                    Storage::disk('public')->delete($tempPath);
                }
                Session::forget('temp_files');
            }

            return redirect()->route('admin.project.index')->with('message', 'Project created successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create project', [
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create project. Please try again.']);
        }
    }

    public function show(Project $project): View
    {
        $project->load([
            'directorate',
            'department',
            'status',
            'priority',
            'projectManager',
            'budgets',
            'comments.user',
            'comments.replies.user',
        ]);

        // Mark comments as read for the current user
        $user = Auth::user();
        $commentIds = $user->comments()
            ->where('commentable_type', 'App\Models\Project')
            ->where('commentable_id', $project->id)
            ->whereNull('comment_user.read_at')
            ->pluck('comments.id');

        foreach ($commentIds as $commentId) {
            $user->comments()->updateExistingPivot($commentId, ['read_at' => now()]);
        }

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        $directorates = collect();
        $departments = collect();
        $users = collect();
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');
        $fiscalYears = FiscalYear::pluck('title', 'id');

        // Load project relationships
        $project->load(['budgets', 'files']);

        if (in_array(Role::SUPERADMIN, $roleIds)) {
            $directorates = Directorate::pluck('title', 'id');
        } elseif (in_array(Role::DIRECTORATE_USER, $roleIds)) {
            $fixedDirectorateId = $user->directorate_id;
            $directorates = Directorate::where('id', $fixedDirectorateId)->pluck('title', 'id');
        }

        if ($project->directorate_id) {
            $departments = Department::whereHas('directorates', function ($query) use ($project) {
                $query->where('directorate_id', $project->directorate_id);
            })->pluck('title', 'id');
            $users = User::where('directorate_id', $project->directorate_id)->pluck('name', 'id');
        }

        return view('admin.projects.edit', compact(
            'project',
            'directorates',
            'departments',
            'users',
            'statuses',
            'priorities',
            'fiscalYears'
        ));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        try {
            /** @var \Illuminate\Http\Request $request */
            $data = $request->validated();

            $project->update(\Illuminate\Support\Arr::except($data, ['budgets', 'files']));

            $project->budgets()->delete();

            foreach ($data['budgets'] as $budget) {
                $project->budgets()->create([
                    'fiscal_year_id' => $budget['fiscal_year_id'],
                    'total_budget' => $budget['total_budget'],
                    'internal_budget' => $budget['internal_budget'],
                    'foreign_loan_budget' => $budget['foreign_loan_budget'],
                    'foreign_subsidy_budget' => $budget['foreign_subsidy_budget'],
                ]);
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('files', 'public');
                    $project->files()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'file_type' => $file->extension(),
                        'file_size' => $file->getSize(),
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            return redirect()->route('admin.project.index')->with('message', 'Project updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update project', [
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to update project. Please try again.']);
        }
    }

    public function destroy(Project $project)
    {
        //
    }

    public function getDepartments($directorate_id): JsonResponse
    {
        try {
            $directorate = Directorate::find($directorate_id);
            if (! $directorate) {
                Log::info('Directorate not found, returning empty array: ' . $directorate_id);

                return response()->json([]);
            }
            $departments = $directorate->departments->map(function ($department) {
                return [
                    'value' => (string) $department->id,
                    'label' => $department->title,
                ];
            })->toArray();

            Log::info('Departments fetched for directorate_id: ' . $directorate_id, ['count' => count($departments)]);

            return response()->json($departments);
        } catch (\Exception $e) {
            Log::error('Failed to fetch departments: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to fetch departments.'], 500);
        }
    }

    public function getUsers($directorate_id): JsonResponse
    {
        try {
            $users = User::where('directorate_id', $directorate_id)
                ->select('id', 'name')
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => (string) $user->id,
                        'label' => $user->name,
                    ];
                })->toArray();

            Log::info('Users fetched for directorate_id: ' . $directorate_id, ['count' => count($users)]);

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to fetch users.'], 500);
        }
    }

    public function progressChart(Project $project)
    {
        $project->load(['tasks', 'contracts', 'expenses']);
        return view('admin.expenses.progress_chart', compact('project'));
    }

    public function analytics(Request $request)
    {
        try {
            // Fetch filter options
            $directorates = Directorate::all();
            $departments = Department::all();
            $statuses = Status::all();
            $priorities = Priority::all();

            // Base query for projects with role-based filtering and eager loading
            $query = Project::query()->with(['directorate', 'status', 'priority', 'tasks', 'contracts', 'expenses', 'budgets']);

            // Apply role-based filtering
            $user = Auth::user();
            if ($user && $user->roles->contains('id', 3)) {
                $query->where('directorate_id', $user->directorate_id);
            } elseif ($user && $user->roles->contains('id', 4)) {
                $query->whereIn('id', function ($query) use ($user) {
                    $query->select('project_id')
                        ->from('project_user')
                        ->where('user_id', $user->id);
                });
            }

            // Apply filters from request
            if ($request->has('directorate_id')) {
                $query->where('directorate_id', $request->directorate_id);
            }
            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            }
            if ($request->has('priority_id')) {
                $query->where('priority_id', $request->priority_id);
            }

            // Fetch projects with pagination
            $projects = $query->paginate(10);

            // Pre-calculate additional attributes
            $projects->getCollection()->transform(function ($project) {
                $totalBudget = $project->total_budget;
                $project->remaining_budget = $totalBudget - ($project->expenses->sum('amount') + $project->contracts->sum('contract_amount'));
                $project->days_remaining = $project->end_date ? max(0, $project->end_date->diffInDays(now()) * ($project->end_date > now() ? 1 : -1)) : 0;
                return $project;
            });

            // Prepare summary data
            $completedStatusId = Status::where('title', 'Completed')->value('id');
            $summary = [
                'total_projects' => $query->count(),
                'completed_projects' => $query->where('status_id', $completedStatusId)->count(),
                'overdue_projects' => $query->where('end_date', '<', now())->where('status_id', '!=', $completedStatusId)->count(),
                'average_progress' => round($query->avg('progress') ?? 0, 2),
            ];

            // Prepare chart data
            $chartProjects = $query->get();
            $charts = [
                'progress' => [
                    'labels' => $chartProjects->pluck('title')->take(5),
                    'physical' => $chartProjects->take(5)->map->progress,
                    'financial' => $chartProjects->take(5)->map->financial_progress,
                ],
                'task_contract' => [
                    'labels' => ['Tasks', 'Contracts'],
                    'data' => [
                        $chartProjects->sum(fn($project) => $project->tasks->count()),
                        $chartProjects->sum(fn($project) => $project->contracts->count()),
                    ],
                ],
            ];

            // Return JSON response for AJAX requests
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

            // Return view for initial load
            return view('admin.analytics.project-analytics', compact('projects', 'summary', 'charts', 'directorates', 'departments', 'statuses', 'priorities'));
        } catch (\Exception $e) {
            Log::error('Error in ProjectController@analytics: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'An error occurred. Please try again.'], 500);
            }
            return redirect()->back()->withErrors(['error' => 'An error occurred. Please check the logs.']);
        }
    }
}
