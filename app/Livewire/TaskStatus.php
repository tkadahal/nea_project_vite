<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Directorate;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        Log::debug('Directorate filter updated to: ' . $this->directorateFilter);
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
        $query = Task::with(['status', 'users']);
        if ($directorateId) {
            $query->whereHas('projects', fn($q) => $q->where('directorate_id', $directorateId));
        } elseif ($projectIds) {
            $query->whereHas('projects', fn($q) => $q->whereIn('id', $projectIds));
        }

        return $query->latest()->take(5)->get()->map(function ($task) {
            return (object) [
                'id' => $task->id,
                'name' => $task->title ?? 'Unnamed Task',
                'status' => $task->status,
                'assigned_to' => $task->users->isNotEmpty()
                    ? $task->users->map->initials()->implode(', ')
                    : 'Unassigned',
                'total_time_spent' => $this->calculateTimeSinceCreation($task->created_at),
            ];
        });
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
