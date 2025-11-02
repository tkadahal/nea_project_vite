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
use Symfony\Component\HttpFoundation\Response;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'capital.*.activity_id' => 'required|exists:project_activities,id',
            'capital.*.parent_id' => 'nullable|exists:project_activities,id', // FIXED: Validate against activities, not expenses
            'capital.*.q1' => 'nullable|numeric|min:0',
            'capital.*.q2' => 'nullable|numeric|min:0',
            'capital.*.q3' => 'nullable|numeric|min:0',
            'capital.*.q4' => 'nullable|numeric|min:0',
            // Repeat for 'recurrent'
            'recurrent.*.activity_id' => 'required|exists:project_activities,id',
            'recurrent.*.parent_id' => 'nullable|exists:project_activities,id', // FIXED: Same here
            'recurrent.*.q1' => 'nullable|numeric|min:0',
            'recurrent.*.q2' => 'nullable|numeric|min:0',
            'recurrent.*.q3' => 'nullable|numeric|min:0',
            'recurrent.*.q4' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $userId = $user->id;
            $projectId = $request->project_id;
            $fiscalYearId = $request->fiscal_year_id;

            // Collect all activity data across sections for mapping
            $allActivityData = [];
            foreach (['capital', 'recurrent'] as $section) {
                if ($request->has($section)) {
                    foreach ($request[$section] as $index => $activityData) {
                        $allActivityData[] = [
                            'section' => $section,
                            'index' => $index,
                            'activity_id' => $activityData['activity_id'],
                            'parent_activity_id' => $activityData['parent_id'] ?? null, // Renamed for clarity
                            'q1' => $activityData['q1'] ?? 0,
                            'q2' => $activityData['q2'] ?? 0,
                            'q3' => $activityData['q3'] ?? 0,
                            'q4' => $activityData['q4'] ?? 0,
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
                        // parent_id omitted here
                    ]
                );

                // Handle quarters (only non-zero)
                $quartersData = [
                    ['quarter' => 1, 'amount' => $data['q1']],
                    ['quarter' => 2, 'amount' => $data['q2']],
                    ['quarter' => 3, 'amount' => $data['q3']],
                    ['quarter' => 4, 'amount' => $data['q4']],
                ];

                foreach ($quartersData as $qData) {
                    if ($qData['amount'] > 0) {
                        $expense->quarters()->updateOrCreate(
                            ['quarter' => $qData['quarter']],
                            ['amount' => $qData['amount']]
                        );
                    } else {
                        $expense->quarters()->where('quarter', $qData['quarter'])->delete();
                    }
                }

                // Update grand_total
                $expense->update(['grand_total' => $expense->getGrandTotalAttribute()]);

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

        // Build quarter amount map: activity_id => [q1 => amount, q2 => ..., total => grand_total]
        $activityAmounts = [];
        foreach ($expenses as $expense) {
            $amounts = ['total' => $expense->grand_total ?? 0];
            foreach ($expense->quarters as $q) {
                $amounts['q' . $q->quarter] = $q->amount;
            }
            for ($i = 1; $i <= 4; $i++) {
                if (!isset($amounts['q' . $i])) $amounts['q' . $i] = 0;
            }
            $activityAmounts[$expense->project_activity_id] = $amounts;
        }
        // Default to 0 for activities without expenses
        foreach ($activities as $activity) {
            if (!isset($activityAmounts[$activity->id])) {
                $activityAmounts[$activity->id] = ['total' => 0, 'q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0];
            }
        }

        // Group activities into capital/recurrent (roots only; children loaded)
        $capitalActivities = $activities->where('expenditure_id', 1)->whereNull('parent_id')->values();
        $recurrentActivities = $activities->where('expenditure_id', 2)->whereNull('parent_id')->values();

        // Group all activities by parent_id for hierarchical rendering
        $groupedActivities = $activities->groupBy(fn($a) => $a->parent_id ?? 'null');

        // Compute subtree quarter totals for each activity (sums own + all descendants)
        $subtreeQuarterTotals = [];
        $computeSubtreeQuarters = function ($activityId, $activityAmounts, $groupedActivities) use (&$subtreeQuarterTotals, &$computeSubtreeQuarters) {
            if (isset($subtreeQuarterTotals[$activityId])) {
                return $subtreeQuarterTotals[$activityId];
            }

            $totals = ['q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0];
            $own = $activityAmounts[$activityId] ?? $totals;
            foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
                $totals[$q] = $own[$q];
            }

            if (isset($groupedActivities[$activityId])) {
                foreach ($groupedActivities[$activityId] as $child) {
                    $childTotals = $computeSubtreeQuarters($child->id, $activityAmounts, $groupedActivities);
                    foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
                        $totals[$q] += $childTotals[$q];
                    }
                }
            }

            $subtreeQuarterTotals[$activityId] = $totals;
            return $totals;
        };

        // Compute for all root activities (this will recursively compute for all descendants)
        $allRoots = $capitalActivities->concat($recurrentActivities);
        foreach ($allRoots as $root) {
            $computeSubtreeQuarters($root->id, $activityAmounts, $groupedActivities);
        }

        // Compute totals (sum all activities by expenditure_id, regardless of depth)
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
            'subtreeQuarterTotals',
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
}
