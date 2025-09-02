<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Directorate;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $userQuery = User::with(['roles', 'directorate'])->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();

            if (!in_array(1, $roleIds)) { // Not a super admin
                if (in_array(3, $roleIds)) { // Directorate user
                    $directorateId = $user->directorate ? [$user->directorate->id] : [];
                    if (empty($directorateId)) {
                        Log::warning('No directorate assigned to user', ['user_id' => $user->id]);
                    }
                    $userQuery->whereHas('directorate', function ($query) use ($directorateId) {
                        $query->whereIn('directorate_id', $directorateId);
                    });
                } elseif (in_array(4, $roleIds)) { // Project user
                    $projectIds = $user->projects()->pluck('projects.id')->toArray();
                    if (empty($projectIds)) {
                        Log::warning('No projects assigned to user', ['user_id' => $user->id]);
                    }
                    $userQuery->whereHas('projects', function ($query) use ($projectIds) {
                        $query->whereIn('projects.id', $projectIds);
                    });
                } else { // Fallback for other roles
                    $userQuery->where('id', $user->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in user filtering', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $data['error'] = 'Unable to load users due to an error.';
        }

        $users = $userQuery->get();

        $headers = [
            trans('global.user.fields.id'),
            trans('global.user.fields.name'),
            trans('global.user.fields.email'),
            trans('global.user.fields.roles'),
            trans('global.user.fields.directorate_id'),
        ];

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('title')->toArray() ?? [],
                'directorate_id' => $user->directorate ? $user->directorate->title : 'N/A',
            ];
        })->all();

        return view('admin.users.index', [
            'headers' => $headers,
            'data' => $data,
            'users' => $users,
            'routePrefix' => 'admin.user',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this user?',
            'arrayColumnColor' => 'purple',
        ]);
    }

    public function create(): View
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        $isDirectorateOrProjectUser = in_array(3, $roleIds) || in_array(4, $roleIds); // Directorate (3) or Project (4) user
        $directorateId = $user->directorate_id;

        $roles = $isDirectorateOrProjectUser ? collect([]) : Role::pluck('title', 'id'); // Empty if directorate/project user
        $directorates = $isDirectorateOrProjectUser ? collect([]) : Directorate::pluck('title', 'id'); // Empty if directorate/project user
        $projects = collect([]);

        if ($isDirectorateOrProjectUser) {
            if (in_array(3, $roleIds)) {
                // Directorate user: Load all projects under their directorate
                $projects = Project::where('directorate_id', $directorateId)->pluck('title', 'id');
            } elseif (in_array(4, $roleIds)) {
                // Project user: Load only their assigned projects
                $projects = $user->projects()->pluck('title', 'id');
            }
        } else {
            // Admin or other roles: Load all projects (or none, depending on your logic)
            $projects = Project::pluck('title', 'id');
        }

        return view('admin.users.create', compact('roles', 'directorates', 'projects', 'isDirectorateOrProjectUser', 'directorateId'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();
        $isDirectorateOrProjectUser = in_array(3, $roleIds) || in_array(4, $roleIds);

        $validated = $request->validated();

        // dd($validated);

        if ($isDirectorateOrProjectUser) {
            $validated['roles'] = [4];
            $validated['directorate_id'] = $user->directorate_id;
        }

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $newUser = User::create(\Illuminate\Support\Arr::except($validated, ['projects', 'roles']));

        $newUser->roles()->sync($validated['roles'] ?? []);

        $newUser->projects()->sync($validated['projects'] ?? []);

        return redirect()->route('admin.user.index')
            ->with('message', 'User created successfully.');
    }

    public function edit($id): View
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $authUser = Auth::user();
        $roleIds = $authUser->roles->pluck('id')->toArray();
        $isDirectorateOrProjectUser = in_array(3, $roleIds) || in_array(4, $roleIds);

        // Load the user to edit with relationships
        $user = User::with(['roles', 'directorate', 'projects'])->findOrFail($id);

        // Restrict access for directorate/project users
        if ($isDirectorateOrProjectUser) {
            if (in_array(3, $roleIds)) {
                // Directorate user: Ensure the user to edit belongs to the same directorate
                if ($user->directorate_id !== $authUser->directorate_id) {
                    abort(Response::HTTP_FORBIDDEN, 'You can only edit users in your directorate.');
                }
            } elseif (in_array(4, $roleIds)) {
                // Project user: Ensure the user to edit shares at least one project
                $authUserProjectIds = $authUser->projects()->pluck('projects.id')->toArray();
                $userProjectIds = $user->projects()->pluck('projects.id')->toArray();
                if (empty(array_intersect($authUserProjectIds, $userProjectIds))) {
                    abort(Response::HTTP_FORBIDDEN, 'You can only edit users in your projects.');
                }
            }
        }

        // Set roles and directorates (empty for directorate/project users)
        $roles = $isDirectorateOrProjectUser ? collect([]) : Role::pluck('title', 'id');
        $directorates = $isDirectorateOrProjectUser ? collect([]) : Directorate::pluck('title', 'id');

        // Set projects based on user role
        $projects = collect([]);
        if ($isDirectorateOrProjectUser) {
            if (in_array(3, $roleIds)) {
                // Directorate user: Load all projects under their directorate
                $projects = Project::where('directorate_id', $authUser->directorate_id)->pluck('title', 'id');
            } elseif (in_array(4, $roleIds)) {
                // Project user: Load only their assigned projects
                $projects = $authUser->projects()->pluck('title', 'id');
            }
        } else {
            // Admin or other roles: Load all projects to ensure edited user's projects are included
            $projects = Project::pluck('title', 'id');
        }

        // Log data for debugging
        \Illuminate\Support\Facades\Log::info('Edit user data', [
            'user_id' => $user->id,
            'auth_user_roles' => $roleIds,
            'is_directorate_or_project_user' => $isDirectorateOrProjectUser,
            'projects' => $projects->toArray(),
            'selected_projects' => $user->projects->pluck('id')->toArray(),
        ]);

        return view('admin.users.edit', compact('user', 'roles', 'directorates', 'projects', 'isDirectorateOrProjectUser'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $authUser = Auth::user();
        $roleIds = $authUser->roles->pluck('id')->toArray();
        $isDirectorateOrProjectUser = in_array(3, $roleIds) || in_array(4, $roleIds);

        $validated = $request->validated();

        // Set default role and directorate for directorate/project users
        if ($isDirectorateOrProjectUser) {
            $validated['roles'] = [4]; // Default role: Project User (ID 4)
            $validated['directorate_id'] = $authUser->directorate_id; // Use authenticated user's directorate
        }

        // Handle password: Only update if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']); // Remove password from update if not provided
        }

        // Ensure directorate_id is not null for non-directorate/project users
        if (!$isDirectorateOrProjectUser && !isset($validated['directorate_id'])) {
            $validated['directorate_id'] = $user->directorate_id; // Retain existing directorate_id
        }

        $user->update($validated);

        // Sync roles (if provided, otherwise empty array to detach)
        $user->roles()->sync($validated['roles'] ?? []);

        // Sync projects (if provided, otherwise empty array to detach)
        $user->projects()->sync($validated['projects'] ?? []);

        return redirect()->route('admin.user.index')
            ->with('message', 'User updated successfully.');
    }

    public function show(User $user): View
    {
        // abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load(['roles', 'directorate', 'projects']);

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return back()->with('message', 'User deleted successfully.');
    }

    public function getProjects(Request $request, $directorateId)
    {
        try {
            $projects = Project::where('directorate_id', $directorateId)
                ->pluck('title', 'id')
                ->map(fn($label, $value) => [
                    'value' => (string) $value,
                    'label' => $label,
                ])
                ->values()
                ->all();

            Log::info('Projects fetched for directorate_id: ' . $directorateId, [
                'count' => count($projects),
                'projects' => $projects,
            ]);

            return response()->json($projects);
        } catch (\Exception $e) {
            Log::error('Failed to fetch projects for directorate_id: ' . $directorateId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to fetch projects: ' . $e->getMessage(),
            ], 500);
        }
    }
}
