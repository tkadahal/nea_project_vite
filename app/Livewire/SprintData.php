<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Status;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SprintData extends Component
{
    public array $sprint_data = [];

    public function mount()
    {
        $this->sprint_data = $this->getSprintData();
    }

    private function getSprintData(): array
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        $directorateId = $user->directorate_id;
        $projectIds = in_array(4, $roles) ? $user->projects()->pluck('id') : collect([]);

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths(71);
        $sprints = [];

        $query = Task::query()
            ->leftJoin('project_task', 'tasks.id', '=', 'project_task.task_id')
            ->select(
                DB::raw("TO_CHAR(tasks.created_at, 'YYYY-MM-01') as month_start"),
                DB::raw('COALESCE(project_task.status_id, tasks.status_id) as status_id'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('tasks.created_at', [$startDate, $endDate]);

        if (in_array(3, $roles) && $directorateId) {
            // Directorate User: Include tasks in directorate (with or without project/department)
            $query->where(function ($q) use ($directorateId, $projectIds) {
                $q->where('tasks.directorate_id', $directorateId)
                    ->orWhereHas('projects', fn($q) => $q->where('projects.directorate_id', $directorateId));
            });
        } elseif (in_array(4, $roles) && $projectIds->isNotEmpty()) {
            // Project User: Include tasks in assigned projects
            $query->where(function ($q) use ($projectIds) {
                $q->whereIn('project_task.project_id', $projectIds);
            });
        } elseif (in_array(1, $roles)) {
            // Superadmin: Include all tasks
            // No additional filters needed
        } else {
            // Default: Limit to tasks user can access (e.g., assigned tasks)
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        }

        $tasksByMonthAndStatus = $query->groupByRaw("TO_CHAR(tasks.created_at, 'YYYY-MM-01'), COALESCE(project_task.status_id, tasks.status_id)")
            ->get()
            ->reduce(function ($carry, $item) {
                $carry[$item->month_start][$item->status_id] = $item->count;
                return $carry;
            }, []);

        for ($i = 0; $i < 24; $i++) {
            $sprintStart = $startDate->copy()->addMonths($i * 3);
            $sprintEnd = $sprintStart->copy()->addMonths(2);

            $sprintData = [
                'todo' => 0,
                'in_progress' => 0,
                'completed' => 0,
            ];

            for ($month = 0; $month < 3; $month++) {
                $monthDate = $sprintStart->copy()->addMonths($month)->format('Y-m-01');
                $monthData = $tasksByMonthAndStatus[$monthDate] ?? [];

                foreach ($monthData as $statusId => $count) {
                    if ($statusId == Status::STATUS_TODO) {
                        $sprintData['todo'] += $count;
                    } elseif ($statusId == Status::STATUS_IN_PROGRESS) {
                        $sprintData['in_progress'] += $count;
                    } elseif ($statusId == Status::STATUS_COMPLETED) {
                        $sprintData['completed'] += $count;
                    }
                }
            }

            $sprints['Sprint ' . ($i + 1)] = $sprintData;
        }

        return $sprints;
    }

    public function render()
    {
        return view('livewire.sprint-data');
    }
}
