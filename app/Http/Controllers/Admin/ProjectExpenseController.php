<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Expense;
use App\Models\Project;
use App\Models\FiscalYear;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProjectExpense;
use App\Models\ProjectActivity;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProgramExpenseTemplateExport;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ProjectExpense\StoreProjectExpenseRequest;

class ProjectExpenseController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('expense_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $aggregated = DB::table('project_expenses as pe')
            ->selectRaw('
            p.id as project_id,
            p.title as project_title,
            fy.id as fiscal_year_id,
            fy.title as fiscal_year_title,
            COALESCE(SUM(pe.grand_total), 0) as grand_total_sum,
            SUM(
                COALESCE(
                    CASE WHEN pa.expenditure_id = 1 THEN pe.grand_total ELSE 0 END, 0
                )
            ) as capital_grand_sum,
            SUM(
                COALESCE(
                    CASE WHEN pa.expenditure_id = 2 THEN pe.grand_total ELSE 0 END, 0
                )
            ) as recurrent_grand_sum,
            -- Fallback: Sum quarters (q1+q2+q3+q4 per expense, then aggregate)
            SUM(
                COALESCE(q1.amount, 0) + COALESCE(q2.amount, 0) + COALESCE(q3.amount, 0) + COALESCE(q4.amount, 0)
            ) as quarters_total_sum,
            SUM(
                CASE WHEN pa.expenditure_id = 1 THEN
                    COALESCE(q1.amount, 0) + COALESCE(q2.amount, 0) + COALESCE(q3.amount, 0) + COALESCE(q4.amount, 0)
                ELSE 0 END
            ) as capital_quarters_sum,
            SUM(
                CASE WHEN pa.expenditure_id = 2 THEN
                    COALESCE(q1.amount, 0) + COALESCE(q2.amount, 0) + COALESCE(q3.amount, 0) + COALESCE(q4.amount, 0)
                ELSE 0 END
            ) as recurrent_quarters_sum
        ')
            ->join('project_activities as pa', 'pe.project_activity_id', '=', 'pa.id')
            ->join('projects as p', 'pa.project_id', '=', 'p.id')
            ->join('fiscal_years as fy', 'pa.fiscal_year_id', '=', 'fy.id')
            ->leftJoin('project_expense_quarters as q1', function ($join) {
                $join->on('pe.id', '=', 'q1.project_expense_id')
                    ->where('q1.quarter', '=', 1);
            })
            ->leftJoin('project_expense_quarters as q2', function ($join) {
                $join->on('pe.id', '=', 'q2.project_expense_id')
                    ->where('q2.quarter', '=', 2);
            })
            ->leftJoin('project_expense_quarters as q3', function ($join) {
                $join->on('pe.id', '=', 'q3.project_expense_id')
                    ->where('q3.quarter', '=', 3);
            })
            ->leftJoin('project_expense_quarters as q4', function ($join) {
                $join->on('pe.id', '=', 'q4.project_expense_id')
                    ->where('q4.quarter', '=', 4);
            })
            ->whereNull('pe.deleted_at')
            ->groupBy('p.id', 'p.title', 'fy.id', 'fy.title')
            ->orderBy('p.title')
            ->orderBy('fy.title')
            ->get()
            ->map(function ($row) {
                // Use grand_total if >0, else fallback to quarters sum
                $totalExpense = ($row->grand_total_sum > 0) ? $row->grand_total_sum : $row->quarters_total_sum;
                $capitalExpense = ($row->capital_grand_sum > 0) ? $row->capital_grand_sum : $row->capital_quarters_sum;
                $recurrentExpense = ($row->recurrent_grand_sum > 0) ? $row->recurrent_grand_sum : $row->recurrent_quarters_sum;

                return [
                    'project_id' => $row->project_id,
                    'project_title' => $row->project_title,
                    'fiscal_year_id' => $row->fiscal_year_id,
                    'fiscal_year_title' => $row->fiscal_year_title,
                    'total_expense' => $totalExpense,
                    'capital_expense' => $capitalExpense,
                    'recurrent_expense' => $recurrentExpense,
                ];
            });

        return view('admin.projectExpenses.index', compact('aggregated'));
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $projects = $user->projects;
        $fiscalYears = FiscalYear::getFiscalYearOptions();

        // Get selected project ID from request or use first project
        $selectedProjectId = $request->integer('project_id') ?: $projects->first()?->id;

        // Get selected fiscal year ID from request or use the first selected fiscal year
        $selectedFiscalYearId = $request->integer('fiscal_year_id')
            ?: collect($fiscalYears)->firstWhere('selected', true)['value'] ?? null;

        $projectOptions = $projects->map(function (Project $project) use ($selectedProjectId) {
            return [
                'value' => $project->id,
                'label' => $project->title,
                'selected' => $project->id == $selectedProjectId,
            ];
        })->toArray();

        $selectedProject = $projects->find($selectedProjectId) ?? $projects->first();

        // Preload activities if both project and fiscal year are selected
        $preloadActivities = !empty($selectedProjectId) && !empty($selectedFiscalYearId);

        return view('admin.projectExpenses.create', compact(
            'projects',
            'projectOptions',
            'fiscalYears',
            'selectedProject',
            'selectedProjectId',
            'selectedFiscalYearId',
            'preloadActivities'
        ));
    }

    public function store(StoreProjectExpenseRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $userId = $user->id;
            $validatedData = $request->validated();

            $projectId = $validatedData['project_id'];
            $fiscalYearId = $validatedData['fiscal_year_id'];

            // Collect all activity data across sections for mapping
            $allActivityData = [];
            foreach (['capital', 'recurrent'] as $section) {
                if (isset($validatedData[$section])) {
                    foreach ($validatedData[$section] as $index => $activityData) {
                        $allActivityData[] = [
                            'section' => $section,
                            'index' => $index,
                            'activity_id' => $activityData['activity_id'],
                            'parent_activity_id' => $activityData['parent_id'] ?? null, // Renamed for clarity
                            'q1_qty' => $activityData['q1_qty'] ?? 0,
                            'q1_amt' => $activityData['q1_amt'] ?? 0,
                            'q2_qty' => $activityData['q2_qty'] ?? 0,
                            'q2_amt' => $activityData['q2_amt'] ?? 0,
                            'q3_qty' => $activityData['q3_qty'] ?? 0,
                            'q3_amt' => $activityData['q3_amt'] ?? 0,
                            'q4_qty' => $activityData['q4_qty'] ?? 0,
                            'q4_amt' => $activityData['q4_amt'] ?? 0,
                            'description' => $activityData['description'] ?? null,
                        ];
                    }
                }
            }

            // Step 1: Create/Update all expenses WITHOUT parent_id (to avoid FK issues)
            $activityToExpenseMap = []; // activity_id => expense_id
            foreach ($allActivityData as $data) {
                $activity = ProjectActivity::findOrFail($data['activity_id']);
                if ($activity->project_id != $projectId || $activity->fiscal_year_id != $fiscalYearId) {
                    throw new \InvalidArgumentException("Activity {$data['activity_id']} does not match selected project/fiscal year.");
                }

                $expense = ProjectExpense::firstOrCreate(
                    ['project_activity_id' => $data['activity_id']],
                    [
                        'user_id' => $userId,
                        'description' => $data['description'],
                        'effective_date' => now(), // Or null if not needed; adjust as per requirements
                        'grand_total' => 0.00, // Default value, no calculation for now
                        // parent_id omitted here
                    ]
                );

                // Handle quarters (only non-zero qty or amt)
                $quartersData = [
                    ['quarter' => 1, 'quantity' => $data['q1_qty'], 'amount' => $data['q1_amt']],
                    ['quarter' => 2, 'quantity' => $data['q2_qty'], 'amount' => $data['q2_amt']],
                    ['quarter' => 3, 'quantity' => $data['q3_qty'], 'amount' => $data['q3_amt']],
                    ['quarter' => 4, 'quantity' => $data['q4_qty'], 'amount' => $data['q4_amt']],
                ];

                foreach ($quartersData as $qData) {
                    if ($qData['quantity'] > 0 || $qData['amount'] > 0) {
                        $expense->quarters()->updateOrCreate(
                            ['quarter' => $qData['quarter']],
                            [
                                'quantity' => $qData['quantity'],
                                'amount' => $qData['amount']
                            ]
                        );
                    } else {
                        $expense->quarters()->where('quarter', $qData['quarter'])->delete();
                    }
                }

                // Map activity to expense
                $activityToExpenseMap[$data['activity_id']] = $expense->id;
            }

            // Step 2: Set parent_id on expenses (now that all exist; mirrors activity hierarchy)
            foreach ($allActivityData as $data) {
                if ($data['parent_activity_id']) {
                    $parentExpenseId = $activityToExpenseMap[$data['parent_activity_id']] ?? null;
                    if (!$parentExpenseId) {
                        throw new \InvalidArgumentException("Parent activity {$data['parent_activity_id']} has no corresponding expense.");
                    }

                    ProjectExpense::where('id', $activityToExpenseMap[$data['activity_id']])
                        ->update(['parent_id' => $parentExpenseId]);
                }
            }

            DB::commit();
            return redirect()->route('admin.projectExpense.index')->with('success', 'Expenses saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save: ' . $e->getMessage()]);
        }
    }

    public function show(int $projectId, int $fiscalYearId)
    {
        abort_if(Gate::denies('expense_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project = Project::findOrFail($projectId);
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        // Load activities hierarchy for this project/fiscal with limited depth eager loading
        $activities = ProjectActivity::where('project_id', $projectId)
            ->where('fiscal_year_id', $fiscalYearId)
            ->with([
                'children:id,parent_id,program,q1,q2,q3,q4,expenditure_id',
                'children.children:id,parent_id,program,q1,q2,q3,q4,expenditure_id', // Depth 2; extend if deeper
                'children.children.children:id,parent_id,program,q1,q2,q3,q4,expenditure_id' // Depth 3
            ])
            ->get();

        // Load all expenses and quarters for these activities
        $activityIds = $activities->pluck('id')->toArray();
        $expenses = ProjectExpense::with(['quarters', 'projectActivity:id,parent_id,expenditure_id'])
            ->whereIn('project_activity_id', $activityIds)
            ->get()
            ->keyBy('project_activity_id'); // Map: activity_id => expense

        // Build quarter quantity and amount map: activity_id => [q1_qty => qty, q1_amt => amt, ..., total => grand_total]
        $activityAmounts = [];
        foreach ($expenses as $expense) {
            $activityAmounts[$expense->project_activity_id] = ['total' => $expense->grand_total ?? 0];
            foreach ($expense->quarters as $q) {
                $activityAmounts[$expense->project_activity_id]['q' . $q->quarter . '_qty'] = $q->quantity;
                $activityAmounts[$expense->project_activity_id]['q' . $q->quarter . '_amt'] = $q->amount;
            }
            for ($i = 1; $i <= 4; $i++) {
                $qtyKey = 'q' . $i . '_qty';
                $amtKey = 'q' . $i . '_amt';
                if (!isset($activityAmounts[$expense->project_activity_id][$qtyKey])) {
                    $activityAmounts[$expense->project_activity_id][$qtyKey] = 0;
                }
                if (!isset($activityAmounts[$expense->project_activity_id][$amtKey])) {
                    $activityAmounts[$expense->project_activity_id][$amtKey] = 0;
                }
            }
        }
        // Default to 0 for activities without expenses
        foreach ($activities as $activity) {
            if (!isset($activityAmounts[$activity->id])) {
                $activityAmounts[$activity->id] = [
                    'total' => 0,
                    'q1_qty' => 0,
                    'q1_amt' => 0,
                    'q2_qty' => 0,
                    'q2_amt' => 0,
                    'q3_qty' => 0,
                    'q3_amt' => 0,
                    'q4_qty' => 0,
                    'q4_amt' => 0,
                ];
            }
        }

        // Group activities into capital/recurrent (roots only; children loaded)
        $capitalActivities = $activities->where('expenditure_id', 1)->whereNull('parent_id')->values();
        $recurrentActivities = $activities->where('expenditure_id', 2)->whereNull('parent_id')->values();

        // Group all activities by parent_id for hierarchical rendering
        $groupedActivities = $activities->groupBy(fn($a) => $a->parent_id ?? 'null');

        // Compute subtree quarter totals for each activity (sums own + all descendants) - amounts only
        $subtreeAmountTotals = [];
        $computeSubtreeAmounts = function ($activityId, $activityAmounts, $groupedActivities) use (&$subtreeAmountTotals, &$computeSubtreeAmounts) {
            if (isset($subtreeAmountTotals[$activityId])) {
                return $subtreeAmountTotals[$activityId];
            }

            $totals = ['q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0];
            $own = $activityAmounts[$activityId] ?? $totals;
            foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
                $totals[$q] = $own[$q . '_amt'] ?? 0;
            }

            if (isset($groupedActivities[$activityId])) {
                foreach ($groupedActivities[$activityId] as $child) {
                    $childTotals = $computeSubtreeAmounts($child->id, $activityAmounts, $groupedActivities);
                    foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
                        $totals[$q] += $childTotals[$q];
                    }
                }
            }

            $subtreeAmountTotals[$activityId] = $totals;
            return $totals;
        };

        // Compute subtree quarter quantities for each activity (sums own + all descendants)
        $subtreeQuantityTotals = [];
        $computeSubtreeQuantities = function ($activityId, $activityAmounts, $groupedActivities) use (&$subtreeQuantityTotals, &$computeSubtreeQuantities) {
            if (isset($subtreeQuantityTotals[$activityId])) {
                return $subtreeQuantityTotals[$activityId];
            }

            $totals = ['q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0];
            $own = $activityAmounts[$activityId] ?? $totals;
            foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
                $totals[$q] = $own[$q . '_qty'] ?? 0;
            }

            if (isset($groupedActivities[$activityId])) {
                foreach ($groupedActivities[$activityId] as $child) {
                    $childTotals = $computeSubtreeQuantities($child->id, $activityAmounts, $groupedActivities);
                    foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
                        $totals[$q] += $childTotals[$q];
                    }
                }
            }

            $subtreeQuantityTotals[$activityId] = $totals;
            return $totals;
        };

        // Compute for all root activities (this will recursively compute for all descendants)
        $allRoots = $capitalActivities->concat($recurrentActivities);
        foreach ($allRoots as $root) {
            $computeSubtreeAmounts($root->id, $activityAmounts, $groupedActivities);
            $computeSubtreeQuantities($root->id, $activityAmounts, $groupedActivities);
        }

        // Compute totals (sum all activities by expenditure_id, regardless of depth) - amounts only
        $totalExpense = collect($activityAmounts)->sum('total');
        $capitalTotal = $activities->where('expenditure_id', 1)->sum(fn($activity) => $activityAmounts[$activity->id]['total'] ?? 0);
        $recurrentTotal = $activities->where('expenditure_id', 2)->sum(fn($activity) => $activityAmounts[$activity->id]['total'] ?? 0);

        return view('admin.projectExpenses.show', compact(
            'project',
            'fiscalYear',
            'capitalActivities',
            'recurrentActivities',
            'activityAmounts',
            'groupedActivities',
            'subtreeAmountTotals',
            'subtreeQuantityTotals',
            'totalExpense',
            'capitalTotal',
            'recurrentTotal'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectExpense $projectExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectExpense $projectExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectExpense $projectExpense)
    {
        //
    }

    public function downloadExcel(Request $request, int $projectId, int $fiscalYearId)
    {
        $project = Project::findOrFail($projectId);
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $quarter = $request->query('quarter', 1);
        if (!in_array($quarter, [1, 2, 3, 4])) {
            $quarter = 1;
        }

        $safeProjectTitle = str_replace(['/', '\\'], '_', Str::slug($project->title));
        $safeFiscalTitle = str_replace(['/', '\\'], '_', $fiscalYear->title);

        return Excel::download(
            new ProgramExpenseTemplateExport($project->title, $fiscalYear->title, $projectId, $fiscalYearId, $quarter),
            'ExpenseReport_' . $project->title . '_' . $safeFiscalTitle . '.xlsx'
        );
    }
}
