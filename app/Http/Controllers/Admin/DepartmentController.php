<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DepartmentController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('department_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $departments = Department::query()->latest()->get();

        $headers = [trans('global.department.fields.id'), trans('global.department.fields.title')];
        $data = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'title' => $department->title,
            ];
        })->all();

        return view('admin.departments.index', [
            'headers' => $headers,
            'data' => $data,
            'departments' => $departments,
            'routePrefix' => 'admin.department',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this department?',
            'arrayColumnColor' => 'blue',
        ]);
    }

    public function create(): View
    {
        abort_if(Gate::denies('department_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.departments.create');
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('department_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Department::create($request->validated());

        return redirect()->route(route: 'admin.department.index')
            ->with('message', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        abort_if(Gate::denies('department_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        abort_if(Gate::denies('department_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        abort_if(Gate::denies('department_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $department->update($request->validated());

        return redirect()->route(route: 'admin.department.index')
            ->with('message', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        abort_if(Gate::denies('department_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $department->delete();

        return back();
    }
}
