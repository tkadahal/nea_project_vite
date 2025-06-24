<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreContractRequest;
use App\Http\Requests\Contract\UpdateContractRequest;
use App\Models\Contract;
use App\Models\Directorate;
use App\Models\Role;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('contract_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $contractQuery = Contract::with(['directorate', 'status', 'priority'])->latest();

        try {
            $roleIds = $user->roles->pluck('id')->toArray();

            if (!in_array(Role::SUPERADMIN, $roleIds)) {
                if (in_array(Role::DIRECTORATE_USER, $roleIds)) {
                    // Filter contracts by user's directorate_id from users table
                    if ($user->directorate_id) {
                        $contractQuery->where('directorate_id', $user->directorate_id);
                    } else {
                        Log::warning('No directorate_id assigned to user', ['user_id' => $user->id]);
                        $contractQuery->where('id', 0); // Return empty result if no directorate_id
                    }
                } else {
                    // Filter contracts where user is explicitly assigned
                    $contractQuery->whereHas('users', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in contract filtering', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $data['error'] = 'Unable to load contracts due to an error.';
        }

        $contracts = $contractQuery->get();

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

        $headers = [
            trans('global.contract.fields.id'),
            trans('global.contract.fields.title'),
            'Informations',
        ];

        $tableData = $contracts->map(function ($contract) use ($priorityColors) {
            $priorityValue = $contract->priority?->title ?? 'N/A';
            $priorityDisplayColor = isset($priorityColors[$priorityValue]) ? $priorityColors[$priorityValue] : '#6B7280';

            $fieldsForTable = [];
            $fieldsForTable[] = ['title' => trans('global.contract.fields.contract_agreement_date') . ': ' . ($contract->contract_agreement_date?->format('Y-m-d') ?? 'N/A'), 'color' => 'gray'];
            $fieldsForTable[] = ['title' => trans('global.contract.fields.agreement_completion_date') . ': ' . ($contract->agreement_completion_date?->format('Y-m-d') ?? 'N/A'), 'color' => 'gray'];
            $fieldsForTable[] = ['title' => trans('global.contract.fields.contract_amount') . ': ' . (is_numeric($contract->contract_amount) ? number_format((float) $contract->contract_amount, 2) : 'N/A'), 'color' => 'blue'];
            $fieldsForTable[] = ['title' => trans('global.contract.fields.progress') . ': ' . (is_numeric($contract->progress) ? $contract->progress . '%' : 'N/A'), 'color' => 'green'];
            $fieldsForTable[] = ['title' => trans('global.contract.fields.priority_id') . ': ' . $priorityValue, 'color' => $priorityDisplayColor];

            return [
                'id' => $contract->id,
                'title' => $contract->title,
                'fields' => $fieldsForTable,
            ];
        })->all();

        $cardData = $contracts->map(function ($contract) use ($directorateColors, $priorityColors) {
            $directorateTitle = $contract->directorate?->title ?? 'N/A';
            $directorateId = $contract->directorate?->id ?? null;
            $priorityValue = $contract->priority?->title ?? 'N/A';
            $priorityColor = isset($priorityColors[$priorityValue]) ? $priorityColors[$priorityValue] : '#6B7280';

            $fields = [
                ['label' => trans('global.contract.fields.contract_agreement_date'), 'key' => 'contract_agreement_date', 'value' => $contract->contract_agreement_date?->format('Y-m-d') ?? 'N/A'],
                ['label' => trans('global.contract.fields.agreement_effective_date'), 'key' => 'agreement_effective_date', 'value' => $contract->agreement_effective_date?->format('Y-m-d') ?? 'N/A'],
                ['label' => trans('global.contract.fields.agreement_completion_date'), 'key' => 'agreement_completion_date', 'value' => $contract->agreement_completion_date?->format('Y-m-d') ?? 'N/A'],
                ['label' => trans('global.contract.fields.contract_amount'), 'key' => 'contract_amount', 'value' => is_numeric($contract->contract_amount) ? number_format((float) $contract->contract_amount, 2) : 'N/A'],
                ['label' => trans('global.contract.fields.progress'), 'key' => 'progress', 'value' => is_numeric($contract->progress) ? $contract->progress . '%' : 'N/A'],
                ['label' => trans('global.contract.fields.status_id'), 'key' => 'status', 'value' => $contract->status?->title ?? 'N/A'],
                ['label' => trans('global.contract.fields.priority_id'), 'key' => 'priority', 'value' => $priorityValue, 'color' => $priorityColor],
                ['label' => trans('global.contract.fields.directorate_id'), 'key' => 'directorate', 'value' => $directorateTitle, 'color' => $directorateColors[$directorateId] ?? 'gray'],
            ];

            return [
                'id' => $contract->id,
                'title' => $contract->title,
                'description' => $contract->description ?? 'No description available',
                'directorate' => ['title' => $directorateTitle, 'id' => $directorateId],
                'fields' => $fields,
            ];
        })->all();

        return view('admin.contracts.index', [
            'data' => $cardData,
            'tableData' => $tableData,
            'contracts' => $contracts,
            'routePrefix' => 'admin.contract',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this contract?',
            'arrayColumnColor' => [
                'title' => '#9333EA',
                'contract_amount' => 'blue',
                'progress' => 'green',
                'directorate' => $directorateColors,
                'priority' => $priorityColors,
            ],
            'headers' => $headers,
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
        $contract->load([
            'directorate',
            'project',
            'status',
            'priority',
        ]);

        return view('admin.contracts.show', compact('contract'));
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
