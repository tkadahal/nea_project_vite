<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Directorate;
use App\Models\Task;
use App\Models\Status;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TaskStatus extends Component
{
    public Collection $tasks;

    public ?int $directorateFilter = null;

    public array $availableDirectorates = [];

    public function mount()
    {
        $this->loadAvailableDirectorates();
        $this->updateTasks();
    }

    public function updatedDirectorateFilter()
    {
        $this->updateTasks();
    }

    private function loadAvailableDirectorates()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if (in_array(1, $roles)) {
            $this->availableDirectorates = Directorate::pluck('title', 'id')->toArray();
        } elseif (in_array(3, $roles) && $user->directorate_id) {
            $this->availableDirectorates = Directorate::where('id', $user->directorate_id)
                ->pluck('title', 'id')
                ->toArray();
            $this->directorateFilter = $user->directorate_id;
        }
    }

    private function updateTasks()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        $directorateId = $this->directorateFilter ?? $user->directorate_id;
        $userProjectIds = in_array(4, $roles) ? $user->projects()->pluck('id') : collect([]);

        if (in_array(1, $roles)) {
            $this->tasks = $this->getTasks($this->directorateFilter);
        } elseif (in_array(3, $roles)) {
            $this->tasks = $directorateId ? $this->getTasks($directorateId) : collect([]);
        } elseif (in_array(4, $roles)) {
            $this->tasks = $userProjectIds->isNotEmpty() ? $this->getTasks(null, $userProjectIds) : collect([]);
        }
    }

    private function getTasks(?int $directorateId = null, ?Collection $projectIds = null): Collection
    {
        $query = Task::with(['users', 'projects' => function ($q) use ($directorateId, $projectIds) {
            $q->withPivot('status_id', 'progress')
                ->with(['status' => fn($sq) => $sq->select('id', 'title', 'color')]);
            if ($directorateId) {
                $q->where('directorate_id', $directorateId);
            } elseif ($projectIds) {
                $q->whereIn('id', $projectIds);
            }
        }]);

        $query->whereHas('projects', function ($q) use ($directorateId, $projectIds) {
            if ($directorateId) {
                $q->where('directorate_id', $directorateId);
            } elseif ($projectIds) {
                $q->whereIn('id', $projectIds);
            }
        });

        return $query->latest()->take(5)->get()->flatMap(function ($task) use ($directorateId, $projectIds) {
            $matchingProjects = $task->projects->filter(function ($project) use ($directorateId, $projectIds) {
                if ($directorateId) {
                    return $project->directorate_id == $directorateId;
                } elseif ($projectIds) {
                    return $projectIds->contains($project->id);
                }
                return true;
            });

            return $matchingProjects->map(function ($project) use ($task) {
                $statusId = $project->pivot->status_id ?? 1;
                $status = Status::find($statusId) ?? (object) [
                    'id' => 1,
                    'title' => 'Not Started',
                    'color' => '#DC143C'
                ];

                return (object) [
                    'id' => $task->id,
                    'name' => $task->title ?? 'Unnamed Task',
                    'status' => (object) [
                        'id' => $status->id,
                        'title' => $status->title,
                        'color' => $status->color ?? config('panel.status_colors.' . $status->id, '#DC143C'),
                    ],
                    'assigned_to' => $task->users->isNotEmpty()
                        ? $task->users->map->initials()->implode(', ')
                        : 'Unassigned',
                    'total_time_spent' => $this->calculateTimeSinceCreation($task->created_at),
                    'project_id' => $project->id,
                    'project_name' => $project->title,
                ];
            });
        })->filter(function ($task) {
            return !is_null($task->project_id);
        })->take(5);
    }

    private function calculateTimeSinceCreation($createdAt): string
    {
        $now = now();
        $created = $createdAt instanceof \Carbon\Carbon ? $createdAt : \Carbon\Carbon::parse($createdAt);
        $diffInHours = floor($created->diffInHours($now));
        $diffInMinutes = $created->diffInMinutes($now) % 60;

        return "{$diffInHours} " . 'hr' . " {$diffInMinutes} " . 'min';
    }

    public function render()
    {
        return view('livewire.task-status');
    }
}
