<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('project_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        return [
            'directorate_id' => ['required', 'exists:directorates,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status_id' => ['required', 'exists:statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'project_manager' => ['nullable', 'exists:users,id'],
            'budgets' => ['required', 'array', 'min:1'],
            'budgets.*.fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'budgets.*.total_budget' => ['required', 'numeric', 'min:0'],
            'budgets.*.internal_budget' => ['required', 'numeric', 'min:0'],
            'budgets.*.foreign_loan_budget' => ['required', 'numeric', 'min:0'],
            'budgets.*.foreign_subsidy_budget' => ['required', 'numeric', 'min:0'],
        ];
    }
}
