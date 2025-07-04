<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends Controller
{
    public function index(): View
    {
        abort_if(Gate::denies('budget_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $budgets = Budget::with(['fiscalYear', 'project'])->latest()->get();

        $headers = ['ID', 'Fiscal Year', 'Project', 'Total Budget', 'Internal Budget', 'Foreign Loan Budget', 'Foreign Subsidy Budget', 'Budget Revision'];

        $data = $budgets->map(function ($budget) {
            return [
                'id' => $budget->id,
                'fiscal_year' => $budget->fiscalYear->title,
                'project' => $budget->project->title,
                'total_budget' => $budget->total_budget,
                'internal_budget' => $budget->internal_budget,
                'foreign_loan' => $budget->foreign_loan_budget,
                'foreign_subsidy' => $budget->foreign_subsidy_budget,
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

    public function create(): View
    {
        abort_if(Gate::denies('budget_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $projects = Project::where('directorate_id', $user->directorate_id)->get();
        $fiscalYears = FiscalYear::pluck('title', 'id')->toArray();

        return view('admin.budgets.create', compact('projects', 'fiscalYears'));
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
                'total_budget' => $existingBudget->total_budget + $validatedData['total_budget'],
            ]);

            $existingBudget->revisions()->create([
                'internal_budget' => $validatedData['internal_budget'],
                'foreign_loan_budget' => $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $validatedData['foreign_subsidy_budget'],
                'total_budget' => $validatedData['total_budget'],
            ]);
        } else {
            $budget = $project->budgets()->create([
                'fiscal_year_id' => $fiscalYearId,
                'internal_budget' => $validatedData['internal_budget'],
                'foreign_loan_budget' => $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $validatedData['foreign_subsidy_budget'],
                'total_budget' => $validatedData['total_budget'],
                'budget_revision' => 1,
            ]);

            $budget->revisions()->create([
                'internal_budget' => $validatedData['internal_budget'],
                'foreign_loan_budget' => $validatedData['foreign_loan_budget'],
                'foreign_subsidy_budget' => $validatedData['foreign_subsidy_budget'],
                'total_budget' => $validatedData['total_budget'],
            ]);
        }

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
}
