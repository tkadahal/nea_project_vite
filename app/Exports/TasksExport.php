<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Status;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;

class TasksExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Task',
            'Project',
            'Status',
            'Priority',
            'Due Date',
            'Progress (%)',
            'Users',
        ];
    }

    public function map($task): array
    {
        try {
            // Load status from project_task.status_id
            $status = Status::find($task->status_id);

            return [
                $task->title ?? 'N/A',
                $task->projects->isNotEmpty() ? $task->projects->pluck('title')->implode(', ') : 'N/A',
                $status ? $status->title : 'N/A',
                $task->priority ? $task->priority->title : 'N/A',
                $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                $task->progress ?? 0,
                $task->users->isNotEmpty() ? $task->users->pluck('name')->implode(', ') : 'No Users',
            ];
        } catch (\Exception $e) {
            return [
                $task->title ?? 'N/A',
                'Error',
                'Error',
                'Error',
                'N/A',
                0,
                'No Users',
            ];
        }
    }
}
