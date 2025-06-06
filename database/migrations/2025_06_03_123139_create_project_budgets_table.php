<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->index()->constrained();
            $table->foreignId('fiscal_year_id')->index()->constrained();
            $table->decimal('total_budget', 15, 2)->nullable();
            $table->decimal('internal_budget', 15, 2)->nullable();
            $table->decimal('foreign_loan_budget', 15, 2)->nullable();
            $table->decimal('foreign_subsidy_budget', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
