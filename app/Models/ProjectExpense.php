<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasMany,
};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_activity_id',
        'parent_id',
        'user_id',
        'description',
        'effective_date',
        'sub_weight',
        'weighted_progress',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'grand_total' => 'decimal:2',
        'sub_weight' => 'decimal:4',
        'weighted_progress' => 'decimal:4',
    ];

    // Accessor for grand_total (sums quarters)
    public function getGrandTotalAttribute(): float
    {
        return $this->quarters->sum('amount');
    }

    // Hierarchy: Self-referential (for expenses; optional if mirroring activities)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectExpense::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProjectExpense::class, 'parent_id');
    }

    // Key relation to your ProjectActivity
    public function projectActivity(): BelongsTo
    {
        return $this->belongsTo(ProjectActivity::class, 'project_activity_id');
    }

    // Derived: Project and FiscalYear via activity
    public function project(): BelongsTo
    {
        return $this->projectActivity()->withDefault()->project();
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->projectActivity()->withDefault()->fiscalYear();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quarters(): HasMany
    {
        return $this->hasMany(ProjectExpenseQuarter::class);
    }

    // Scoped queries (scoped to activity's project/fiscal)
    public function scopeForProjectActivity(Builder $query, int $activityId): void
    {
        $query->where('project_activity_id', $activityId);
    }

    // Scoped by category (via activity's expenditure_id)
    public function scopeInCategory(Builder $query, int $expenditureId): void // 1=capital, 2=recurrent
    {
        $query->whereHas('projectActivity', fn($q) => $q->where('expenditure_id', $expenditureId));
    }
}
