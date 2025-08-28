<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Budget;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('budget_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $budgets = Budget::with(['fiscalYear', 'project'])->latest()->get();

        $headers = [
            trans('global.budget.fields.id'),
            trans('global.budget.fields.fiscal_year_id'),
            trans('global.budget.fields.project_id'),
            trans('global.budget.fields.total_budget'),
            trans('global.budget.fields.internal_budget'),
            trans('global.budget.fields.foreign_loan_budget'),
            trans('global.budget.fields.foreign_subsidy_budget'),
            trans('global.budget.fields.government_loan'),
            trans('global.budget.fields.government_share'),
            trans('global.budget.fields.budget_revision'),
        ];

        $data = $budgets->map(function ($budget) {
            return [
                'id' => $budget->id,
                'fiscal_year' => $budget->fiscalYear->title,
                'project' => $budget->project->title,
                'total_budget' => $budget->total_budget,
                'internal_budget' => $budget->internal_budget,
                'foreign_loan' => $budget->foreign_loan_budget,
                'foreign_subsidy' => $budget->foreign_subsidy_budget,
                'government_loan' => $budget->government_loan,
                'government_share' => $budget->government_share,
                'budget_revision' => $budget->budget_revision,
            ];
        })->all();

        return view('admin.budgets.index', [
            'headers' => $headers,
            'data' => $data,
            'budgets' => $budgets,
            'routePrefix' => 'admin.budget',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this project budget?',
        ]);
    }

    public function create(Request $request): View
    {
        abort_if(Gate::denies('budget_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $roleIds = $user->roles->pluck('id')->toArray();

        // Initialize projects collection
        $projects = collect();

        // Fetch projects based on user role
        if (in_array(Role::SUPERADMIN, $roleIds)) {
            $projects = Project::pluck('title', 'id');
        } elseif (in_array(Role::DIRECTORATE_USER, $roleIds)) {
            if ($user->directorate_id) {
                $projects = Project::where('directorate_id', $user->directorate_id)->pluck('title', 'id');
            } else {
                \Illuminate\Support\Facades\Log::warning('No directorate_id assigned to user', ['user_id' => $user->id]);
                $projects = collect();
            }
        }

        // Get project_id from query parameter only
        $projectId = $request->query('project_id');

        // Clear session project_id if no query parameter is provided
        if (!$projectId) {
            Session::forget('project_id');
        } else {
            // Store project_id in session for form repopulation if validation fails
            Session::put('project_id', $projectId);
        }

        $fiscalYears = FiscalYear::pluck('title', 'id')->toArray();

        return view('admin.budgets.create', compact('projects', 'fiscalYears', 'projectId'));
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('budget_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validatedData = $request->validated();

        $project = Project::find($validatedData['project_id']);
        $fiscalYearId = $validatedData['fiscal_year_id'];

        $existingBudget = Budget::where('project_id', $validatedData['project_id'])
            ->where('fiscal_year_id', $fiscalYearId)
            ->first();

        if ($existingBudget) {
            $existingBudget->update([
                'budget_revision' => $existingBudget->budget_revision + 1,
                'internal_budget' => $existingBudget->internal_budget + $validatedData['internal_budget'],
                'foreign_loan_budget' => $existingBudget->foreign_loan_budget + $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $existingBudget->foreign_subsidy_budget + $validatedData['foreign_subsidy_budget'],
                'government_loan' => $existingBudget->government_loan + $validatedData['government_loan'],
                'government_share' => $existingBudget->government_share + $validatedData['government_share'],
                'total_budget' => $existingBudget->total_budget + $validatedData['total_budget'],
            ]);

            $revision = $existingBudget->revisions()->create([
                'internal_budget' => $validatedData['internal_budget'],
                'foreign_loan_budget' => $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $validatedData['foreign_subsidy_budget'],
                'government_loan' => $validatedData['government_loan'],
                'government_share' => $validatedData['government_share'],
                'total_budget' => $validatedData['total_budget'],
                'decision_date' => $validatedData['decision_date'],
                'remarks' => $validatedData['remarks'],
            ]);
        } else {
            $budget = $project->budgets()->create([
                'fiscal_year_id' => $fiscalYearId,
                'internal_budget' => $validatedData['internal_budget'],
                'foreign_loan_budget' => $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $validatedData['foreign_subsidy_budget'],
                'government_loan' => $validatedData['government_loan'],
                'government_share' => $validatedData['government_share'],
                'total_budget' => $validatedData['total_budget'],
                'budget_revision' => 1,
            ]);

            $revision = $budget->revisions()->create([
                'internal_budget' => $validatedData['internal_budget'],
                'foreign_loan_budget' => $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $validatedData['foreign_subsidy_budget'],
                'government_loan' => $validatedData['government_loan'],
                'government_share' => $validatedData['government_share'],
                'total_budget' => $validatedData['total_budget'],
                'decision_date' => $validatedData['decision_date'],
                'remarks' => $validatedData['remarks'],
            ]);
        }

        /** @var \Illuminate\Http\Request $request */

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('revisions', 'public');
                    $revision->files()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'file_type' => $file->extension(),
                        'file_size' => $file->getSize(),
                        'user_id' => Auth::id(),
                    ]);
                }
            }
        }

        // Clear project_id from session after successful submission
        Session::forget('project_id');

        return redirect()->route('admin.budget.index')->with('success', 'Budget saved successfully.');
    }

    public function show(Budget $budget): View
    {
        abort_if(Gate::denies('budget_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $budget->load(['project', 'fiscalYear', 'revisions']);

        return view('admin.budgets.show', [
            'budget' => $budget,
            'revisions' => $budget->revisions()->latest()->get(),
        ]);
    }

    public function edit(Budget $budget)
    {
        //
    }

    public function update(Request $request, Budget $budget)
    {
        //
    }

    public function destroy(Budget $budget)
    {
        //
    }

    public function remaining(Budget $budget)
    {
        abort_if(Gate::denies('budget_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $budget->load('project', 'fiscalYear');
        return view('admin.budgets.remaining', compact('budget'));
    }
}
