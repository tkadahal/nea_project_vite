<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(Gate::denies('expense_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Expense::query()->with(['project', 'fiscalYear', 'user']);

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($fiscalYearId = $request->input('fiscal_year_id')) {
            $query->where('fiscal_year_id', $fiscalYearId);
        }

        if ($budgetType = $request->input('budget_type')) {
            $query->where('budget_type', $budgetType);
        }

        $expenses = $query->latest()->paginate(10);
        $projects = Project::all();
        $fiscalYears = FiscalYear::all();
        $budgetTypes = [
            ['value' => 'internal', 'label' => trans('global.budget.fields.internal_budget')],
            ['value' => 'foreign_loan', 'label' => trans('global.budget.fields.foreign_loan_budget')],
            ['value' => 'foreign_subsidy', 'label' => trans('global.budget.fields.foreign_subsidy_budget')],
        ];

        $headers = [
            trans('global.expense.fields.id'),
            trans('global.expense.fields.project_id'),
            trans('global.expense.fields.fiscal_year_id'),
            trans('global.expense.fields.budget_type'),
            trans('global.expense.fields.amount'),
            trans('global.expense.fields.description'),
            trans('global.expense.fields.date'),
            trans('global.expense.fields.quarter'),
        ];

        $data = $expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'project' => $expense->project->title ?? 'N/A',
                'fiscal_year' => $expense->fiscalYear->title ?? 'N/A',
                'budget_type' => str_replace(
                    ['internal', 'foreign_loan', 'foreign_subsidy'],
                    [
                        trans('global.budget.fields.internal_budget'),
                        trans('global.budget.fields.foreign_loan_budget'),
                        trans('global.budget.fields.foreign_subsidy_budget')
                    ],
                    $expense->budget_type
                ),
                'amount' => $expense->amount,
                'description' => Str::limit($expense->description, 50, '...'),
                'date' => $expense->date->format('M d, Y'),
                'quarter' => 'Q' . $expense->quarter,
            ];
        })->all();

        return view('admin.expenses.index', [
            'headers' => $headers,
            'data' => $data,
            'expenses' => $expenses,
            'projects' => $projects,
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
            'routePrefix' => 'admin.expense',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => __('Are you sure you want to delete this expense?'),
        ]);
    }

    public function create(): View
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $projects = $user->projects()->get();
        $fiscalYears = FiscalYear::all();
        return view('admin.expenses.create', compact('projects', 'fiscalYears'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validatedData = $request->validated();

        // Validate expense against budget
        $budget = Budget::where('project_id', $validatedData['project_id'])
            ->where('fiscal_year_id', $validatedData['fiscal_year_id'])
            ->first();

        if (!$budget) {
            return back()->withErrors(['budget_type' => 'No budget allocated for this project and fiscal year.'])->withInput();
        }

        $budgetField = match ($validatedData['budget_type']) {
            'internal' => 'internal_budget',
            'foreign_loan' => 'foreign_loan_budget',
            'foreign_subsidy' => 'foreign_subsidy_budget',
        };

        $existingExpenses = Expense::where('project_id', $validatedData['project_id'])
            ->where('fiscal_year_id', $validatedData['fiscal_year_id'])
            ->where('budget_type', $validatedData['budget_type'])
            ->sum('amount');

        $availableBudget = $budget->$budgetField - $existingExpenses;

        if ($validatedData['amount'] > $availableBudget) {
            return back()->withErrors([
                'amount' => "The expense amount ({$validatedData['amount']}) exceeds the available {$validatedData['budget_type']} budget ({$availableBudget})."
            ])->withInput();
        }

        Expense::create([
            'project_id' => $validatedData['project_id'],
            'user_id' => Auth::id(),
            'fiscal_year_id' => $validatedData['fiscal_year_id'],
            'budget_type' => $validatedData['budget_type'],
            'amount' => $validatedData['amount'],
            'description' => $validatedData['description'],
            'date' => $validatedData['date'],
            'quarter' => $validatedData['quarter'],
        ]);

        return redirect()->route('admin.expense.index')->with('success', 'Expense added successfully.');
    }

    public function show(Expense $expense): View
    {
        abort_if(Gate::denies('expense_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense->load(['project', 'fiscalYear', 'user']);
        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        abort_if(Gate::denies('expense_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $projects = $user->projects()->get();
        $fiscalYears = FiscalYear::all();
        return view('admin.expenses.edit', compact('expense', 'projects', 'fiscalYears'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        abort_if(Gate::denies('expense_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validatedData = $request->validated();

        // Validate expense against budget
        $budget = Budget::where('project_id', $validatedData['project_id'])
            ->where('fiscal_year_id', $validatedData['fiscal_year_id'])
            ->first();

        if (!$budget) {
            return back()->withErrors(['budget_type' => 'No budget allocated for this project and fiscal year.'])->withInput();
        }

        $budgetField = match ($validatedData['budget_type']) {
            'internal' => 'internal_budget',
            'foreign_loan' => 'foreign_loan_budget',
            'foreign_subsidy' => 'foreign_subsidy_budget',
        };

        $existingExpenses = Expense::where('project_id', $validatedData['project_id'])
            ->where('fiscal_year_id', $validatedData['fiscal_year_id'])
            ->where('budget_type', $validatedData['budget_type'])
            ->where('id', '!=', $expense->id)
            ->sum('amount');

        $availableBudget = $budget->$budgetField - $existingExpenses;

        if ($validatedData['amount'] > $availableBudget) {
            return back()->withErrors([
                'amount' => "The expense amount ({$validatedData['amount']}) exceeds the available {$validatedData['budget_type']} budget ({$availableBudget})."
            ])->withInput();
        }

        $expense->update([
            'project_id' => $validatedData['project_id'],
            'fiscal_year_id' => $validatedData['fiscal_year_id'],
            'budget_type' => $validatedData['budget_type'],
            'amount' => $validatedData['amount'],
            'description' => $validatedData['description'],
            'date' => $validatedData['date'],
            'quarter' => $validatedData['quarter'],
        ]);

        return redirect()->route('admin.expense.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense->delete();
        return redirect()->route('admin.expense.index')->with('success', 'Expense deleted successfully.');
    }

    /**
     * Get the fiscal year for a given date.
     */
    public function byDate(Request $request)
    {
        $date = $request->query('date');
        $fiscalYear = FiscalYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        return response()->json(['fiscal_year_id' => $fiscalYear ? $fiscalYear->id : null]);
    }

    /**
     * Get the available budget for a project, fiscal year, and budget type.
     */
    public function availableBudget(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'fiscal_year_id' => 'required|integer|exists:fiscal_years,id',
            'budget_type' => 'required|in:internal,foreign_loan,foreign_subsidy,government_loan,government_share',
        ]);

        $budget = Budget::where('project_id', $validated['project_id'])
            ->where('fiscal_year_id', $validated['fiscal_year_id'])
            ->first();

        if (!$budget) {
            return response()->json(['available' => null]);
        }

        $remainingBudgetField = match ($validated['budget_type']) {
            'internal' => 'remaining_internal_budget',
            'foreign_loan' => 'remaining_foreign_loan_budget',
            'foreign_subsidy' => 'remaining_foreign_subsidy_budget',
            'government_loan' => 'remaining_government_loan',
            'government_share' => 'remaining_government_share',
        };

        $available = $budget->$remainingBudgetField;

        return response()->json(['available' => number_format($available, 2, '.', '')]);
    }
}
