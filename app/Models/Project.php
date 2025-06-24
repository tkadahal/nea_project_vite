<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\ModelBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'directorate_id',
        'department_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status_id',
        'priority_id',
        'progress',
        'project_manager',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'budget' => 'decimal:2',
        'progress' => 'float',
    ];

    protected $attributes = [];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(ProjectBudget::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function tasks(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'project_task');
    }

    public function calculatePhysicalProgress(): float
    {
        // Task-based progress
        $tasks = $this->tasks()->get();
        if ($tasks->isNotEmpty()) {
            $totalWeight = $tasks->sum('estimated_hours') ?: $tasks->count();
            if ($totalWeight == 0) {
                return $tasks->avg('progress');
            }
            $weightedProgress = $tasks->sum(fn($task) => $task->progress * $task->estimated_hours);
            return round($weightedProgress / $totalWeight, 2);
        }

        // Contract-based progress (fallback)
        $contracts = $this->contracts()->get();
        if ($contracts->isNotEmpty()) {
            $totalWeight = $contracts->sum('contract_amount') ?: $contracts->count();
            if ($totalWeight == 0) {
                return $contracts->avg('progress');
            }
            $weightedProgress = $contracts->sum(fn($contract) => $contract->progress * $contract->contract_amount);
            return round($weightedProgress / $totalWeight, 2);
        }

        return 0.0;
    }

    public function updatePhysicalProgress(): void
    {
        $this->update(['progress' => $this->calculatePhysicalProgress()]);
    }

    public function getTotalBudgetAttribute(): float
    {
        if (!array_key_exists('total_budget', $this->attributes)) {
            $latestBudget = $this->relationLoaded('budgets')
                ? $this->budgets->sortByDesc('id')->first()
                : $this->budgets()->latest('id')->first();

            $this->attributes['total_budget'] = $latestBudget ? (float) $latestBudget->total_budget : 0.0;
        }

        return $this->attributes['total_budget'];
    }

    // public function getTotalBudgetAttribute(): float
    // {
    //     $latestBudget = $this->relationLoaded('budgets')
    //         ? $this->budgets->sortByDesc('id')->first()
    //         : $this->budgets()->latest('id')->first();

    //     return $latestBudget ? (float) $latestBudget->total_budget : 0.0;
    // }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getFinancialProgressAttribute(): float
    {
        if (!array_key_exists('financial_progress', $this->attributes)) {
            $totalBudget = $this->total_budget;
            if ($totalBudget == 0) {
                $this->attributes['financial_progress'] = 0.0;
            } else {
                $totalExpenses = $this->relationLoaded('expenses')
                    ? $this->expenses->sum('amount')
                    : $this->expenses()->sum('amount');
                $contractExpenses = $this->relationLoaded('contracts')
                    ? $this->contracts->sum('contract_amount')
                    : $this->contracts()->sum('contract_amount');
                $totalSpent = $totalExpenses + $contractExpenses;
                $this->attributes['financial_progress'] = round(($totalSpent / $totalBudget) * 100, 2);
            }
        }

        return $this->attributes['financial_progress'];
    }

    // public function getFinancialProgressAttribute(): float
    // {
    //     $totalBudget = $this->total_budget;
    //     if ($totalBudget == 0) {
    //         return 0.0;
    //     }
    //     $totalExpenses = $this->expenses()->sum('amount');
    //     $contractExpenses = $this->contracts()->sum('contract_amount');
    //     $totalSpent = $totalExpenses + $contractExpenses;
    //     return round(($totalSpent / $totalBudget) * 100, 2);
    // }

    public function scopeFilterByRole(Builder $query, $user)
    {
        if ($user->roles->contains('id', 3)) {
            return $query->where('directorate_id', $user->directorate_id);
        }

        if ($user->roles->contains('id', 4)) {
            return $query->whereIn('id', function ($query) use ($user) {
                $query->select('project_id')
                    ->from('project_user')
                    ->where('user_id', $user->id);
            });
        }

        return $query;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description',
                'status_id',
                'project_manager',
                'start_date',
                'end_date',
            ])
            ->logOnlyDirty()
            ->useLogName('project')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user()?->name ?? 'System';

                return match ($eventName) {
                    'created' => "Project created by {$user}",
                    'updated' => "Project updated by {$user}",
                    'deleted' => "Project deleted by {$user}",
                    default => "Project {$eventName} by {$user}",
                };
            });
    }

    public function newEloquentBuilder($query): ModelBuilder
    {
        return new ModelBuilder($query);
    }
}
