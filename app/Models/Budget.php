<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\ModelBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'project_id',
        'fiscal_year_id',
        'total_budget',
        'internal_budget',
        'foreign_loan_budget',
        'foreign_subsidy_budget',
        'budget_revision',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'internal_budget' => 'decimal:2',
        'foreign_loan_budget' => 'decimal:2',
        'foreign_subsidy_budget' => 'decimal:2',
        'budget_revision' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ProjectBudgetRevision::class);
    }

    // public function getRemainingBudgetAttribute(): float
    // {
    //     $totalSpent = $this->project->expenses()
    //         ->where('fiscal_year_id', $this->fiscal_year_id)
    //         ->whereIn('budget_type', ['internal', 'foreign_loan', 'foreign_subsidy'])
    //         ->sum('amount');

    //     return max(0, (float) $this->total_budget - (float) $totalSpent);
    // }

    public static function getCumulativeBudget(Project $project, FiscalYear $fiscalYear): float
    {
        $previousBudgets = $project->budgets()
            ->whereHas('fiscalYear', fn($query) => $query->where('start_date', '<=', $fiscalYear->start_date))
            ->get();

        $cumulative = 0.0;
        foreach ($previousBudgets as $budget) {
            $cumulative += $budget->remaining_budget;
        }

        return round($cumulative, 2);
    }

    public function newEloquentBuilder($query): ModelBuilder
    {
        return new ModelBuilder($query);
    }
}
