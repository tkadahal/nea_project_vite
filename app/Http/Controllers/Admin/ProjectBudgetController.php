<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\ProjectBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectBudgetController extends Controller
{
    public function index(): View
    {
        $projectBudgets = ProjectBudget::with(['fiscalYear', 'project'])->latest()->get();

        $headers = ['ID', 'Fiscal Year', 'Project', 'Total Budget', 'Internal Budget', 'Foreign Loan Budget', 'Foreign Subsidy Budget', 'Budget Revision'];

        $data = $projectBudgets->map(function ($projectBudget) {
            return [
                'id' => $projectBudget->id,
                'fiscal_year' => $projectBudget->fiscalYear->title,
                'project' => $projectBudget->project->title,
                'total_budget' => $projectBudget->total_budget,
                'internal_budget' => $projectBudget->internal_budget,
                'foreign_loan' => $projectBudget->foreign_loan_budget,
                'foreign_subsidy' => $projectBudget->foreign_subsidy_budget,
                'budget_revision' => $projectBudget->budget_revision,
            ];
        })->all();

        return view('admin.projectBudgets.index', [
            'headers' => $headers,
            'data' => $data,
            'projectBudgets' => $projectBudgets,
            'routePrefix' => 'admin.projectBudget',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this project budget?',
        ]);
    }

    public function create(): View
    {
        $projects = Project::all();
        $fiscalYears = FiscalYear::pluck('title', 'id')->toArray();

        return view('admin.projectBudgets.create', compact('projects', 'fiscalYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'fiscal_year_id' => 'required|integer|exists:fiscal_years,id',
            'internal_budget' => 'required|numeric|min:0',
            'foreign_loan_budget' => 'required|numeric|min:0',
            'foreign_subsidy_budget' => 'required|numeric|min:0',
            'total_budget' => 'required|numeric|min:0',
        ]);

        $project = Project::find($validatedData['project_id']);
        $fiscalYearId = $validatedData['fiscal_year_id'];

        $existingBudget = ProjectBudget::where('project_id', $validatedData['project_id'])
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

        return redirect()->route('admin.projectBudget.index')->with('success', 'Budget saved successfully.');
    }

    public function show(ProjectBudget $projectBudget): View
    {
        $projectBudget->load(['project', 'fiscalYear', 'revisions']);

        return view('admin.projectBudgets.show', [
            'projectBudget' => $projectBudget,
            'revisions' => $projectBudget->revisions()->latest()->get(),
        ]);
    }

    public function edit(ProjectBudget $projectBudget)
    {
        //
    }

    public function update(Request $request, ProjectBudget $projectBudget)
    {
        //
    }

    public function destroy(ProjectBudget $projectBudget)
    {
        //
    }
}
