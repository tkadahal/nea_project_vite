<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function getByDirectorate($directorateId)
    {
        try {
            $projects = Project::where('directorate_id', $directorateId)
                ->whereNull('deleted_at') // Ensure only non-deleted projects
                ->get()
                ->map(function ($project) {
                    return [
                        'value' => (string) $project->id,
                        'label' => $project->title,
                    ];
                })
                ->toArray();

            return response()->json($projects);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch projects: '.$e->getMessage(),
            ], 500);
        }
    }
}
