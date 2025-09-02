<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use App\Models\ProjectActivity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProjectActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();
        $fiscalYears = FiscalYear::all();

        $project = Project::find(1);

        $capitalActivities = $project->projectActivities()->where('expenditure_id', 1)->get();
        $recurrentActivities = $project->projectActivities()->where('expenditure_id', 2)->get();
        return view('admin.project-activities.create', compact('projects', 'fiscalYears', 'capitalActivities', 'recurrentActivities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $project = Project::find(1);
        try {
            $capitalActivities = $request->input('capital', []);
            $recurrentActivities = $request->input('recurrent', []);

            $this->validateActivities($capitalActivities, 'capital');
            $this->validateActivities($recurrentActivities, 'recurrent');

            foreach ($capitalActivities as $activityData) {
                ProjectActivity::create([
                    'project_id' => $project->id,
                    'expenditure_id' => 1,
                    'parent_id' => $activityData['parent_id'] ?? null,
                    'programs' => $activityData['programs'],
                    'unit' => $activityData['unit'],
                    'total_quantity' => $activityData['total_quantity'],
                    'total_cost' => $activityData['total_cost'],
                    'weight_percentage' => $activityData['weight_percentage'],
                    'description' => $activityData['description'] ?? null,
                ]);
            }

            foreach ($recurrentActivities as $activityData) {
                ProjectActivity::create([
                    'project_id' => $project->id,
                    'expenditure_id' => 2,
                    'parent_id' => $activityData['parent_id'] ?? null,
                    'programs' => $activityData['programs'],
                    'unit' => $activityData['unit'],
                    'total_quantity' => $activityData['total_quantity'],
                    'total_cost' => $activityData['total_cost'],
                    'weight_percentage' => $activityData['weight_percentage'],
                    'description' => $activityData['description'] ?? null,
                ]);
            }

            return redirect()->route('admin.project.show', $project)
                ->with('success', 'Activities created successfully.');
        } catch (\Exception $e) {

            return redirect()->back()
                ->withErrors(['message' => 'Failed to create activities. Please try again.'])
                ->withInput();
        }
    }

    protected function validateActivities(array $activities, string $type)
    {
        foreach ($activities as $index => $activity) {
            $validator = Validator::make($activity, [
                'programs' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:project_activities,id',
                'unit' => 'required|string|max:50',
                'total_quantity' => 'required|numeric|min:0',
                'total_cost' => 'required|numeric|min:0',
                'weight_percentage' => 'required|numeric|min:0|max:100',
                'description' => 'nullable|string',
            ], [], [
                'programs' => trans('global.activity.fields.programs'),
                'parent_id' => trans('global.activity.fields.parent_program'),
                'unit' => trans('global.activity.fields.unit'),
                'total_quantity' => trans('global.activity.fields.total_quantity'),
                'total_cost' => trans('global.activity.fields.total_cost'),
                'weight_percentage' => trans('global.activity.fields.weight_percentage'),
                'description' => trans('global.activity.fields.description'),
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                throw new \Illuminate\Validation\ValidationException($validator, redirect()->back()->withErrors([
                    "{$type}.{$index}" => $errors,
                ])->withInput());
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectActivity $projectActivity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectActivity $projectActivity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectActivity $projectActivity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectActivity $projectActivity)
    {
        //
    }
}
