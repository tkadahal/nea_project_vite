<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->constrained()->onDelete('cascade');
            $table->integer('expenditure_id'); // can be only two: 1 for capital, 2 for recurrent
            $table->string('program');
            $table->decimal('total_budget', 10, 2);
            $table->decimal('total_expense', 15, 2);
            $table->decimal('planned_budget', 10, 2);
            $table->decimal('q1', 10, 2);
            $table->decimal('q2', 10, 2);
            $table->decimal('q3', 10, 2);
            $table->decimal('q4', 10, 2);

            $table->foreignId('parent_id')->nullable()->constrained('project_activities')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();


            $table->index(['deleted_at', 'project_id', 'fiscal_year_id', 'expenditure_id'], 'idx_project_activities');
        });
    }
};
