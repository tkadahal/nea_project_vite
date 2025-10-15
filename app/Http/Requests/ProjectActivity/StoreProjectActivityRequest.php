<?php

declare(strict_types=1);

namespace App\Http\Requests\ProjectActivity;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method array all($keys = null)
 * @method void merge(array $input)
 */
class StoreProjectActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('projectActivity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return true;
    }

    public function rules(Request $request): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',

            'capital' => [
                'sometimes',
                'array',
                function (string $attribute, array $value, Closure $fail) {
                    $this->validateSectionHierarchy($value, 'capital', $fail);
                },
            ],
            'capital.*.program' => 'required|string|max:255',
            'capital.*.total_budget' => 'nullable|numeric|min:0',
            'capital.*.total_expense' => 'nullable|numeric|min:0',
            'capital.*.planned_budget' => 'nullable|numeric|min:0',
            'capital.*.q1' => 'nullable|numeric|min:0',
            'capital.*.q2' => 'nullable|numeric|min:0',
            'capital.*.q3' => 'nullable|numeric|min:0',
            'capital.*.q4' => 'nullable|numeric|min:0',
            'capital.*.parent_id' => 'nullable|integer',

            'recurrent' => [
                'sometimes',
                'array',
                function (string $attribute, array $value, Closure $fail) {
                    $this->validateSectionHierarchy($value, 'recurrent', $fail);
                },
            ],
            'recurrent.*.program' => 'required|string|max:255',
            'recurrent.*.total_budget' => 'nullable|numeric|min:0',
            'recurrent.*.total_expense' => 'nullable|numeric|min:0',
            'recurrent.*.planned_budget' => 'nullable|numeric|min:0',
            'recurrent.*.q1' => 'nullable|numeric|min:0',
            'recurrent.*.q2' => 'nullable|numeric|min:0',
            'recurrent.*.q3' => 'nullable|numeric|min:0',
            'recurrent.*.q4' => 'nullable|numeric|min:0',
            'recurrent.*.parent_id' => 'nullable|integer',
        ];
    }

    private function validateSectionHierarchy(array $data, string $section, Closure $fail): void
    {
        $indices = array_keys($data);
        Log::info('Section: ' . $section . ', Data keys: ' . json_encode($indices));

        foreach ($data as $index => $row) {
            $rowNum = $index + 1;
            $parentId = $row['parent_id'] ?? 'N/A';
            Log::info("Row {$rowNum} (index {$index}), parent_id: " . json_encode($parentId) .
                ", program: " . json_encode($row['program'] ?? '') .
                ", has_budget: " . json_encode($this->any_budget_positive($row)));

            // Skip rows with no parent
            if (!isset($row['parent_id']) || $row['parent_id'] === '' || $row['parent_id'] === null) {
                continue;
            }

            $parentIndex = (int) $row['parent_id'];

            if ($parentIndex < 0 || !array_key_exists($parentIndex, $data)) {
                $fail("Invalid parent_id for {$section} row {$rowNum}: Parent activity not found or not saved");
                continue;
            }

            if ($parentIndex >= $index) {
                $fail("Parent must precede child in {$section} row {$rowNum}");
                continue;
            }
        }
    }

    private function any_budget_positive(array $row): bool
    {
        return ($row['total_budget'] ?? 0) > 0 ||
            ($row['total_expense'] ?? 0) > 0 ||
            ($row['planned_budget'] ?? 0) > 0 ||
            ($row['q1'] ?? 0) > 0 ||
            ($row['q2'] ?? 0) > 0 ||
            ($row['q3'] ?? 0) > 0 ||
            ($row['q4'] ?? 0) > 0;
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Selected project does not exist.',
            'fiscal_year_id.required' => 'Fiscal year is required.',
            'fiscal_year_id.exists' => 'Selected fiscal year does not exist.',

            'capital.*.program.required' => 'Program name is required for capital row.',
            'capital.*.program.max' => 'Program name may not be greater than 255 characters.',
            'capital.*.total_budget.numeric' => 'Total budget must be a number for capital row.',
            'capital.*.total_budget.min' => 'Total budget must be at least 0 for capital row.',
            'capital.*.total_expense.numeric' => 'Expenses till date must be a number for capital row.',
            'capital.*.total_expense.min' => 'Expenses till date must be at least 0 for capital row.',
            'capital.*.planned_budget.numeric' => 'Planned budget must be a number for capital row.',
            'capital.*.planned_budget.min' => 'Planned budget must be at least 0 for capital row.',
            'capital.*.q1.numeric' => 'Q1 budget must be a number for capital row.',
            'capital.*.q1.min' => 'Q1 budget must be at least 0 for capital row.',
            'capital.*.q2.numeric' => 'Q2 budget must be a number for capital row.',
            'capital.*.q2.min' => 'Q2 budget must be at least 0 for capital row.',
            'capital.*.q3.numeric' => 'Q3 budget must be a number for capital row.',
            'capital.*.q3.min' => 'Q3 budget must be at least 0 for capital row.',
            'capital.*.q4.numeric' => 'Q4 budget must be a number for capital row.',
            'capital.*.q4.min' => 'Q4 budget must be at least 0 for capital row.',
            'capital.*.parent_id.integer' => 'Parent ID must be a valid integer for capital row.',

            'recurrent.*.program.required' => 'Program name is required for recurrent row.',
            'recurrent.*.program.max' => 'Program name may not be greater than 255 characters.',
            'recurrent.*.total_budget.numeric' => 'Total budget must be a number for recurrent row.',
            'recurrent.*.total_budget.min' => 'Total budget must be at least 0 for recurrent row.',
            'recurrent.*.total_expense.numeric' => 'Expenses till date must be a number for recurrent row.',
            'recurrent.*.total_expense.min' => 'Expenses till date must be at least 0 for recurrent row.',
            'recurrent.*.planned_budget.numeric' => 'Planned budget must be a number for recurrent row.',
            'recurrent.*.planned_budget.min' => 'Planned budget must be at least 0 for recurrent row.',
            'recurrent.*.q1.numeric' => 'Q1 budget must be a number for recurrent row.',
            'recurrent.*.q1.min' => 'Q1 budget must be at least 0 for recurrent row.',
            'recurrent.*.q2.numeric' => 'Q2 budget must be a number for recurrent row.',
            'recurrent.*.q2.min' => 'Q2 budget must be at least 0 for recurrent row.',
            'recurrent.*.q3.numeric' => 'Q3 budget must be a number for recurrent row.',
            'recurrent.*.q3.min' => 'Q3 budget must be at least 0 for recurrent row.',
            'recurrent.*.q4.numeric' => 'Q4 budget must be a number for recurrent row.',
            'recurrent.*.q4.min' => 'Q4 budget must be at least 0 for recurrent row.',
            'recurrent.*.parent_id.integer' => 'Parent ID must be a valid integer for recurrent row.',
        ];
    }

    protected function prepareForValidation()
    {
        $input = $this->all();

        if (array_key_exists('capital', $input)) {
            $this->processSection($input['capital'], 'capital');
        }

        if (array_key_exists('recurrent', $input)) {
            $this->processSection($input['recurrent'], 'recurrent');
        }
    }

    /**
     * Cleans up the section data, removes empty rows, reindexes them,
     * and remaps parent_id correctly.
     */
    private function processSection(array $sectionData, string $sectionName): void
    {
        $originalData = $sectionData;
        Log::info("Original {$sectionName} keys: " . json_encode(array_keys($originalData)));

        // Filter out empty rows
        $filteredData = array_filter($originalData, function ($exp) {
            $keep = !empty(trim($exp['program'] ?? '')) ||
                ($exp['total_budget'] ?? 0) > 0 ||
                ($exp['total_expense'] ?? 0) > 0 ||
                ($exp['planned_budget'] ?? 0) > 0 ||
                ($exp['q1'] ?? 0) > 0 ||
                ($exp['q2'] ?? 0) > 0 ||
                ($exp['q3'] ?? 0) > 0 ||
                ($exp['q4'] ?? 0) > 0;
            return $keep;
        });

        Log::info("Filtered {$sectionName} keys (pre-values): " . json_encode(array_keys($filteredData)));

        // Create mapping oldIndex → newIndex
        $mapping = [];
        $newIndex = 0;
        foreach ($originalData as $oldIndex => $exp) {
            if (isset($filteredData[$oldIndex])) {
                $mapping[$oldIndex] = $newIndex;
                $newIndex++;
            }
        }

        // Reindex
        $filteredData = array_values($filteredData);
        $parentIds = array_column($filteredData, 'parent_id');
        Log::info("Final {$sectionName} after mapping: " . json_encode(array_keys($filteredData)) .
            ", Sample parent_ids: " . json_encode($parentIds));

        // 🔧 Fix: Clean and remap parent_ids properly
        foreach ($filteredData as $newIndex => &$row) {
            if (!isset($row['parent_id']) || $row['parent_id'] === '' || $row['parent_id'] === null) {
                unset($row['parent_id']);
                continue;
            }

            // Ignore invalid / non-numeric parent IDs
            if (!is_numeric($row['parent_id'])) {
                unset($row['parent_id']);
                continue;
            }

            $oldParentIndex = (int) $row['parent_id'];
            if (isset($mapping[$oldParentIndex])) {
                $row['parent_id'] = $mapping[$oldParentIndex];
            } else {
                unset($row['parent_id']); // parent filtered out
            }
        }

        Log::info("Remapped {$sectionName} parent_ids: " . json_encode(array_column($filteredData, 'parent_id')));
        $this->merge([$sectionName => $filteredData]);
    }
}
