<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectActivity extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'project_id',
        'fiscal_year_id',
        'expenditure_id',
        'program',
        'total_budget',
        'total_quantity',
        'total_expense',
        'completed_quantity',
        'planned_budget',
        'planned_quantity',
        'q1',
        'q1_quantity',
        'q2',
        'q2_quantity',
        'q3',
        'q3_quantity',
        'q4',
        'q4_quantity',
        'parent_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'total_budget' => 'float',
        'total_quantity' => 'float',
        'total_expense' => 'float',
        'completed_quantity' => 'float',
        'planned_budget' => 'float',
        'planned_quantity' => 'float',
        'q1' => 'float',
        'q1_quantity' => 'float',
        'q2' => 'float',
        'q2_quantity' => 'float',
        'q3' => 'float',
        'q3_quantity' => 'float',
        'q4' => 'float',
        'q4_quantity' => 'float',
    ];

    /**
     * Get the total budget sum (x) for root nodes (parent_id null) within a specific fiscal year.
     * Only for capital expenditure (expenditure_id = 1).
     */
    public static function getRootTotalBudget(int $fiscalYearId): float
    {
        return (float) static::where('fiscal_year_id', $fiscalYearId)
            ->whereNull('parent_id')
            ->where('expenditure_id', 1)
            ->sum('total_budget');
    }

    /**
     * Get the weighted average (proportion) for total_budget.
     */
    public function getVarTotalBudgetAttribute(): float
    {
        $x = static::getRootTotalBudget($this->fiscal_year_id ?? 0);
        return $x > 0 ? $this->total_budget / $x : 0.0;
    }

    /**
     * Get the percentage for total_expense based on quantity completion.
     */
    public function getVarTotalExpenseAttribute(): float
    {
        return $this->total_quantity > 0 ? ($this->completed_quantity / $this->total_quantity) * $this->var_total_budget : 0.0;
    }

    /**
     * Get the percentage for planned_budget based on planned quantity.
     */
    public function getVarPlannedBudgetAttribute(): float
    {
        return $this->total_quantity > 0 ? ($this->planned_quantity / $this->total_quantity) * $this->var_total_budget : 0.0;
    }

    /**
     * Get the percentage for q1 based on q1 quantity.
     */
    public function getVarQ1Attribute(): float
    {
        return $this->total_quantity > 0 ? ($this->q1_quantity / $this->total_quantity) * $this->var_total_budget : 0.0;
    }

    /**
     * Get the percentage for q2 based on q2 quantity.
     */
    public function getVarQ2Attribute(): float
    {
        return $this->total_quantity > 0 ? ($this->q2_quantity / $this->total_quantity) * $this->var_total_budget : 0.0;
    }

    /**
     * Get the percentage for q3 based on q3 quantity.
     */
    public function getVarQ3Attribute(): float
    {
        return $this->total_quantity > 0 ? ($this->q3_quantity / $this->total_quantity) * $this->var_total_budget : 0.0;
    }

    /**
     * Get the percentage for q4 based on q4 quantity.
     */
    public function getVarQ4Attribute(): float
    {
        return $this->total_quantity > 0 ? ($this->q4_quantity / $this->total_quantity) * $this->var_total_budget : 0.0;
    }

    /**
     * Get all weighted averages (vars) as an array for the current row.
     */
    public function getVarsAttribute(): array
    {
        return [
            'var_total_budget' => $this->var_total_budget,
            'var_total_expense' => $this->var_total_expense,
            'var_planned_budget' => $this->var_planned_budget,
            'var_q1' => $this->var_q1,
            'var_q2' => $this->var_q2,
            'var_q3' => $this->var_q3,
            'var_q4' => $this->var_q4,
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectActivity::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProjectActivity::class, 'parent_id');
    }

    /**
     * Expenses relation: One activity can have multiple expenses.
     */
    public function expenses()
    {
        return $this->hasMany(ProjectExpense::class, 'project_activity_id');
    }

    /**
     * Recursively get all descendants (children + grandchildren) for a node.
     */
    public function getDescendants(): Collection
    {
        $descendants = new Collection();
        $this->loadDescendants($descendants);
        return $descendants;
    }

    private function loadDescendants(Collection $descendants): void
    {
        foreach ($this->children as $child) {
            $descendants->push($child);
            $child->loadDescendants($descendants);
        }
    }

    /**
     * Sum a field (e.g., 'total_budget') for this node + all descendants.
     */
    public function getSubtreeSum(string $field): float
    {
        $sum = $this->{$field} ?? 0.0;
        foreach ($this->getDescendants() as $descendant) {
            $sum += $descendant->{$field} ?? 0.0;
        }
        return $sum;
    }

    /**
     * Sum quarters for subtree (e.g., total Q1 under this heading).
     */
    public function getSubtreeQuarterSum(string $quarter): float // e.g., 'q1'
    {
        return $this->getSubtreeSum($quarter);
    }

    /**
     * Calculate depth of this node in the hierarchy.
     */
    public function getDepthAttribute(): int
    {
        return $this->calculateDepth();
    }

    private function calculateDepth(): int
    {
        $depth = 0;
        $current = $this;
        while ($current->parent_id) {
            $current = $current->parent;
            if (!$current) break;
            $depth++;
        }
        return $depth;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('projectActivity')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user()?->name ?? 'System';
                return "Project Activity {$eventName} by {$user}";
            });
    }
}
