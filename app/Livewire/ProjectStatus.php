<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Directorate;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProjectStatus extends Component
{
    public array $project_status = ['completed' => 0, 'in_progress' => 0, 'behind' => 0];

    public ?int $directorateFilter = null; // Default: no directorate filter

    public array $availableDirectorates = [];

    public function mount()
    {
        $this->loadAvailableDirectorates();
        $this->updateProjectStatus();
    }

    public function updatedDirectorateFilter()
    {
        $this->updateProjectStatus();
    }

    private function loadAvailableDirectorates()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if (in_array(1, $roles)) {
            // Admin can see all directorates
            $this->availableDirectorates = Directorate::pluck('title', 'id')->toArray();
        } elseif (in_array(3, $roles) && $user->directorate_id) {
            // Directorate user can only see their own directorate
            $this->availableDirectorates = Directorate::where('id', $user->directorate_id)
                ->pluck('title', 'id')
                ->toArray();
            $this->directorateFilter = $user->directorate_id;
        }
    }

    private function updateProjectStatus()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        $directorateId = $this->directorateFilter ?? $user->directorate_id;
        $userProjectIds = in_array(4, $roles) ? $user->projects()->pluck('id') : collect([]);

        if (in_array(1, $roles)) {
            // Admin role
            $this->project_status = $this->getProjectStatus($this->directorateFilter);
        } elseif (in_array(3, $roles)) {
            // Directorate user role
            $this->project_status = $directorateId ? $this->getProjectStatus($directorateId) : $this->project_status;
        } elseif (in_array(4, $roles)) {
            // Project user role
            $this->project_status = $userProjectIds->isNotEmpty() ? $this->getProjectStatus(null, $userProjectIds) : $this->project_status;
        }

        // Ensure project_status has valid numeric values
        $this->project_status = array_map(function ($value) {
            return is_numeric($value) ? (int) $value : 0;
        }, $this->project_status);
    }

    private function getProjectStatus(?int $directorateId = null, ?Collection $projectIds = null): array
    {
        $query = Project::query();
        if ($directorateId) {
            $query->where('directorate_id', $directorateId);
        } elseif ($projectIds) {
            $query->whereIn('id', $projectIds);
        }

        $total = max(1, $query->count());
        Log::debug('Total projects: '.$total);

        $statusCounts = $query->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id')->all();

        $completed = $statusCounts[Status::STATUS_COMPLETED] ?? 0;
        $inProgress = $statusCounts[Status::STATUS_IN_PROGRESS] ?? 0;
        $behind = $statusCounts[Status::STATUS_TODO] ?? 0;

        Log::debug("Completed: $completed, In Progress: $inProgress, Behind: $behind");

        $total = $completed + $inProgress + $behind;
        $total = max(1, $total);

        return [
            'completed' => round(($completed / $total) * 100),
            'in_progress' => round(($inProgress / $total) * 100),
            'behind' => round(($behind / $total) * 100),
        ];
    }

    public function render()
    {
        return view('livewire.project-status');
    }
}
