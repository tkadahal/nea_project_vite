<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'fiscal_year_id',
        'expenditure_id',
        'program',
        'total_budget',
        'total_expense',
        'planned_budget',
        'q1',
        'q2',
        'q3',
        'q4',
        'parent_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'total_budget' => 'float',
        'total_expense' => 'float',
        'planned_budget' => 'float',
        'q1' => 'float',
        'q2' => 'float',
        'q3' => 'float',
        'q4' => 'float',
    ];

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
}
