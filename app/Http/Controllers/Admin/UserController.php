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
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // protected function getRoleDirectorateAndProjects($user)
    // {
    //     $roleIds = $user->roles->pluck('id')->toArray();

    //     $roles = collect();
    //     $directorates = collect();
    //     $projects = collect();
    //     $fixedDirectorateId = null;
    //     $fixedRoleId = null;

    //     if (in_array(Role::SUPERADMIN, $roleIds)) {
    //         $roles = Role::pluck('title', 'id');
    //         $directorates = Directorate::pluck('title', 'id')->prepend(trans('global.pleaseSelect', []));
    //         $projects = Project::pluck('title', 'id');
    //     } elseif (in_array(Role::DIRECTORATE_USER, $roleIds)) {
    //         $roles = Role::whereIn('id', [Role::DIRECTORATE_USER, Role::PROJECT_USER])->pluck('title', 'id');
    //         $fixedDirectorateId = $user->directorate_id;
    //         $projects = Project::where('directorate_id', $user->directorate_id)->pluck('title', 'id');
    //     } elseif (in_array(Role::PROJECT_USER, $roleIds)) {
    //         $fixedRoleId = [Role::PROJECT_USER];
    //         $fixedDirectorateId = $user->directorate_id;
    //         $projects = $user->projects()->pluck('title', 'id');
    //     }

    //     return compact('roles', 'directorates', 'projects', 'fixedDirectorateId', 'fixedRoleId');
    // }

    public function index(): View
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::with('roles', 'directorate')->latest()->get();

        $headers = [trans('global.user.fields.id'), trans('global.user.fields.name'), trans('global.user.fields.email'), trans('global.user.fields.roles'), trans('global.user.fields.directorate_id')];
        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('title')->toArray() ?? [],
                'directorate_id' => $user->directorate->title ?? '',
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

        $roles = Role::pluck('title', 'id');
        $directorates = Directorate::pluck('title', 'id');
        $projects = Project::pluck('title', 'id');

        return view('admin.users.create', compact('roles', 'directorates', 'projects'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        // abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validated();

        $user = User::create($validated);

        $user->roles()->sync($validated['roles'], []);
        $user->projects()->sync($validated['projects'], []);

        return redirect()->route(route: 'admin.user.index')
            ->with('message', 'User created successfully.');
    }

    public function edit($id): View
    {
        // abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = User::with(['roles', 'directorate', 'projects'])->findOrFail($id);
        $roles = Role::pluck('title', 'id');
        $directorates = Directorate::pluck('title', 'id');
        $projects = $user->directorate_id
            ? Project::where('directorate_id', $user->directorate_id)->pluck('title', 'id')
            : collect([]);

        return view('admin.users.edit', compact('user', 'roles', 'directorates', 'projects'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validated();

        $user->update($validated);

        $user->roles()->sync($validated['roles'] ?? []);

        $user->projects()->sync($validated['projects'] ?? []);

        return redirect()->route(route: 'admin.user.index')
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
