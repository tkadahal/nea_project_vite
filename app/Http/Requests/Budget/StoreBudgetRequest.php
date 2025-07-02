<?php

declare(strict_types=1);

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('budget_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'fiscal_year_id' => 'required|integer|exists:fiscal_years,id',
            'internal_budget' => 'required|numeric|min:0',
            'foreign_loan_budget' => 'required|numeric|min:0',
            'foreign_subsidy_budget' => 'required|numeric|min:0',
            'total_budget' => 'required|numeric|min:0',
        ];
    }
}
