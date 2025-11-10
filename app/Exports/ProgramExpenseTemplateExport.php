<?php

namespace App\Exports;

use App\Models\ProjectActivity;
use App\Models\ProjectExpense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ProgramExpenseTemplateExport implements FromCollection, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $projectTitle;
    protected $fiscalYearTitle;
    protected $projectId;
    protected $fiscalYearId;
    protected $quarter;
    protected $reportingPeriod;
    protected $globalXCapital;
    protected $globalXRecurrent;
    protected $capitalPeriodTotal;
    protected $recurrentPeriodTotal;

    // Default values set based on the template structure
    public function __construct($projectTitle = 'कार्यालय/आयोजना', $fiscalYearTitle = '२०८१/८२', $projectId = null, $fiscalYearId = null, $quarter = 1)
    {
        $this->projectTitle = $projectTitle;
        $this->fiscalYearTitle = $fiscalYearTitle;
        $this->projectId = $projectId;
        $this->fiscalYearId = $fiscalYearId;
        $this->quarter = $quarter;
        $this->reportingPeriod = $this->getQuarterText($quarter);
    }

    private function getQuarterText(int $quarter): string
    {
        $quarters = [
            1 => 'पहिलो त्रैमास',
            2 => 'दोस्रो त्रैमास',
            3 => 'तेस्रो त्रैमास',
            4 => 'चौथो त्रैमास'
        ];
        return $quarters[$quarter] ?? 'त्रैमासिक';
    }

    private function convertToNepaliDigits(string $input): string
    {
        $map = [
            '0' => '०',
            '1' => '१',
            '2' => '२',
            '3' => '३',
            '4' => '४',
            '5' => '५',
            '6' => '६',
            '7' => '७',
            '8' => '८',
            '9' => '९'
        ];
        return strtr($input, $map);
    }

    private function formatQuantity(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals);
    }

    private function formatAmount(float $number, int $decimals = 2): string
    {
        $formatted = number_format($number, $decimals);
        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function formatPercent(float $number): string
    {
        return number_format($number, 2);
    }

    public function collection()
    {
        // Total 21 columns (A-U, indices 0-20)
        $headerRows = [];

        // Row 1: नेपाल सरकार (A1:U1)
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[0][0] = 'नेपाल सरकार';

        // Row 2: ............ मन्त्रालय/निकाय (fully merged)
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[1][0] = '......................... मन्त्रालय/निकाय';

        // Row 3: ............ कार्यालय/आयोजना (fully merged)
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[2][0] = $this->projectTitle;

        // Row 4: Main Title
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[3][0] = 'आ.व. ' . $this->fiscalYearTitle . ' को ' . $this->reportingPeriod . ' अवधिसम्मको लक्ष्य र प्रगति विवरण';

        // Row 5: बजेट उपशीर्षक नं. (Left)
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[4][0] = 'बजेट उपशीर्षक नं. ....................';

        // Row 6: (रकम रु. हजारमा) (T6:U6)
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[5][19] = '(रकम रु हजारमा)';

        // Row 7: Main Headers
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[6][0] = 'क्रम';
        $headerRows[6][1] = 'कार्यक्रम/';
        $headerRows[6][2] = 'एकाइ';
        $headerRows[6][3] = 'आयोजना अवधिसम्मको लक्ष्य (बहुवर्षीय आयोजनाको हकमा)';
        $headerRows[6][6] = 'वार्षिक लक्ष्य';
        $periodTargetHeader = 'यस ' . $this->reportingPeriod . ' अवधिसम्मको लक्ष्य';
        $headerRows[6][9] = $periodTargetHeader;
        $headerRows[6][12] = 'चौमासिक / वार्षिक प्रगति (भौतिक)';
        $periodPhysicalHeader = 'यस ' . $this->reportingPeriod . ' अवधिसम्मको भौतिक प्रगति (बहुवर्षीय आयोजनाको हकमा)';
        $headerRows[6][15] = $periodPhysicalHeader;
        $periodExpenseHeader = 'यस ' . $this->reportingPeriod . ' अवधिसम्मको खर्च';
        $headerRows[6][18] = $periodExpenseHeader;
        $headerRows[6][20] = 'कैफियत';

        // Row 8: Sub Headers
        $headerRows[] = array_fill(0, 21, null);
        $headerRows[7][0] = 'सङ्ख्या';
        $headerRows[7][1] = 'क्रियाकलापहरु';
        $headerRows[7][2] = null;

        // D8-F8: आयोजना अवधिसम्मको लक्ष्य
        $headerRows[7][3] = 'परिमाण';
        $headerRows[7][4] = 'भार';
        $headerRows[7][5] = 'बजेट';

        // G8-I8: वार्षिक लक्ष्य
        $headerRows[7][6] = 'परिमाण';
        $headerRows[7][7] = 'भार';
        $headerRows[7][8] = 'बजेट';

        // J8-L8: यस अवधिसम्मको लक्ष्य
        $headerRows[7][9] = 'परिमाण';
        $headerRows[7][10] = 'भार';
        $headerRows[7][11] = 'बजेट';

        // M8-N8: चौमासिक / वार्षिक प्रगति
        $headerRows[7][12] = 'परिमाण';
        $headerRows[7][13] = 'भार';
        $headerRows[7][14] = 'प्रतिशत';

        // O8-P8: यस अवधिसम्मको भौतिक प्रगति
        $headerRows[7][15] = 'परिमाण';
        $headerRows[7][16] = 'भार';
        $headerRows[7][17] = 'प्रतिशत';

        // Q8-T8: यस अवधिसम्मको खर्च (S-T for amount and percent; Q-R for progress percent? Wait, per code)
        $headerRows[7][18] = 'रकम रु.';
        $headerRows[7][19] = 'प्रतिशत';

        // U8: कैफियत (empty for merge)
        // Already null

        // Row 9: Number headers
        $numberRow = array_fill(0, 21, null);
        for ($col = 0; $col < 21; $col++) {
            $numberRow[$col] = $this->convertToNepaliDigits((string) ($col + 1));
        }
        $headerRows[] = $numberRow;

        if (!$this->projectId || !$this->fiscalYearId) {
            // Fallback to template with empty rows
            $dataRows = [];
            for ($i = 0; $i < 20; $i++) {
                $dataRows[] = array_fill(0, 21, '');
            }
            return collect(array_merge($headerRows, $dataRows));
        }

        // Fetch activities with eager loading
        $activities = ProjectActivity::with('expenses.quarters')
            ->where('project_id', $this->projectId)
            ->where('fiscal_year_id', $this->fiscalYearId)
            ->get([
                'id',
                'parent_id',
                'program',
                'expenditure_id',
                'total_quantity',
                'total_budget',
                'planned_quantity',
                'planned_budget',
                'q1_quantity',
                'q2_quantity',
                'q3_quantity',
                'q4_quantity',
                'q1',
                'q2',
                'q3',
                'q4'
            ]);

        $activityMap = $activities->keyBy('id');
        $groupedActivities = $activities->groupBy(fn($a) => $a->parent_id ?? 'null');

        $capital_roots = $activities->whereNull('parent_id')->where('expenditure_id', 1)->values();
        $recurrent_roots = $activities->whereNull('parent_id')->where('expenditure_id', 2)->values();

        // Precompute leaf values for all activities
        $leafValues = [];
        foreach ($activities as $act) {
            $id = $act->id;
            $period_qty_planned = 0.0;
            $period_amt_planned = 0.0;
            for ($i = 1; $i <= $this->quarter; $i++) {
                $period_qty_planned += (float) ($act->{"q{$i}_quantity"} ?? 0);
                $period_amt_planned += (float) ($act->{"q{$i}"} ?? 0);
            }

            $period_qty_actual = 0.0;
            $period_amt_actual = 0.0;
            $quarter_qty_actual = 0.0;
            foreach ($act->expenses as $exp) {
                foreach ($exp->quarters as $qtr) {
                    if ($qtr->quarter <= $this->quarter) {
                        $period_qty_actual += (float) $qtr->quantity;
                        $period_amt_actual += (float) $qtr->amount;
                    }
                    if ($qtr->quarter == $this->quarter) {
                        $quarter_qty_actual += (float) $qtr->quantity;
                    }
                }
            }

            $total_qty = (float) ($act->total_quantity ?? 0);
            $total_budget = (float) ($act->total_budget ?? 0);
            $annual_qty = (float) ($act->planned_quantity ?? 0);
            $annual_amt = (float) ($act->planned_budget ?? 0);

            $weighted_annual_qty = $total_qty > 0 ? ($annual_qty / $total_qty * $total_budget) : 0.0;
            $weighted_quarter_physical = $total_qty > 0 ? ($quarter_qty_actual / $total_qty * $total_budget) : 0.0;
            $weighted_period_physical = $total_qty > 0 ? ($period_qty_actual / $total_qty * $total_budget) : 0.0;

            $leafValues[$id] = [
                'total_qty' => $total_qty,
                'total_budget' => $total_budget,
                'annual_qty' => $annual_qty,
                'annual_amt' => $annual_amt,
                'period_qty_planned' => $period_qty_planned,
                'period_amt_planned' => $period_amt_planned,
                'period_qty_actual' => $period_qty_actual,
                'period_amt_actual' => $period_amt_actual,
                'quarter_qty_actual' => $quarter_qty_actual,
                'weighted_annual_qty' => $weighted_annual_qty,
                'weighted_quarter_physical' => $weighted_quarter_physical,
                'weighted_period_physical' => $weighted_period_physical,
            ];
        }

        // Single recursive computation for all subtree sums
        $subtreeSums = [];
        $computeSubtreeSums = function ($actId) use ($leafValues, $groupedActivities, &$subtreeSums, &$computeSubtreeSums) {
            if (isset($subtreeSums[$actId])) {
                return $subtreeSums[$actId];
            }

            $children = $groupedActivities[$actId] ?? collect();
            $sums = [
                'total_qty' => 0.0,
                'total_budget' => 0.0,
                'annual_qty' => 0.0,
                'annual_amt' => 0.0,
                'period_qty_planned' => 0.0,
                'period_amt_planned' => 0.0,
                'period_qty_actual' => 0.0,
                'period_amt_actual' => 0.0,
                'quarter_qty_actual' => 0.0,
                'weighted_annual_qty' => 0.0,
                'weighted_quarter_physical' => 0.0,
                'weighted_period_physical' => 0.0,
            ];

            foreach ($children as $child) {
                $childSums = $computeSubtreeSums($child->id);
                foreach ($sums as $key => &$value) {
                    $value += $childSums[$key];
                }
            }

            if ($children->isEmpty()) {
                $sums = $leafValues[$actId];
            }

            $subtreeSums[$actId] = $sums;
            return $sums;
        };

        $allRoots = $capital_roots->concat($recurrent_roots);
        foreach ($allRoots as $root) {
            $computeSubtreeSums($root->id);
        }

        // Compute globals using subtree sums for consistency
        $this->globalXCapital = $capital_roots->isEmpty() ? 0.0 : collect($capital_roots)->sum(fn($root) => $subtreeSums[$root->id]['total_budget']);
        $this->globalXRecurrent = $recurrent_roots->isEmpty() ? 0.0 : collect($recurrent_roots)->sum(fn($root) => $subtreeSums[$root->id]['total_budget']);
        $this->capitalPeriodTotal = $capital_roots->isEmpty() ? 0.0 : collect($capital_roots)->sum(fn($root) => $subtreeSums[$root->id]['period_amt_planned']);
        $this->recurrentPeriodTotal = $recurrent_roots->isEmpty() ? 0.0 : collect($recurrent_roots)->sum(fn($root) => $subtreeSums[$root->id]['period_amt_planned']);

        // Build flat hierarchical data rows
        $dataRows = [];

        // Recursive traverse function (now simplified, no accumulation)
        $traverse = function ($acts, $level = 0, $path = [], $globalX, $periodTotal) use (&$dataRows, &$traverse, $groupedActivities, $subtreeSums, $activityMap) {
            foreach ($acts as $index => $act) {
                $currentPath = array_merge($path, [$index + 1]);
                $serial = implode('.', $currentPath);
                $parentSerial = implode('.', $path);
                $indent = str_repeat('  ', $level * 2);

                $row = array_fill(0, 21, '');
                $row[0] = $this->convertToNepaliDigits($serial);
                $row[1] = $indent . ($act->program ?? '');
                $row[2] = ''; // Unit

                $children = $groupedActivities[$act->id] ?? collect();
                $hasChildren = $children->isNotEmpty();

                if (!$hasChildren) {
                    // Leaf row: use subtree sums (equals leaf values)
                    $subtree = $subtreeSums[$act->id];

                    $row[3] = $this->formatQuantity($subtree['total_qty'], 2);
                    $weightProject = $globalX > 0 ? ($subtree['total_budget'] / $globalX * 100) : 0;
                    $row[4] = $this->formatPercent($weightProject);
                    $row[5] = $this->formatAmount($subtree['total_budget'] / 1000, 2);

                    $row[6] = $this->formatQuantity($subtree['annual_qty'], 2);
                    $weightAnnual = $globalX > 0 ? ($subtree['weighted_annual_qty'] / $globalX * 100) : 0;
                    $row[7] = $this->formatPercent($weightAnnual);
                    $row[8] = $this->formatAmount($subtree['annual_amt'] / 1000, 2);

                    $row[9] = $this->formatQuantity($subtree['period_qty_planned'], 2);
                    $weightPeriod = $periodTotal > 0 ? ($subtree['period_amt_planned'] / $periodTotal * 100) : 0;
                    $row[10] = $this->formatPercent($weightPeriod);
                    $row[11] = $this->formatAmount($subtree['period_amt_planned'] / 1000, 2);

                    $row[12] = $this->formatQuantity($subtree['quarter_qty_actual'], 2);
                    $weightQuarter = $globalX > 0 ? ($subtree['weighted_quarter_physical'] / $globalX * 100) : 0;
                    $row[13] = $this->formatPercent($weightQuarter);
                    $quarterPerc = $subtree['annual_qty'] > 0 ? ($subtree['quarter_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
                    $row[14] = $this->formatPercent($quarterPerc);

                    $row[15] = $this->formatQuantity($subtree['period_qty_actual'], 2);
                    $weightPeriodPhysical = $globalX > 0 ? ($subtree['weighted_period_physical'] / $globalX * 100) : 0;
                    $row[16] = $this->formatPercent($weightPeriodPhysical);
                    $periodPerc = $subtree['annual_qty'] > 0 ? ($subtree['period_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
                    $row[17] = $this->formatPercent($periodPerc);

                    $row[18] = $this->formatAmount($subtree['period_amt_actual'] / 1000, 2);
                    $expPerc = $subtree['period_amt_planned'] > 0 ? ($subtree['period_amt_actual'] / $subtree['period_amt_planned'] * 100) : 0;
                    $row[19] = $this->formatPercent($expPerc);

                    $row[20] = ''; // Remarks
                } else {
                    // Parent row: zeros for quantities/budgets, empty for weights, zero for percents
                    $row[3] = $this->formatQuantity(0, 2);
                    $row[4] = '';
                    $row[5] = $this->formatAmount(0 / 1000, 2);

                    $row[6] = $this->formatQuantity(0, 2);
                    $row[7] = '';
                    $row[8] = $this->formatAmount(0 / 1000, 2);

                    $row[9] = $this->formatQuantity(0, 2);
                    $row[10] = '';
                    $row[11] = $this->formatAmount(0 / 1000, 2);

                    $row[12] = $this->formatQuantity(0, 2);
                    $row[13] = '';
                    $row[14] = $this->formatPercent(0);

                    $row[15] = $this->formatQuantity(0, 2);
                    $row[16] = '';
                    $row[17] = $this->formatPercent(0);

                    $row[18] = $this->formatAmount(0 / 1000, 2);
                    $row[19] = $this->formatPercent(0);

                    $row[20] = '';
                }

                $dataRows[] = $row;

                if ($hasChildren) {
                    $traverse($children, $level + 1, $currentPath, $globalX, $periodTotal);

                    // Add total row for this parent
                    $totalRow = array_fill(0, 21, '');
                    $totalRow[0] = $this->convertToNepaliDigits($parentSerial);
                    $totalRow[1] = $indent . 'Total of ' . $parentSerial;
                    $totalRow[2] = '';

                    $subtree = $subtreeSums[$act->id];

                    // Blanks for quantities
                    $totalRow[3] = '';
                    $totalRow[6] = '';
                    $totalRow[9] = '';
                    $totalRow[12] = '';
                    $totalRow[15] = '';

                    // Weights
                    $weightProject = $globalX > 0 ? ($subtree['total_budget'] / $globalX * 100) : 0;
                    $totalRow[4] = $this->formatPercent($weightProject);

                    $weightAnnual = $globalX > 0 ? ($subtree['weighted_annual_qty'] / $globalX * 100) : 0;
                    $totalRow[7] = $this->formatPercent($weightAnnual);

                    $weightPeriod = $periodTotal > 0 ? ($subtree['period_amt_planned'] / $periodTotal * 100) : 0;
                    $totalRow[10] = $this->formatPercent($weightPeriod);

                    $weightQuarter = $globalX > 0 ? ($subtree['weighted_quarter_physical'] / $globalX * 100) : 0;
                    $totalRow[13] = $this->formatPercent($weightQuarter);

                    $weightPeriodPhysical = $globalX > 0 ? ($subtree['weighted_period_physical'] / $globalX * 100) : 0;
                    $totalRow[16] = $this->formatPercent($weightPeriodPhysical);

                    // Budgets
                    $totalRow[5] = $this->formatAmount($subtree['total_budget'] / 1000, 2);
                    $totalRow[8] = $this->formatAmount($subtree['annual_amt'] / 1000, 2);
                    $totalRow[11] = $this->formatAmount($subtree['period_amt_planned'] / 1000, 2);

                    // Percents
                    $quarterPerc = $subtree['annual_qty'] > 0 ? ($subtree['quarter_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
                    $totalRow[14] = $this->formatPercent($quarterPerc);

                    $periodPerc = $subtree['annual_qty'] > 0 ? ($subtree['period_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
                    $totalRow[17] = $this->formatPercent($periodPerc);

                    $totalRow[18] = $this->formatAmount($subtree['period_amt_actual'] / 1000, 2);
                    $expPerc = $subtree['period_amt_planned'] > 0 ? ($subtree['period_amt_actual'] / $subtree['period_amt_planned'] * 100) : 0;
                    $totalRow[19] = $this->formatPercent($expPerc);

                    $totalRow[20] = '';

                    $dataRows[] = $totalRow;
                }
            }
        };

        // Capital Section
        $capitalHeader = array_fill(0, 21, null);
        $capitalHeader[0] = 'पुँजीगत वर्षान्तक कार्यक्रम :';
        $dataRows[] = $capitalHeader;

        if ($capital_roots->isNotEmpty()) {
            $traverse($capital_roots, 0, [], $this->globalXCapital, $this->capitalPeriodTotal);

            // Capital Section Total
            $sectionSums = [
                'total_qty' => 0.0,
                'total_budget' => 0.0,
                'annual_qty' => 0.0,
                'annual_amt' => 0.0,
                'period_qty_planned' => 0.0,
                'period_amt_planned' => 0.0,
                'period_qty_actual' => 0.0,
                'period_amt_actual' => 0.0,
                'quarter_qty_actual' => 0.0,
                'weighted_annual_qty' => 0.0,
                'weighted_quarter_physical' => 0.0,
                'weighted_period_physical' => 0.0,
            ];
            foreach ($capital_roots as $root) {
                $rootSums = $subtreeSums[$root->id];
                foreach ($sectionSums as $key => &$value) {
                    $value += $rootSums[$key];
                }
            }

            $capitalTotalRow = array_fill(0, 21, '');
            $capitalTotalRow[0] = '(क) जम्मा';

            // Blanks for quantities
            $capitalTotalRow[3] = '';
            $capitalTotalRow[6] = '';
            $capitalTotalRow[9] = '';
            $capitalTotalRow[12] = '';
            $capitalTotalRow[15] = '';

            $subtree = $sectionSums;
            $globalX = $this->globalXCapital;
            $periodTotal = $this->capitalPeriodTotal;

            // Weights
            $weightProject = $globalX > 0 ? ($subtree['total_budget'] / $globalX * 100) : 0;
            $capitalTotalRow[4] = $this->formatPercent($weightProject);

            $weightAnnual = $globalX > 0 ? ($subtree['weighted_annual_qty'] / $globalX * 100) : 0;
            $capitalTotalRow[7] = $this->formatPercent($weightAnnual);

            $weightPeriod = $periodTotal > 0 ? ($subtree['period_amt_planned'] / $periodTotal * 100) : 0;
            $capitalTotalRow[10] = $this->formatPercent($weightPeriod);

            $weightQuarter = $globalX > 0 ? ($subtree['weighted_quarter_physical'] / $globalX * 100) : 0;
            $capitalTotalRow[13] = $this->formatPercent($weightQuarter);

            $weightPeriodPhysical = $globalX > 0 ? ($subtree['weighted_period_physical'] / $globalX * 100) : 0;
            $capitalTotalRow[16] = $this->formatPercent($weightPeriodPhysical);

            // Budgets
            $capitalTotalRow[5] = $this->formatAmount($subtree['total_budget'] / 1000, 2);
            $capitalTotalRow[8] = $this->formatAmount($subtree['annual_amt'] / 1000, 2);
            $capitalTotalRow[11] = $this->formatAmount($subtree['period_amt_planned'] / 1000, 2);

            // Percents
            $quarterPerc = $subtree['annual_qty'] > 0 ? ($subtree['quarter_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
            $capitalTotalRow[14] = $this->formatPercent($quarterPerc);

            $periodPerc = $subtree['annual_qty'] > 0 ? ($subtree['period_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
            $capitalTotalRow[17] = $this->formatPercent($periodPerc);

            $capitalTotalRow[18] = $this->formatAmount($subtree['period_amt_actual'] / 1000, 2);
            $expPerc = $subtree['period_amt_planned'] > 0 ? ($subtree['period_amt_actual'] / $subtree['period_amt_planned'] * 100) : 0;
            $capitalTotalRow[19] = $this->formatPercent($expPerc);

            $capitalTotalRow[20] = '';

            $dataRows[] = $capitalTotalRow;
        }

        // Recurrent Section
        $recurrentHeader = array_fill(0, 21, null);
        $recurrentHeader[0] = '(ख) वाह्य सहायता कार्यक्रम';
        $dataRows[] = $recurrentHeader;

        if ($recurrent_roots->isNotEmpty()) {
            $traverse($recurrent_roots, 0, [], $this->globalXRecurrent, $this->recurrentPeriodTotal);

            // Recurrent Section Total
            $sectionSums = [
                'total_qty' => 0.0,
                'total_budget' => 0.0,
                'annual_qty' => 0.0,
                'annual_amt' => 0.0,
                'period_qty_planned' => 0.0,
                'period_amt_planned' => 0.0,
                'period_qty_actual' => 0.0,
                'period_amt_actual' => 0.0,
                'quarter_qty_actual' => 0.0,
                'weighted_annual_qty' => 0.0,
                'weighted_quarter_physical' => 0.0,
                'weighted_period_physical' => 0.0,
            ];
            foreach ($recurrent_roots as $root) {
                $rootSums = $subtreeSums[$root->id];
                foreach ($sectionSums as $key => &$value) {
                    $value += $rootSums[$key];
                }
            }

            $recurrentTotalRow = array_fill(0, 21, '');
            $recurrentTotalRow[0] = '(ख) जम्मा';

            // Blanks for quantities
            $recurrentTotalRow[3] = '';
            $recurrentTotalRow[6] = '';
            $recurrentTotalRow[9] = '';
            $recurrentTotalRow[12] = '';
            $recurrentTotalRow[15] = '';

            $subtree = $sectionSums;
            $globalX = $this->globalXRecurrent;
            $periodTotal = $this->recurrentPeriodTotal;

            // Weights
            $weightProject = $globalX > 0 ? ($subtree['total_budget'] / $globalX * 100) : 0;
            $recurrentTotalRow[4] = $this->formatPercent($weightProject);

            $weightAnnual = $globalX > 0 ? ($subtree['weighted_annual_qty'] / $globalX * 100) : 0;
            $recurrentTotalRow[7] = $this->formatPercent($weightAnnual);

            $weightPeriod = $periodTotal > 0 ? ($subtree['period_amt_planned'] / $periodTotal * 100) : 0;
            $recurrentTotalRow[10] = $this->formatPercent($weightPeriod);

            $weightQuarter = $globalX > 0 ? ($subtree['weighted_quarter_physical'] / $globalX * 100) : 0;
            $recurrentTotalRow[13] = $this->formatPercent($weightQuarter);

            $weightPeriodPhysical = $globalX > 0 ? ($subtree['weighted_period_physical'] / $globalX * 100) : 0;
            $recurrentTotalRow[16] = $this->formatPercent($weightPeriodPhysical);

            // Budgets
            $recurrentTotalRow[5] = $this->formatAmount($subtree['total_budget'] / 1000, 2);
            $recurrentTotalRow[8] = $this->formatAmount($subtree['annual_amt'] / 1000, 2);
            $recurrentTotalRow[11] = $this->formatAmount($subtree['period_amt_planned'] / 1000, 2);

            // Percents
            $quarterPerc = $subtree['annual_qty'] > 0 ? ($subtree['quarter_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
            $recurrentTotalRow[14] = $this->formatPercent($quarterPerc);

            $periodPerc = $subtree['annual_qty'] > 0 ? ($subtree['period_qty_actual'] / $subtree['annual_qty'] * 100) : 0;
            $recurrentTotalRow[17] = $this->formatPercent($periodPerc);

            $recurrentTotalRow[18] = $this->formatAmount($subtree['period_amt_actual'] / 1000, 2);
            $expPerc = $subtree['period_amt_planned'] > 0 ? ($subtree['period_amt_actual'] / $subtree['period_amt_planned'] * 100) : 0;
            $recurrentTotalRow[19] = $this->formatPercent($expPerc);

            $recurrentTotalRow[20] = '';

            $dataRows[] = $recurrentTotalRow;
        }

        return collect(array_merge($headerRows, $dataRows));
    }

    public function title(): string
    {
        $safeTitle = str_replace(['/', '\\'], '_', $this->fiscalYearTitle);
        return 'Progress Report ' . $safeTitle;
    }

    public function styles(Worksheet $sheet)
    {
        // Row 1: नेपाल सरकार (A1:U1)
        $sheet->mergeCells('A1:U1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Row 2: मन्त्रालय/निकाय (A2:U2 - fully merged and center aligned)
        $sheet->mergeCells('A2:U2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Row 3: कार्यालय/आयोजना (A3:U3 - fully merged and center aligned)
        $sheet->mergeCells('A3:U3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Row 4: Main Title (A4:U4)
        $sheet->mergeCells('A4:U4');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Row 5: बजेट उपशीर्षक (A5:B5)
        $sheet->mergeCells('A5:B5');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // Row 6: (रकम रु. हजारमा) (T6:U6)
        $sheet->mergeCells('T6:U6');
        $sheet->getStyle('T6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('T6')->getFont()->setSize(10);

        // --- HEADER MERGES (Rows 7 & 8) ---

        // Vertical Merges
        $sheet->mergeCells('A7:A8');
        $sheet->mergeCells('B7:B8');
        $sheet->mergeCells('C7:C8');
        $sheet->mergeCells('U7:U8');

        // Horizontal Merges (Row 7)
        $sheet->mergeCells('D7:F7');
        $sheet->mergeCells('G7:I7');
        $sheet->mergeCells('J7:L7');
        $sheet->mergeCells('M7:O7');
        $sheet->mergeCells('P7:R7');
        $sheet->mergeCells('S7:T7');

        // --- STYLES FOR HEADERS ---
        $headerRange = 'A7:U8';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
        $sheet->getStyle($headerRange)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);

        // Row 9: Number headers
        $sheet->getStyle('A9:U9')->getFont()->setSize(9)->setBold(true);
        $sheet->getStyle('A9:U9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A9:U9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
        $sheet->getStyle('A9:U9')->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);
        $sheet->getRowDimension(9)->setRowHeight(20);

        // Dynamic data rows styling (from row 10 to last row)
        $lastRow = $sheet->getHighestRow();
        for ($row = 10; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
            $dataRange = 'A' . $row . ':U' . $row;
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row . ':T' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('U' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            // Reduce font size for data rows
            $sheet->getStyle($dataRange)->getFont()->setSize(8);
        }

        // Set font for Devanagari support
        $fullRange = 'A1:U' . $lastRow;
        $sheet->getStyle($fullRange)->getFont()->setName('Nirmala UI');

        // Adjust row heights for top
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(18);
        $sheet->getRowDimension(7)->setRowHeight(35);
        $sheet->getRowDimension(8)->setRowHeight(35);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // क्रम सङ्ख्या
            'B' => 30,  // कार्यक्रम/क्रियाकलापहरु
            'C' => 6,   // एकाइ
            'D' => 8,   // परिमाण
            'E' => 6,   // भार
            'F' => 8,   // बजेट
            'G' => 8,   // परिमाण
            'H' => 6,   // भार
            'I' => 8,   // बजेट
            'J' => 8,   // परिमाण
            'K' => 6,   // भार
            'L' => 8,   // बजेट
            'M' => 8,   // परिमाण
            'N' => 6,   // भार
            'O' => 8,   // प्रतिशत
            'P' => 8,   // परिमाण
            'Q' => 6,   // भार
            'R' => 8,   // प्रतिशत
            'S' => 8,   // रकम रु.
            'T' => 6,   // प्रतिशत
            'U' => 15,   // कैफियत
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set page orientation to Landscape
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                // Set paper size to A4
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

                // Fit to one page wide
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0); // 0 = automatic height

                // Set margins (in inches) - narrow margins for more content
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setHeader(0.3);
                $sheet->getPageMargins()->setFooter(0.3);

                // Center on page horizontally
                $sheet->getPageSetup()->setHorizontalCentered(true);

                // Set row/column headings to print on every page
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 9);

                // Merge and style section headers
                $lastRow = $sheet->getHighestRow();
                for ($row = 10; $row <= $lastRow; $row++) {
                    $headerCellValue = $sheet->getCell('A' . $row)->getValue();
                    if (is_string($headerCellValue) && (str_contains($headerCellValue, 'पुँजीगत वर्षान्तक कार्यक्रम') || str_contains($headerCellValue, 'वाह्य सहायता कार्यक्रम'))) {
                        $sheet->mergeCells('A' . $row . ':U' . $row);
                        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getRowDimension($row)->setRowHeight(25);
                    }

                    // Highlight total rows (yellow background, bold)
                    $cellAValue = $sheet->getCell('A' . $row)->getValue();
                    $cellBValue = $sheet->getCell('B' . $row)->getValue();
                    if (is_string($cellBValue) && str_contains($cellBValue, 'Total of ') || (is_string($cellAValue) && str_contains($cellAValue, 'जम्मा'))) {
                        $range = 'A' . $row . ':U' . $row;
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $sheet->getStyle($range)->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}
