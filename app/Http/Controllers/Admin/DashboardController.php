<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // $sprint_data = $this->getSprintData(); // New method for sprint data
        $activity_logs = $this->getActivityLogs($user);

        $userProjectIds = in_array(self::ROLE_PROJECT_USER, $roles)
            ? $user->projects()->pluck('id')
            : collect([]);

        if (in_array(self::ROLE_ADMIN, $roles)) {
            $number_blocks = $this->getAdminNumberBlocks();
            // $tasks = $this->getTasks();
            // $project_status = $this->getProjectStatus();
        } elseif (in_array(self::ROLE_DIRECTORATE_USER, $roles)) {
            $number_blocks = $this->getDirectorateNumberBlocks($user);
            $directorateId = $user->directorate_id;
            // $tasks = $directorateId ? $this->getTasks($directorateId) : collect([]);
            // $project_status = $this->getProjectStatus($directorateId);
        } elseif (in_array(self::ROLE_PROJECT_USER, $roles)) {
            $number_blocks = $this->getProjectNumberBlocks($user, $userProjectIds);
            // $tasks = $userProjectIds->isNotEmpty() ? $this->getTasks(null, $userProjectIds) : collect([]);
            // $project_status = $this->getProjectStatus(null, $userProjectIds);
        }

        // Ensure project_status has valid numeric values
        $project_status = array_map(function ($value) {
            return is_numeric($value) ? (int) $value : 0;
        }, $project_status);

        return view('dashboard', compact('number_blocks', 'activity_logs'));
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
