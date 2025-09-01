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
            $table->integer('expenditure_id'); // can be only two: 1 for capital, 2 for recurrent
            $table->string('unit');
            $table->decimal('total_quantity', 10, 2);
            $table->decimal('total_cost', 15, 2);
            $table->decimal('weight_percentage', 10, 2);
            $table->text('description')->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('project_activities')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();


            $table->index(['deleted_at', 'project_id', 'expenditure_id'], 'idx_project_activities');
        });
    }
};
