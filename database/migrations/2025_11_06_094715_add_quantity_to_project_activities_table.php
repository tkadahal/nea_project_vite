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
            $table->decimal('total_quantity', 10, 2)->after('total_budget');
            $table->decimal('completed_quantity', 10, 2)->after('total_expense');
            $table->decimal('planned_quantity', 10, 2)->after('planned_budget');
            $table->decimal('q1_quantity', 10, 2)->after('q1');
            $table->decimal('q2_quantity', 10, 2)->after('q2');
            $table->decimal('q3_quantity', 10, 2)->after('q3');
            $table->decimal('q4_quantity', 10, 2)->after('q4');
        });
    }
};
