<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreContractRequest;
use App\Http\Requests\Contract\UpdateContractRequest;
use App\Models\Contract;
use App\Models\Directorate;
use App\Models\Project;
use App\Models\Status;
use App\Models\Priority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ContractController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('contract_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contracts = Contract::query()->latest()->get();

        $headers = [trans('global.contract.fields.id'), trans('global.contract.fields.title')];
        $data = $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'title' => $contract->title,
            ];
        })->all();

        return view('admin.contracts.index', [
            'headers' => $headers,
            'data' => $data,
            'contracts' => $contracts,
            'routePrefix' => 'admin.contract',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this contract?',
            'arrayColumnColor' => 'blue',
        ]);
    }

    public function create(): View
    {
        abort_if(Gate::denies('contract_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $directorates = Directorate::pluck('title', 'id');
        $projects = collect();
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');
        return view('admin.contracts.create', compact('directorates', 'projects', 'statuses', 'priorities'));
    }

    public function store(StoreContractRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('contract_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Contract::create($request->validated());

        return redirect()->route('admin.contract.index');
    }

    public function show(Contract $contract)
    {
        //
    }

    public function edit(Contract $contract): View
    {
        abort_if(Gate::denies('contract_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $directorates = Directorate::pluck('title', 'id');
        $projects = Project::where('directorate_id', $contract->directorate_id)
            ->whereNull('deleted_at')
            ->pluck('title', 'id');
        $statuses = Status::pluck('title', 'id');
        $priorities = Priority::pluck('title', 'id');

        $contract->load('directorate', 'project', 'status', 'priority');

        return view('admin.contracts.edit', compact('contract', 'directorates', 'projects', 'statuses', 'priorities'));
    }

    public function update(UpdateContractRequest $request, Contract $contract): RedirectResponse
    {
        abort_if(Gate::denies('contract_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contract->update($request->validated());

        return redirect()->route('admin.contract.index');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        abort_if(Gate::denies('contract_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contract->delete();

        return back();
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
}
