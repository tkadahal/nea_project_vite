<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Status;
use App\Models\Task;
use Carbon\Carbon;
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
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths(71);
        $sprints = [];

        $query = Task::select(
            DB::raw("TO_CHAR(created_at, 'YYYY-MM-01') as month_start"),
            DB::raw('status_id'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-01')"), 'status_id');

        $tasksByMonthAndStatus = $query->get()->reduce(function ($carry, $item) {
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
