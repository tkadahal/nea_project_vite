<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Role;
use App\Models\Project;
use Illuminate\View\View;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use App\Models\ProjectActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\ProjectActivityTemplateExport;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ProjectActivity\StoreProjectActivityRequest;

class ProjectActivityController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(Gate::denies('projectActivity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $activityQuery = ProjectActivity::with(['project', 'fiscalYear'])
            ->select(
                'project_id',
                'fiscal_year_id',
                DB::raw('SUM(CASE WHEN parent_id IS NULL AND expenditure_id = 1 THEN total_budget ELSE 0 END) as capital_budget'),
                DB::raw('SUM(CASE WHEN parent_id IS NULL AND expenditure_id = 2 THEN total_budget ELSE 0 END) as recurrent_budget'),
                DB::raw('SUM(CASE WHEN parent_id IS NULL THEN total_budget ELSE 0 END) as total_budget'),
                DB::raw('MAX(created_at) as latest_created_at')
            )
            ->groupBy('project_id', 'fiscal_year_id')
            ->havingRaw('SUM(CASE WHEN parent_id IS NULL THEN total_budget ELSE 0 END) > 0')
            ->orderBy('latest_created_at', 'desc');

        try {
            $roleIds = $user->roles->pluck('id')->toArray();

            if (!in_array(Role::SUPERADMIN, $roleIds) && !in_array(Role::ADMIN, $roleIds)) {
                if (in_array(Role::DIRECTORATE_USER, $roleIds)) {
                    $directorateId = $user->directorate ? [$user->directorate->id] : [];
                    $activityQuery->whereHas('project', function ($query) use ($directorateId) {
                        $query->whereIn('id', $directorateId);
                    });
                } elseif (in_array(Role::PROJECT_USER, $roleIds)) {
                    $projectIds = $user->projects()->pluck('id')->toArray();
                    $activityQuery->whereHas('project', function ($query) use ($projectIds) {
                        $query->whereIn('id', $projectIds);
                    });
                } else {
                    $activityQuery->where('id', $user->id);
                }
            }
        } catch (\Exception $e) {
            $data['error'] = 'Unable to load users due to an error.';
        }

        $activities = $activityQuery->get();

        $headers = [
            'Id',
            'Fiscal Year',
            'Project',
            'Total Budget',
            'Capital Budget',
            'Recurrent Budget',
            'Actions',
        ];

        $data = $activities->map(function ($activity) {
            return [
                'project_id' => $activity->project_id,
                'fiscal_year_id' => $activity->fiscal_year_id,
                'project' => $activity->project->title,
                'fiscal_year' => $activity->fiscalYear->title,
                'capital_budget' => $activity->capital_budget,
                'recurrent_budget' => $activity->recurrent_budget,
                'total_budget' => $activity->total_budget,
            ];
        })->all();

        return view('admin.project-activities.index', [
            'headers' => $headers,
            'data' => $data,
            'activities' => $activities,
            'routePrefix' => 'admin.project-activity',
            'actions' => ['view', 'edit', 'delete'],
            'deleteConfirmationMessage' => 'Are you sure you want to delete this project activity?',
        ]);
    }

    public function create(Request $request): view
    {
        abort_if(Gate::denies('projectActivity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $projects = $user->projects;
        $fiscalYears = FiscalYear::getFiscalYearOptions();

        $selectedProjectId = $request->integer('project_id') ?? $projects->first()?->id;
        $selectedProject = $projects->find($selectedProjectId) ?? $projects->first();

        $projectOptions = $projects->map(fn(Project $project) => [
            'value' => $project->id,
            'label' => $project->title,
        ])->toArray();

        // Eager-load activities if relations are used downstream
        $capitalActivities = $selectedProject?->projectActivities()
            ->with([]) // Add relations if needed
            ->where('expenditure_id', 1)
            ->get() ?? collect();
        $recurrentActivities = $selectedProject?->projectActivities()
            ->with([])
            ->where('expenditure_id', 2)
            ->get() ?? collect();

        return view('admin.project-activities.create', compact(
            'projects',
            'projectOptions',
            'fiscalYears',
            'selectedProject',
            'selectedProjectId',
            'capitalActivities',
            'recurrentActivities'
        ));
    }

    public function store(StoreProjectActivityRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $project = Project::findOrFail($validated['project_id']);

            $sections = ['capital', 'recurrent'];

            foreach ($sections as $section) {
                if (!isset($validated[$section]) || !is_array($validated[$section])) {
                    continue;
                }

                $expenditureId = ($section === 'capital') ? 1 : 2;
                $activities = $validated[$section];
                $savedMap = []; // Map form index → saved ID

                // 1️⃣ Save parent activities first (top-level: no parent_id)
                foreach ($activities as $index => $activityData) {
                    if (
                        !array_key_exists('parent_id', $activityData) ||
                        $activityData['parent_id'] === null ||
                        $activityData['parent_id'] === ''
                    ) {

                        $activity = ProjectActivity::create([
                            'project_id' => $project->id,
                            'fiscal_year_id' => $validated['fiscal_year_id'],
                            'expenditure_id' => $expenditureId,
                            'program' => $activityData['program'],
                            'total_budget' => $activityData['total_budget'] ?? 0,
                            'total_expense' => $activityData['total_expense'] ?? 0,
                            'planned_budget' => $activityData['planned_budget'] ?? 0,
                            'q1' => $activityData['q1'] ?? 0,
                            'q2' => $activityData['q2'] ?? 0,
                            'q3' => $activityData['q3'] ?? 0,
                            'q4' => $activityData['q4'] ?? 0,
                            'parent_id' => null,
                        ]);

                        $savedMap[$index] = $activity->id;
                    }
                }

                // 2️⃣ Save child activities after all parents exist
                foreach ($activities as $index => $activityData) {
                    if (
                        !array_key_exists('parent_id', $activityData) ||
                        $activityData['parent_id'] === null ||
                        $activityData['parent_id'] === ''
                    ) {
                        continue; // Already saved above
                    }

                    $parentFormIndex = $activityData['parent_id'];
                    if (!isset($savedMap[$parentFormIndex])) {
                        Log::warning("Invalid parent_form_index '{$parentFormIndex}' for row {$index} in {$section}");
                        continue;
                    }

                    $activity = ProjectActivity::create([
                        'project_id' => $project->id,
                        'fiscal_year_id' => $validated['fiscal_year_id'],
                        'expenditure_id' => $expenditureId,
                        'program' => $activityData['program'],
                        'total_budget' => $activityData['total_budget'] ?? 0,
                        'total_expense' => $activityData['total_expense'] ?? 0,
                        'planned_budget' => $activityData['planned_budget'] ?? 0,
                        'q1' => $activityData['q1'] ?? 0,
                        'q2' => $activityData['q2'] ?? 0,
                        'q3' => $activityData['q3'] ?? 0,
                        'q4' => $activityData['q4'] ?? 0,
                        'parent_id' => $savedMap[$parentFormIndex],
                    ]);

                    $savedMap[$index] = $activity->id;
                }
            }

            DB::commit();

            return redirect()->route('admin.projectActivity.index')->with('success', 'Project activities saved successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to save project activities: " . $e->getMessage(), [
                'project_id' => $validated['project_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to save project activities: ' . $e->getMessage()]);
        }
    }

    public function show(int $projectId, int $fiscalYearId): View
    {
        abort_if(Gate::denies('projectActivity_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project = Project::findOrFail($projectId);
        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        // Load activities with hierarchy (up to depth 2)
        $capitalActivities = $project->projectActivities()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('expenditure_id', 1)
            ->with('children.children')
            ->get();
        $recurrentActivities = $project->projectActivities()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('expenditure_id', 2)
            ->with('children.children')
            ->get();

        $totalCapitalRows = $capitalActivities->sum(fn($act) => 1 + $act->children->count() + $act->children->sum(fn($child) => $child->children->count()));
        $totalRecurrentRows = $recurrentActivities->sum(fn($act) => 1 + $act->children->count() + $act->children->sum(fn($child) => $child->children->count()));

        $totalActivities = $project->projectActivities()->where('fiscal_year_id', $fiscalYearId)->count();
        $totalPlanned = $project->projectActivities()->where('fiscal_year_id', $fiscalYearId)->sum('planned_budget');
        $totalExpense = $project->projectActivities()->where('fiscal_year_id', $fiscalYearId)->sum('total_expense');

        return view('admin.project-activities.show', compact('capitalActivities', 'recurrentActivities', 'totalCapitalRows', 'totalRecurrentRows', 'project', 'fiscalYear', 'totalActivities', 'totalPlanned', 'totalExpense'));
    }

    public function edit(int $projectId, int $fiscalYearId): View
    {
        abort_if(Gate::denies('projectActivity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();
        $project = Project::findOrFail($projectId);

        // Assuming user-project relation check
        if (!$project->users->contains($user->id)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $fiscalYear = FiscalYear::findOrFail($fiscalYearId);

        $projects = $user->projects;
        $fiscalYears = FiscalYear::getFiscalYearOptions();

        $projectOptions = $projects->map(fn(Project $project) => [
            'value' => $project->id,
            'label' => $project->title,
        ])->toArray();

        // Load activities with hierarchy (up to depth 2)
        $capitalActivities = $project->projectActivities()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('expenditure_id', 1)
            ->with('children.children')
            ->get();
        $recurrentActivities = $project->projectActivities()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('expenditure_id', 2)
            ->with('children.children')
            ->get();

        // Compute total rows for JS indices
        $totalCapitalRows = $capitalActivities->sum(fn($act) => 1 + $act->children->count() + $act->children->sum(fn($child) => $child->children->count()));
        $totalRecurrentRows = $recurrentActivities->sum(fn($act) => 1 + $act->children->count() + $act->children->sum(fn($child) => $child->children->count()));

        return view('admin.project-activities.edit', compact(
            'project',
            'fiscalYear',
            'projectOptions',
            'fiscalYears',
            'capitalActivities',
            'recurrentActivities',
            'totalCapitalRows',
            'totalRecurrentRows',
            'projectId',
            'fiscalYearId'
        ));
    }

    public function update(StoreProjectActivityRequest $request, int $projectId, int $fiscalYearId)
    {
        $validated = $request->validated();

        // Ensure project and fiscal year match
        $project = Project::findOrFail($projectId);
        if ($validated['project_id'] != $projectId || $validated['fiscal_year_id'] != $fiscalYearId) {
            return back()->withErrors(['error' => 'Project or fiscal year mismatch.']);
        }

        DB::beginTransaction();

        try {
            $sections = ['capital', 'recurrent'];

            foreach ($sections as $section) {
                if (!isset($validated[$section]) || !is_array($validated[$section])) {
                    continue;
                }

                $expenditureId = ($section === 'capital') ? 1 : 2;
                $activities = $validated[$section];
                $savedMap = []; // Map form index → saved/updated ID
                $submittedIds = collect($activities)->pluck('id')->filter()->toArray(); // For cleanup

                // Soft-delete all existing for this project/fy/section
                ProjectActivity::where('project_id', $project->id)
                    ->where('fiscal_year_id', $fiscalYearId)
                    ->where('expenditure_id', $expenditureId)
                    ->delete(); // Soft delete

                // 1️⃣ Process parent activities first (top-level: no parent_id)
                foreach ($activities as $index => $activityData) {
                    if (
                        !array_key_exists('parent_id', $activityData) ||
                        $activityData['parent_id'] === null ||
                        $activityData['parent_id'] === ''
                    ) {
                        $activityId = $activityData['id'] ?? null;

                        // Update if existing ID (restore from soft-deleted), else create new
                        if ($activityId) {
                            $activity = ProjectActivity::withTrashed()->findOrFail($activityId);
                            $activity->restore(); // Restore if soft-deleted
                        } else {
                            $activity = new ProjectActivity();
                        }

                        $activity->update([
                            'project_id' => $project->id,
                            'fiscal_year_id' => $fiscalYearId,
                            'expenditure_id' => $expenditureId,
                            'program' => $activityData['program'],
                            'total_budget' => $activityData['total_budget'] ?? 0,
                            'total_expense' => $activityData['total_expense'] ?? 0,
                            'planned_budget' => $activityData['planned_budget'] ?? 0,
                            'q1' => $activityData['q1'] ?? 0,
                            'q2' => $activityData['q2'] ?? 0,
                            'q3' => $activityData['q3'] ?? 0,
                            'q4' => $activityData['q4'] ?? 0,
                            'parent_id' => null,
                        ]);

                        $savedMap[$index] = $activity->id;
                    }
                }

                // 2️⃣ Process child activities after all parents exist
                foreach ($activities as $index => $activityData) {
                    if (
                        !array_key_exists('parent_id', $activityData) ||
                        $activityData['parent_id'] === null ||
                        $activityData['parent_id'] === ''
                    ) {
                        continue; // Already processed above
                    }

                    $parentFormIndex = $activityData['parent_id']; // This is now real DB ID? Wait, no—in form, it's form index? Wait.
                    // In our updated view/JS: parent_id is real DB ID for existing/new.
                    // But in loop, $activityData['parent_id'] is the submitted value: for children, it's the parent's real DB ID.
                    // So, no need for $savedMap[$parentFormIndex]—directly use $activityData['parent_id'] as parent DB ID.
                    // But to ensure parent exists, we can verify it's in $savedMap or recent saves.

                    // Since we process top-level first, parents are saved/updated.
                    // For children: parent_id is direct DB ID of parent (from form).
                    $parentDbId = $activityData['parent_id'];

                    // Verify parent exists (quick check)
                    $parentActivity = ProjectActivity::where('id', $parentDbId)
                        ->where('project_id', $project->id)
                        ->where('fiscal_year_id', $fiscalYearId)
                        ->where('expenditure_id', $expenditureId)
                        ->first();

                    if (!$parentActivity) {
                        Log::warning("Invalid parent_id '{$parentDbId}' for row {$index} in {$section}");
                        continue;
                    }

                    $activityId = $activityData['id'] ?? null;

                    // Update if existing ID (restore from soft-deleted), else create new
                    if ($activityId) {
                        $activity = ProjectActivity::withTrashed()->findOrFail($activityId);
                        $activity->restore(); // Restore if soft-deleted
                    } else {
                        $activity = new ProjectActivity();
                    }

                    $activity->update([
                        'project_id' => $project->id,
                        'fiscal_year_id' => $fiscalYearId,
                        'expenditure_id' => $expenditureId,
                        'program' => $activityData['program'],
                        'total_budget' => $activityData['total_budget'] ?? 0,
                        'total_expense' => $activityData['total_expense'] ?? 0,
                        'planned_budget' => $activityData['planned_budget'] ?? 0,
                        'q1' => $activityData['q1'] ?? 0,
                        'q2' => $activityData['q2'] ?? 0,
                        'q3' => $activityData['q3'] ?? 0,
                        'q4' => $activityData['q4'] ?? 0,
                        'parent_id' => $parentDbId, // Direct DB ID
                    ]);

                    $savedMap[$index] = $activity->id;
                }

                // 3️⃣ Permanent delete truly removed ones (not resubmitted)
                ProjectActivity::where('project_id', $project->id)
                    ->where('fiscal_year_id', $fiscalYearId)
                    ->where('expenditure_id', $expenditureId)
                    ->whereNotIn('id', $submittedIds)
                    ->forceDelete();
            }

            DB::commit();

            return redirect()->route('admin.projectActivity.index')->with('success', 'Project activities updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to update project activities: " . $e->getMessage(), [
                'project_id' => $projectId,
                'fiscal_year_id' => $fiscalYearId,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to update project activities: ' . $e->getMessage()]);
        }
    }

    public function destroy(ProjectActivity $projectActivity): Response
    {
        abort_if(Gate::denies('projectActivity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $projectActivity->delete();

        return response()->json(['message' => 'Project activity deleted successfully'], 200);
    }

    public function downloadTemplate(Request $request): Response
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
        ]);

        $projectId = $request->integer('project_id');
        $fiscalYearId = $request->integer('fiscal_year_id');

        $selectedProject = Project::where('id', $projectId)->first();
        if (!$selectedProject) {
            throw new Exception('Selected project not found.');
        }

        $selectedFiscalYear = FiscalYear::where('id', $fiscalYearId)->first();
        if (!$selectedFiscalYear) {
            throw new Exception('Selected fiscal year not found.');
        }

        // Pass selected values to export
        return Excel::download(
            new ProjectActivityTemplateExport($selectedProject->title, $selectedFiscalYear->title),
            'project_activity_' . $selectedProject->title . '_template.xlsx'
        );
    }

    public function showUploadForm(Request $request): View
    {
        abort_if(Gate::denies('projectActivity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Simplified form: just file upload, selections are in Excel
        return view('admin.project-activities.upload');
    }

    public function uploadExcel(Request $request): Response
    {
        abort_if(Gate::denies('projectActivity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());

            // Read project and FY from Capital sheet (A1 and G1)
            $capitalSheet = $spreadsheet->getSheetByName('पूँजीगत खर्च');
            if (!$capitalSheet) {
                throw new Exception('Capital sheet not found. Expected "पूँजीगत खर्च".');
            }

            $projectName = trim((string) $capitalSheet->getCell('A1')->getValue()); // Changed to getValue()
            $fiscalYearName = trim((string) $capitalSheet->getCell('G1')->getValue()); // Changed to G1 and getValue()

            // Debug logging (remove after testing)
            Log::info('Excel Debug - A1 Raw: ' . $capitalSheet->getCell('A1')->getValue());
            Log::info('Excel Debug - G1 Raw: ' . $capitalSheet->getCell('G1')->getValue());
            Log::info('Excel Debug - A1 Trimmed: ' . $projectName);
            Log::info('Excel Debug - G1 Trimmed: ' . $fiscalYearName);

            if (empty($projectName)) {
                throw new Exception('Project title missing in Excel cell A1 on Capital sheet. Please select a project before downloading the template.');
            }
            if (empty($fiscalYearName)) {
                throw new Exception('Fiscal Year title missing in Excel cell G1 on Capital sheet. Please select a fiscal year before downloading the template.');
            }

            $project = Project::where('title', $projectName)->first();
            if (!$project) {
                throw new Exception("Project '{$projectName}' not found in database (check exact title match).");
            }

            $user = Auth::user();
            if (!$project->users->contains($user->id)) {
                throw new Exception('You do not have access to the selected project.');
            }

            $fiscalYear = FiscalYear::where('title', $fiscalYearName)->first(); // Assumes 'title' field for label
            if (!$fiscalYear) {
                throw new Exception("Fiscal Year '{$fiscalYearName}' not found in database (check exact title match).");
            }

            $projectId = $project->id;
            $fiscalYearId = $fiscalYear->id;

            // Optionally validate same selections in Recurrent sheet
            $recurrentSheet = $spreadsheet->getSheetByName('चालू खर्च');
            if ($recurrentSheet) {
                $recProjectName = trim((string) $recurrentSheet->getCell('A1')->getValue());
                $recFiscalYearName = trim((string) $recurrentSheet->getCell('G1')->getValue());
                if ($recProjectName !== $projectName || $recFiscalYearName !== $fiscalYearName) {
                    throw new Exception('Project or Fiscal Year selections must match across both sheets.');
                }
            }

            // Parse and process Capital (data starts at row 4)
            $capitalData = $this->parseSheet($capitalSheet, 1, 4);
            $this->validateExcelData($capitalData);
            $this->insertHierarchicalData($capitalData, $projectId, $fiscalYearId);

            // Parse and process Recurrent
            if ($recurrentSheet) {
                $recurrentData = $this->parseSheet($recurrentSheet, 2, 4);
                $this->validateExcelData($recurrentData);
                $this->insertHierarchicalData($recurrentData, $projectId, $fiscalYearId);
            }

            DB::commit();

            return redirect()->route('admin.projectActivity.index')
                ->with('success', 'Excel uploaded and activities created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Excel upload failed: ' . $e->getMessage());

            return back()->withErrors(['excel_file' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    private function parseSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $expenditureId, int $startRow = 4): array
    {
        $data = [];
        $index = 0;

        for ($rowNum = $startRow; $rowNum <= 100; $rowNum++) {
            $cellA = $sheet->getCell('A' . $rowNum);
            $hash = trim((string) ($cellA->getCalculatedValue() ?? ''));

            // Skip empty or total row
            if (empty($hash) || trim($hash) === 'कुल जम्मा') {
                continue;
            }

            // Skip if not a valid numeric hierarchy
            $cleanHash = str_replace('.', '', $hash);
            if (!is_numeric($cleanHash)) {
                continue;
            }

            $parts = explode('.', $hash);
            $level = count($parts) - 1;
            $parentHash = $level > 0 ? implode('.', array_slice($parts, 0, -1)) : null;

            $program = trim((string) ($sheet->getCell('B' . $rowNum)->getCalculatedValue() ?? '')); // Cast for safety

            $total_budget = (float) ($sheet->getCell('C' . $rowNum)->getCalculatedValue() ?? 0);
            $total_expense = (float) ($sheet->getCell('D' . $rowNum)->getCalculatedValue() ?? 0);
            $planned_budget = (float) ($sheet->getCell('E' . $rowNum)->getCalculatedValue() ?? 0);
            $q1 = (float) ($sheet->getCell('F' . $rowNum)->getCalculatedValue() ?? 0);
            $q2 = (float) ($sheet->getCell('G' . $rowNum)->getCalculatedValue() ?? 0);
            $q3 = (float) ($sheet->getCell('H' . $rowNum)->getCalculatedValue() ?? 0);
            $q4 = (float) ($sheet->getCell('I' . $rowNum)->getCalculatedValue() ?? 0);

            $data[] = [
                'index' => $index++,
                'hash' => $hash,
                'level' => $level,
                'parent_hash' => $parentHash,
                'program' => $program,
                'total_budget' => $total_budget,
                'total_expense' => $total_expense,
                'planned_budget' => $planned_budget,
                'q1' => $q1,
                'q2' => $q2,
                'q3' => $q3,
                'q4' => $q4,
                'expenditure_id' => $expenditureId,
            ];
        }

        // Sort for tree order
        usort($data, fn(array $a, array $b) => strcmp($a['hash'], $b['hash']));

        return $data;
    }

    private function validateExcelData(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $errors = [];
        $hashToIndex = array_column($data, 'index', 'hash');
        $hashToChildren = []; // Group children by parent hash for efficient sum calc

        // Pre-build children map
        foreach ($data as $row) {
            if ($row['parent_hash']) {
                $hashToChildren[$row['parent_hash']][] = $row;
            }
        }

        foreach ($data as $row) {
            // Quarter sum
            $quarterSum = $row['q1'] + $row['q2'] + $row['q3'] + $row['q4'];
            if (abs($row['planned_budget'] - $quarterSum) > 0.01) {
                $errors[] = "Row #{$row['hash']}: Planned Budget ({$row['planned_budget']}) must equal Q1+Q2+Q3+Q4 ({$quarterSum}).";
            }

            // Non-negative
            $fields = ['total_budget', 'total_expense', 'planned_budget', 'q1', 'q2', 'q3', 'q4'];
            foreach ($fields as $field) {
                if ($row[$field] < 0) {
                    $errors[] = "Row #{$row['hash']}: {$field} cannot be negative ({$row[$field]}).";
                }
            }

            // Required program
            if (empty(trim($row['program']))) {
                $errors[] = "Row #{$row['hash']}: Program name is required.";
            }

            // Parent existence (for non-top-level)
            if ($row['level'] > 0 && !isset($hashToIndex[$row['parent_hash']])) {
                $errors[] = "Row #{$row['hash']}: Invalid parent #{$row['parent_hash']} (not found).";
            }
        }

        // Parent-child sums (only for rows WITH children)
        foreach ($hashToChildren as $parentHash => $children) {
            $parentRow = current(array_filter($data, fn(array $r) => $r['hash'] === $parentHash));
            if (!$parentRow) {
                continue;
            }

            $childrenSum = array_reduce($children, fn(array $carry, array $child) => [
                'total_budget' => $carry['total_budget'] + $child['total_budget'],
                'total_expense' => $carry['total_expense'] + $child['total_expense'],
                'planned_budget' => $carry['planned_budget'] + $child['planned_budget'],
                'q1' => $carry['q1'] + $child['q1'],
                'q2' => $carry['q2'] + $child['q2'],
                'q3' => $carry['q3'] + $child['q3'],
                'q4' => $carry['q4'] + $child['q4'],
            ], ['total_budget' => 0, 'total_expense' => 0, 'planned_budget' => 0, 'q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0]);

            $fields = ['total_budget', 'total_expense', 'planned_budget', 'q1', 'q2', 'q3', 'q4'];
            foreach ($fields as $field) {
                if (abs($parentRow[$field] - $childrenSum[$field]) > 0.01) {
                    $errors[] = "Row #{$parentRow['hash']}: {$field} ({$parentRow[$field]}) must equal sum of children ({$childrenSum[$field]}).";
                }
            }
        }

        // Max depth
        $maxLevel = max(array_column($data, 'level'));
        if ($maxLevel > 2) {
            $errors[] = "Maximum hierarchy depth is 2 (found level {$maxLevel}).";
        }

        if (!empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }
    }

    private function insertHierarchicalData(array $data, int $projectId, int $fiscalYearId): void
    {
        if (empty($data)) {
            return;
        }

        $hashToId = [];
        $expenditureId = $data[0]['expenditure_id'];

        // Create all records and track IDs by hash
        foreach ($data as $row) {
            $activity = ProjectActivity::create([
                'project_id' => $projectId,
                'fiscal_year_id' => $fiscalYearId,
                'expenditure_id' => $expenditureId,
                'parent_id' => null, // Update later
                'program' => $row['program'],
                'total_budget' => $row['total_budget'],
                'total_expense' => $row['total_expense'],
                'planned_budget' => $row['planned_budget'],
                'q1' => $row['q1'],
                'q2' => $row['q2'],
                'q3' => $row['q3'],
                'q4' => $row['q4'],
            ]);

            $hashToId[$row['hash']] = $activity->id;
        }

        // Update parent_ids
        foreach ($data as $row) {
            if ($row['parent_hash'] && isset($hashToId[$row['parent_hash']])) {
                ProjectActivity::where('id', $hashToId[$row['hash']])
                    ->update(['parent_id' => $hashToId[$row['parent_hash']]]);
            }
        }
    }
}
