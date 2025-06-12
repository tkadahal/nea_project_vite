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
    public ?int $directorateFilter = null; // Default: no directorate filter
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

    private function updateTasks()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();
        $directorateId = $this->directorateFilter ?? $user->directorate_id;
        $userProjectIds = in_array(4, $roles) ? $user->projects()->pluck('id') : collect([]);

        if (in_array(1, $roles)) {
            // Admin role
            $this->tasks = $this->getTasks($this->directorateFilter);
        } elseif (in_array(3, $roles)) {
            // Directorate user role
            $this->tasks = $directorateId ? $this->getTasks($directorateId) : collect([]);
        } elseif (in_array(4, $roles)) {
            // Project user role
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
                'assigned_to' => $task->users->first()?->name ?? 'Unassigned',
                'total_time_spent' => $this->generateRandomTime(),
            ];
        });
    }

    private function generateRandomTime(): string
    {
        $hours = rand(1, 100);
        $minutes = rand(0, 59);
        return "{$hours}h {$minutes}min";
    }

    public function render()
    {
        return view('livewire.task-status');
    }
}
