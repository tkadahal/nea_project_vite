<?php

declare(strict_types=1);

namespace App\Http\Requests\ProjectExpense;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreProjectExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('projectActivity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            // Capital section validation
            'capital' => ['array'],
            'capital.*.activity_id' => 'required|exists:project_activities,id',
            'capital.*.parent_id' => 'nullable|integer', // parent_id is activity id, not expense
            'capital.*.q1_qty' => 'nullable|numeric|min:0',
            'capital.*.q1_amt' => 'nullable|numeric|min:0',
            'capital.*.q2_qty' => 'nullable|numeric|min:0',
            'capital.*.q2_amt' => 'nullable|numeric|min:0',
            'capital.*.q3_qty' => 'nullable|numeric|min:0',
            'capital.*.q3_amt' => 'nullable|numeric|min:0',
            'capital.*.q4_qty' => 'nullable|numeric|min:0',
            'capital.*.q4_amt' => 'nullable|numeric|min:0',
            // Recurrent section validation
            'recurrent' => ['array'],
            'recurrent.*.activity_id' => 'required|exists:project_activities,id',
            'recurrent.*.parent_id' => 'nullable|integer',
            'recurrent.*.q1_qty' => 'nullable|numeric|min:0',
            'recurrent.*.q1_amt' => 'nullable|numeric|min:0',
            'recurrent.*.q2_qty' => 'nullable|numeric|min:0',
            'recurrent.*.q2_amt' => 'nullable|numeric|min:0',
            'recurrent.*.q3_qty' => 'nullable|numeric|min:0',
            'recurrent.*.q3_amt' => 'nullable|numeric|min:0',
            'recurrent.*.q4_qty' => 'nullable|numeric|min:0',
            'recurrent.*.q4_amt' => 'nullable|numeric|min:0',
        ];
    }
}
