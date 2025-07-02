<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Expense;
use App\Models\ProjectBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
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

        $expenses = $query->paginate(10);
        $projects = Project::all();
        $fiscalYears = FiscalYear::all();
        $budgets = ProjectBudget::whereIn('project_id', $projects->pluck('id'))->get();

        return view('admin.expenses.index', compact('expenses', 'projects', 'fiscalYears', 'budgets'));
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
        $budget = ProjectBudget::where('project_id', $validatedData['project_id'])
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
        $budget = ProjectBudget::where('project_id', $validatedData['project_id'])
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

        Log::info('Expense update request', [
            'expense_id' => $expense->id,
            'project_id' => $validatedData['project_id'],
            'fiscal_year_id' => $validatedData['fiscal_year_id'],
            'budget_type' => $validatedData['budget_type'],
            'amount' => $validatedData['amount'],
            'date' => $validatedData['date'],
            'quarter' => $validatedData['quarter'],
            'available_budget' => $availableBudget,
        ]);

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

        Log::info('Expense delete request', ['expense_id' => $expense->id]);
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
            'budget_type' => 'required|in:internal,foreign_loan,foreign_subsidy',
        ]);

        $budget = ProjectBudget::where('project_id', $validated['project_id'])
            ->where('fiscal_year_id', $validated['fiscal_year_id'])
            ->first();

        if (!$budget) {
            return response()->json(['available' => null]);
        }

        $budgetField = match ($validated['budget_type']) {
            'internal' => 'internal_budget',
            'foreign_loan' => 'foreign_loan_budget',
            'foreign_subsidy' => 'foreign_subsidy_budget',
        };

        $existingExpenses = Expense::where('project_id', $validated['project_id'])
            ->where('fiscal_year_id', $validated['fiscal_year_id'])
            ->where('budget_type', $validated['budget_type'])
            ->sum('amount');

        $available = $budget->$budgetField - $existingExpenses;

        Log::info('Available budget queried', [
            'project_id' => $validated['project_id'],
            'fiscal_year_id' => $validated['fiscal_year_id'],
            'budget_type' => $validated['budget_type'],
            'available' => $available,
        ]);

        return response()->json(['available' => $available]);
    }
}
