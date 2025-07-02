<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBudgetRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'internal_budget',
        'foreign_loan_budget',
        'foreign_subsidy_budget',
        'total_budget',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'internal_budget' => 'decimal:2',
        'foreign_loan_budget' => 'decimal:2',
        'foreign_subsidy_budget' => 'decimal:2',
        'total_budget' => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
}
