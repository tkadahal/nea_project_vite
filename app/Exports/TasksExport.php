<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

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
        ];
    }

    public function map($task): array
    {
        return [
            $task->title,
            $task->projects->pluck('title')->implode(', '),
            $task->status->title,
            $task->priority->title,
            $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
            $task->progress ?? 0,
        ];
    }
}
