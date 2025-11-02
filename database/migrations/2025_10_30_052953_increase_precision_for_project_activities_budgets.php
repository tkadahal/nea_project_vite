<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_activities', function (Blueprint $table) {
            $table->decimal('total_budget', 15, 2)->change();
            $table->decimal('total_expense', 15, 2)->change();
            $table->decimal('planned_budget', 15, 2)->change();
            $table->decimal('q1', 15, 2)->change();
            $table->decimal('q2', 15, 2)->change();
            $table->decimal('q3', 15, 2)->change();
            $table->decimal('q4', 15, 2)->change();
        });
    }
};
