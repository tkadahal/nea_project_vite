<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use LogsActivity;

    protected $fillable = [
        'directorate_id',
        'title',
        'description',
        'start_date',
        'due_date',
        'completion_date',
        //'status_id',
        'priority_id',
        //'progress',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completion_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        // 'progress' => 'integer',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    // public function status(): BelongsTo
    // {
    //     return $this->belongsTo(Status::class);
    // }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_task')
            ->withPivot('status_id', 'progress')
            ->withTimestamps();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function getEstimatedHoursAttribute(): float
    {
        if (!$this->start_date || !$this->due_date) {
            return 1.0;
        }
        $days = $this->start_date->diffInDays($this->due_date) + 1;
        return $days * 8;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description',
                'start_date',
                'due_date',
                'completion_date',
                'status_id',
                'priority_id',
                'progress',
            ])
            ->logOnlyDirty()
            ->useLogName('task')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user()?->name ?? 'System';

                return match ($eventName) {
                    'created' => "Task created by {$user}",
                    'updated' => "Task updated by {$user}",
                    'deleted' => "Task deleted by {$user}",
                    default => "Task {$eventName} by {$user}",
                };
            });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
}
