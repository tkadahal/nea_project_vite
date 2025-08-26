<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Directorate;
use App\Models\Task;
use App\Models\Status;
use App\Models\Department;
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
            $this->tasks = $this->getTasks($this->directorateFilter, null, $userProjectIds);
        } elseif (in_array(3, $roles)) {
            $this->tasks = $directorateId ? $this->getTasks($directorateId, null, $userProjectIds) : collect([]);
        } elseif (in_array(4, $roles)) {
            $this->tasks = $userProjectIds->isNotEmpty() ? $this->getTasks(null, null, $userProjectIds) : collect([]);
        }
    }

    private function getTasks(?int $directorateId = null, ?int $departmentId = null, ?Collection $projectIds = null): Collection
    {
        $query = Task::with([
            'users',
            'projects' => function ($q) {
                $q->withPivot('status_id', 'progress')
                    ->with(['status' => fn($sq) => $sq->select('id', 'title', 'color'), 'directorate', 'department']);
            },
            'directorate',
            'department',
            'subTasks' => function ($q) {
                $q->with([
                    'users',
                    'status' => fn($sq) => $sq->select('id', 'title', 'color'),
                    'directorate',
                    'department',
                    'projects' => fn($pq) => $pq->select('id', 'title', 'directorate_id', 'department_id')->withPivot('status_id', 'progress')
                ]);
            }
        ])->whereNull('parent_id');

        // Apply filters based on directorate, department, or project
        $query->where(function ($q) use ($directorateId, $departmentId, $projectIds) {
            if ($directorateId) {
                $q->where('directorate_id', $directorateId);
            }
            if ($departmentId) {
                $q->orWhere('department_id', $departmentId);
            }
            if ($projectIds && $projectIds->isNotEmpty()) {
                $q->orWhereHas('projects', function ($pq) use ($projectIds) {
                    $pq->whereIn('id', $projectIds);
                });
            }
        });

        return $query->latest()->take(5)->get()->map(function ($task) {
            $status = $task->projects->isNotEmpty()
                ? ($task->projects->first()->pivot->status ?? Status::find(1) ?? (object) [
                    'id' => 1,
                    'title' => 'Not Started',
                    'color' => '#DC143C'
                ])
                : ($task->status ?? (object) [
                    'id' => 1,
                    'title' => 'Not Started',
                    'color' => '#DC143C'
                ]);

            $subTasks = $task->subTasks->map(function ($subTask) {
                $subStatus = $subTask->projects->isNotEmpty()
                    ? ($subTask->projects->first()->pivot->status ?? $subTask->status ?? (object) [
                        'id' => 1,
                        'title' => 'Not Started',
                        'color' => '#DC143C'
                    ])
                    : ($subTask->status ?? (object) [
                        'id' => 1,
                        'title' => 'Not Started',
                        'color' => '#DC143C'
                    ]);

                return (object) [
                    'id' => $subTask->id,
                    'name' => $subTask->title ?? 'Unnamed Sub-task',
                    'status' => (object) [
                        'id' => $subStatus->id,
                        'title' => $subStatus->title,
                        'color' => $subStatus->color ?? '#DC143C',
                    ],
                    'assigned_to' => $subTask->users->isNotEmpty()
                        ? $subTask->users->map->initials()->implode(', ')
                        : 'Unassigned',
                    'total_time_spent' => $this->calculateTimeSinceCreation($subTask->created_at),
                    'project_id' => $subTask->projects->isNotEmpty() ? $subTask->projects->first()->id : null,
                    'project_name' => $subTask->projects->isNotEmpty() ? $subTask->projects->first()->title : null,
                    'directorate_id' => $subTask->directorate_id,
                    'directorate_name' => $subTask->directorate ? $subTask->directorate->title : null,
                    'department_id' => $subTask->department_id,
                    'department_name' => $subTask->department ? $subTask->department->title : null,
                ];
            });

            return (object) [
                'id' => $task->id,
                'name' => $task->title ?? 'Unnamed Task',
                'status' => (object) [
                    'id' => $status->id,
                    'title' => $status->title,
                    'color' => $status->color ?? '#DC143C',
                ],
                'assigned_to' => $task->users->isNotEmpty()
                    ? $task->users->map->initials()->implode(', ')
                    : 'Unassigned',
                'total_time_spent' => $this->calculateTimeSinceCreation($task->created_at),
                'project_id' => $task->projects->isNotEmpty() ? $task->projects->first()->id : null,
                'project_name' => $task->projects->isNotEmpty() ? $task->projects->first()->title : null,
                'directorate_id' => $task->directorate_id,
                'directorate_name' => $task->directorate ? $task->directorate->title : null,
                'department_id' => $task->department_id,
                'department_name' => $task->department ? $task->department->title : null,
                'sub_tasks' => $subTasks,
            ];
        })->filter(function ($task) {
            return !is_null($task->project_id) || !is_null($task->directorate_id) || !is_null($task->department_id);
        })->take(5);
    }

    private function calculateTimeSinceCreation($createdAt): string
    {
        $now = now();
        $created = $createdAt instanceof \Carbon\Carbon ? $createdAt : \Carbon\Carbon::parse($createdAt);
        $diffInHours = floor($created->diffInHours($now));
        $diffInMinutes = $created->diffInMinutes($now) % 60;

        return "{$diffInHours} hr {$diffInMinutes} min";
    }

    public function render()
    {
        return view('livewire.task-status');
    }
}
