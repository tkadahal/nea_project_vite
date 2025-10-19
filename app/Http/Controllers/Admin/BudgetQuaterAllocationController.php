<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Budget;
use App\Models\Project;
use Illuminate\View\View;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Models\BudgetQuaterAllocation;
use App\Http\Requests\BudgetQuaterAllocation\StoreBudgetQuaterAllocationRequest;

class BudgetQuaterAllocationController extends Controller
{
    public function index()
    {
        //
    }

    public function create(): View
    {
        $user = Auth::user();
        $projects = $user->projects;
        $fiscalYears = FiscalYear::getFiscalYearOptions();

        // Check for passed project_id via query parameter
        $requestedProjectId = request()->get('project_id');
        $selectedProjectId = '';

        if ($requestedProjectId) {
            // Validate: Ensure the project belongs to the user
            $requestedProject = $projects->find($requestedProjectId);
            if ($requestedProject) {
                $selectedProjectId = $requestedProjectId;
            } else {
                // Optional: Log or flash a message for invalid project_id
                session()->flash('warning', 'Invalid project selected. Defaulting to first available project.');
            }
        }

        // Fallback to first project if no valid selection
        if (!$selectedProjectId) {
            $selectedProjectId = $projects->first()?->id ?? '';
        }

        $firstProject = $projects->find($selectedProjectId) ?? $projects->first();
        $selectedFiscalYearId = collect($fiscalYears)->firstWhere('selected', true)['value'] ?? '';

        $currentFiscalYear = FiscalYear::currentFiscalYear();

        if (!$currentFiscalYear) {
            abort(404, 'No current fiscal year found.');
        }

        $budgets = Budget::where('project_id', $selectedProjectId)
            ->where('fiscal_year_id', $currentFiscalYear->id)
            ->get();

        $budgetData = $this->prepareBudgetData($budgets);

        $projectOptions = $projects->map(fn(Project $project) => [
            'value' => $project->id,
            'label' => $project->title,
        ])->toArray();

        return view('admin.budgetQuaterAllocations.create', compact(
            'projectOptions',
            'fiscalYears',
            'budgetData',
            'firstProject',
            'currentFiscalYear',
            'selectedProjectId',
            'selectedFiscalYearId'
        ));
    }

    public function loadBudgets(Request $request): \Illuminate\Http\JsonResponse
    {
        $projectId = $request->input('project_id');
        $fiscalYearId = $request->input('fiscal_year_id');

        if (!$projectId || !$fiscalYearId) {
            return response()->json(['budgetData' => [], 'projectName' => '', 'fiscalYearTitle' => ''], 400);
        }

        $project = Project::find($projectId);
        $fiscalYear = FiscalYear::find($fiscalYearId);

        if (!$project || !$fiscalYear) {
            return response()->json(['budgetData' => [], 'projectName' => '', 'fiscalYearTitle' => ''], 404);
        }

        $budgets = Budget::where('project_id', $projectId)
            ->where('fiscal_year_id', $fiscalYearId)
            ->get();

        $budgetData = $this->prepareBudgetData($budgets);

        return response()->json([
            'budgetData' => $budgetData,
            'projectName' => $project->title,
            'fiscalYearTitle' => $fiscalYear->title,
        ]);
    }

    private function prepareBudgetData($budgets)
    {
        $budgetData = [];
        $counter = 1;

        $fieldMap = [
            'internal_budget' => 'Internal Budget',
            'government_share' => 'Government Share',
            'government_loan' => 'Government Loan',
            'foreign_loan_budget' => 'Foreign Loan',
            'foreign_subsidy_budget' => 'Foreign Subsidy',
            'total_budget' => 'Total Budget',
        ];

        $storageFieldMap = [
            'total_budget' => 'total_budget',
            'internal_budget' => 'internal_budget',
            'government_share' => 'government_share',
            'government_loan' => 'government_loan',
            'foreign_loan_budget' => 'foreign_loan',
            'foreign_subsidy_budget' => 'foreign_subsidy',
        ];

        foreach ($budgets as $budget) {
            foreach ($fieldMap as $budgetField => $title) {
                $amount = $budget->{$budgetField} ?? 0;
                if ($amount > 0) {
                    $storageField = $storageFieldMap[$budgetField];
                    $budgetData[] = [
                        'sn' => $counter++,
                        'title' => $title,
                        'amount' => $amount,
                        'budget_id' => $budget->id,
                        'field' => $storageField,
                    ];
                }
            }
        }

        return $budgetData;
    }

    public function store(StoreBudgetQuaterAllocationRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $budgetIds = $validated['budget_ids'];
            $fields = $validated['fields'];
            $q1Allocations = $validated['q1_allocations'];
            $q2Allocations = $validated['q2_allocations'];
            $q3Allocations = $validated['q3_allocations'];
            $q4Allocations = $validated['q4_allocations'];

            $allocationData = [];
            $index = 0;
            foreach ($budgetIds as $budgetId) {
                $field = $fields[$index];
                $q1 = (float) ($q1Allocations[$index] ?? 0);
                $q2 = (float) ($q2Allocations[$index] ?? 0);
                $q3 = (float) ($q3Allocations[$index] ?? 0);
                $q4 = (float) ($q4Allocations[$index] ?? 0);

                $quartersData = [
                    'Q1' => $q1,
                    'Q2' => $q2,
                    'Q3' => $q3,
                    'Q4' => $q4,
                ];

                foreach ($quartersData as $quarter => $alloc) {
                    if (!isset($allocationData[$budgetId][$quarter])) {
                        $allocationData[$budgetId][$quarter] = [
                            'budget_id' => $budgetId,
                            'quarter' => $quarter,
                            'internal_budget' => 0.00,
                            'government_share' => 0.00,
                            'government_loan' => 0.00,
                            'foreign_loan' => 0.00,
                            'foreign_subsidy' => 0.00,
                            'total_budget' => 0.00,
                        ];
                    }

                    if ($field === 'total_budget') {
                        $allocationData[$budgetId][$quarter]['total_budget'] = $alloc;
                    } else {
                        $allocationData[$budgetId][$quarter][$field] = $alloc;
                    }
                }

                $index++;
            }

            foreach ($allocationData as $budgetId => $quarters) {
                foreach ($quarters as $quarter => $data) {
                    BudgetQuaterAllocation::create($data);
                }
            }

            DB::commit();

            return redirect()->route('admin.budgetQuaterAllocation.index')
                ->with('success', 'Quarterly budget allocations created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing budget quarter allocation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create quarterly budget allocations. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BudgetQuaterAllocation $budgetQuaterAllocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BudgetQuaterAllocation $budgetQuaterAllocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BudgetQuaterAllocation $budgetQuaterAllocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BudgetQuaterAllocation $budgetQuaterAllocation)
    {
        //
    }
}
