<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    // Role ID constants for better maintainability
    private const ROLE_ADMIN = 1;

    private const ROLE_DIRECTORATE_USER = 3;

    private const ROLE_PROJECT_USER = 4;

    /**
     * Handle the incoming request.
     */
    public function index(): View
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        $number_blocks = [];
        $tasks = collect([]);
        $project_status = ['completed' => 0, 'in_progress' => 0, 'behind' => 0];
        $sprint_data = $this->getSprintData(); // New method for sprint data
        $activity_logs = $this->getActivityLogs($user);

        $userProjectIds = in_array(self::ROLE_PROJECT_USER, $roles)
            ? $user->projects()->pluck('id')
            : collect([]);

        if (in_array(self::ROLE_ADMIN, $roles)) {
            $number_blocks = $this->getAdminNumberBlocks();
            $tasks = $this->getTasks();
            $project_status = $this->getProjectStatus();
        } elseif (in_array(self::ROLE_DIRECTORATE_USER, $roles)) {
            $number_blocks = $this->getDirectorateNumberBlocks($user);
            $directorateId = $user->directorate_id;
            $tasks = $directorateId ? $this->getTasks($directorateId) : collect([]);
            $project_status = $this->getProjectStatus($directorateId);
        } elseif (in_array(self::ROLE_PROJECT_USER, $roles)) {
            $number_blocks = $this->getProjectNumberBlocks($user, $userProjectIds);
            $tasks = $userProjectIds->isNotEmpty() ? $this->getTasks(null, $userProjectIds) : collect([]);
            $project_status = $this->getProjectStatus(null, $userProjectIds);
        }

        // Ensure project_status has valid numeric values
        $project_status = array_map(function ($value) {
            return is_numeric($value) ? (int) $value : 0;
        }, $project_status);

        return view('dashboard', compact('number_blocks', 'tasks', 'project_status', 'sprint_data', 'activity_logs'));
    }

    /**
     * Get number blocks for Admin role.
     */
    private function getAdminNumberBlocks(): array
    {
        return [
            ['title' => trans('global.user.title'), 'number' => User::count(), 'url' => route('admin.user.index')],
            ['title' => trans('global.project.title'), 'number' => Project::count(), 'url' => route('admin.project.index')],
            ['title' => trans('global.contract.title'), 'number' => Contract::count(), 'url' => route('admin.contract.index')],
            ['title' => trans('global.task.title'), 'number' => Task::count(), 'url' => route('admin.task.index')],
        ];
    }

    /**
     * Get number blocks for Directorate User role.
     */
    private function getDirectorateNumberBlocks(User $user): array
    {
        $directorateId = $user->directorate_id;

        if (! $directorateId) {
            return [
                ['title' => trans('global.user.title'), 'number' => 0, 'url' => route('admin.user.index')],
                ['title' => trans('global.project.title'), 'number' => 0, 'url' => route('admin.project.index')],
                ['title' => trans('global.contract.title'), 'number' => 0, 'url' => route('admin.contract.index')],
                ['title' => trans('global.task.title'), 'number' => 0, 'url' => route('admin.task.index')],
            ];
        }

        return [
            ['title' => trans('global.user.title'), 'number' => User::where('directorate_id', $directorateId)->count(), 'url' => route('admin.user.index')],
            ['title' => trans('global.project.title'), 'number' => Project::where('directorate_id', $directorateId)->count(), 'url' => route('admin.project.index')],
            ['title' => trans('global.contract.title'), 'number' => Contract::whereHas('project', fn($q) => $q->where('directorate_id', $directorateId))->count(), 'url' => route('admin.contract.index')],
            ['title' => trans('global.task.title'), 'number' => Task::whereHas('projects', fn($q) => $q->where('directorate_id', $directorateId))->count(), 'url' => route('admin.task.index')],
        ];
    }

    /**
     * Get number blocks for Project User role.
     */
    private function getProjectNumberBlocks(User $user, Collection $projectIds): array
    {
        $distinctUserCount = $projectIds->isEmpty()
            ? 0
            : DB::table('project_user')
            ->whereIn('project_id', $projectIds)
            ->distinct('user_id')
            ->count('user_id');

        if ($projectIds->isEmpty()) {
            return [
                ['title' => trans('global.user.title'), 'number' => $distinctUserCount, 'url' => route('admin.user.index')],
                ['title' => trans('global.project.title'), 'number' => 0, 'url' => route('admin.project.index')],
                ['title' => trans('global.contract.title'), 'number' => 0, 'url' => route('admin.contract.index')],
                ['title' => trans('global.task.title'), 'number' => 0, 'url' => route('admin.task.index')],
            ];
        }

        return [
            ['title' => trans('global.user.title'), 'number' => $distinctUserCount, 'url' => route('admin.user.index')],
            ['title' => trans('global.project.title'), 'number' => $projectIds->count(), 'url' => route('admin.project.index')],
            ['title' => trans('global.contract.title'), 'number' => Contract::whereIn('project_id', $projectIds)->count(), 'url' => route('admin.contract.index')],
            ['title' => trans('global.task.title'), 'number' => Task::whereHas('projects', fn($q) => $q->whereIn('id', $projectIds))->count(), 'url' => route('admin.task.index')],
        ];
    }

    /**
     * Get tasks data for the dashboard.
     */
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

    /**
     * Get project status data.
     */
    private function getProjectStatus(?int $directorateId = null, ?Collection $projectIds = null): array
    {
        $query = Project::query();
        if ($directorateId) {
            $query->where('directorate_id', $directorateId);
        } elseif ($projectIds) {
            $query->whereIn('id', $projectIds);
        }

        // Get total projects
        $total = max(1, $query->count());
        Log::debug('Total projects: ' . $total);

        // Count projects by status_id in a single query
        $statusCounts = $query->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id')->all();

        $completed = $statusCounts[Status::STATUS_COMPLETED] ?? 0;
        $inProgress = $statusCounts[Status::STATUS_IN_PROGRESS] ?? 0;
        $behind = $statusCounts[Status::STATUS_TODO] ?? 0;

        Log::debug("Completed: $completed, In Progress: $inProgress, Behind: $behind");

        // Ensure total matches the sum of counted statuses (should equal original total)
        $total = $completed + $inProgress + $behind;
        $total = max(1, $total); // Avoid division by zero

        $completedPercent = round(($completed / $total) * 100);
        $inProgressPercent = round(($inProgress / $total) * 100);
        $behindPercent = round(($behind / $total) * 100);

        return [
            'completed' => $completedPercent,
            'in_progress' => $inProgressPercent,
            'behind' => $behindPercent,
        ];
    }

    /**
     * Get sprint data for task overview (3 months per sprint), counting tasks by status.
     */
    private function getSprintData(): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths(71); // 24 sprints × 3 months = 72 months
        $sprints = [];

        // Use TO_CHAR for PostgreSQL date formatting and group by status
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

                // Map status IDs to categories (adjust status IDs based on your Status model)
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

    /**
     * Get activity logs for the authenticated user.
     */
    private function getActivityLogs(User $user): Collection
    {
        return Activity::where('causer_type', 'App\Models\User')
            ->where('causer_id', $user->id ?? 1)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($activity) {
                return (object) [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Generate random time spent for placeholder data.
     */
    private function generateRandomTime(): string
    {
        $hours = rand(1, 100);
        $minutes = rand(0, 59);

        return "{$hours}h {$minutes}min";
    }
}
