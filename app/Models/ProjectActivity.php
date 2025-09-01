<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'expenditure_id',
        'programs',
        'unit',
        'total_quantity',
        'total_cost',
        'weight_percentage',
        'description',
        'parent_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectActivity::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProjectActivity::class, 'parent_id');
    }
}
