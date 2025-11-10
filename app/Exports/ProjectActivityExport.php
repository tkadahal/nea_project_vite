<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\ProjectActivity;

class ProjectActivityExport implements FromArray, WithTitle, WithStyles, WithEvents
{
    protected $projectId;
    protected $fiscalYearId;
    protected $project;
    protected $fiscalYear;
    protected $totalRows = [];
    protected $headerRows = [];
    protected $footerStart;
    protected $globalXCapital;
    protected $globalXRecurrent;
    protected $tableHeaderRow; // Track where table headers start

    public function __construct($projectId, $fiscalYearId, $project, $fiscalYear)
    {
        $this->projectId = $projectId;
        $this->fiscalYearId = $fiscalYearId;
        $this->project = $project;
        $this->fiscalYear = $fiscalYear;
    }

    public function array(): array
    {
        // Load activities
        $capitalActivities = $this->project->projectActivities()
            ->where('fiscal_year_id', $this->fiscalYearId)
            ->where('expenditure_id', 1)
            ->with('children.children')
            ->get();

        $recurrentActivities = $this->project->projectActivities()
            ->where('fiscal_year_id', $this->fiscalYearId)
            ->where('expenditure_id', 2)
            ->with('children.children')
            ->get();

        // Compute global x for capital and recurrent
        $this->globalXCapital = ProjectActivity::getRootTotalBudget($this->fiscalYearId);
        $this->globalXRecurrent = (float) ProjectActivity::where('fiscal_year_id', $this->fiscalYearId)
            ->whereNull('parent_id')
            ->where('expenditure_id', 2)
            ->sum('total_budget');

        $data = [];

        // ========== HEADER SECTION (Exact match to image) ==========
        $data[] = ['अनुसूची–१'];
        $data[] = [''];
        $data[] = ['(नियम १६ को उपनियम (१) र (३) सँग सम्बन्धित)'];
        $data[] = [''];
        $data[] = ['वार्षिक बजेट तथा कार्यक्रमको ढाँचा'];
        $data[] = [''];
        $data[] = ['नेपाल सरकार'];
        $data[] = [''];
        $data[] = ['.................. मन्त्रालय/निकाय'];
        $data[] = [''];
        $data[] = ['वार्षिक कार्यक्रम'];
        $data[] = [''];
        $data[] = [''];

        // Form section - 3 columns layout (A-G, H-O, P-Y)
        $formStart = count($data) + 1;

        // Column 1: A-G (indices 0-6)
        // Column 2: H-O (indices 7-14)
        // Column 3: P-Y (indices 15-24)

        // Row 1
        $data[] = [
            '१. आ.व.:–',
            $this->fiscalYear->title ?? '',
            '',
            '',
            '',
            '',
            '',
            '१०. वार्षिक बजेट रु.:',
            '',
            '',
            '',
            '',
            '',
            '',
            '११. कार्यक्रम/आयोजनाको कुल लागत: सुरुको',
            '',
            'संशोधित',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 2
        $data[] = [
            '२. बजेट उपशीर्षक नं.:',
            '',
            '',
            '',
            '',
            '',
            '',
            '(क) आन्तरिक',
            '(१) नेपाल सरकार:',
            '',
            '',
            '',
            '',
            '',
            '(क) आन्तरिक',
            '(१) नेपाल सरकार:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 3
        $data[] = [
            '३. मन्त्रालय/निकाय:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(२) संस्था/निकाय:',
            '',
            '',
            '',
            '',
            '',
            '',
            '(२) संस्था/निकाय:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 4
        $data[] = [
            '४. विभाग/कार्यालय:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(३) जनसहभागिता:',
            '',
            '',
            '',
            '',
            '',
            '',
            '(३) जनसहभागिता:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 5
        $data[] = [
            '५. कार्यक्रम/आयोजनाको नाम:',
            $this->project->title ?? '',
            '',
            '',
            '',
            '',
            '',
            '(ख) वैदेशिक',
            '(१) ऋण:',
            '',
            '',
            'सट्टा दर:',
            '',
            '',
            '(ख) वैदेशिक',
            '(१) ऋण:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 6
        $data[] = [
            '६. स्थान:',
            '(क) जिल्ला:',
            '',
            '',
            '',
            '',
            '',
            '',
            '(ग) मुद्रा:',
            '',
            '',
            '',
            '',
            '',
            '',
            '(२) अनुदान:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 7
        $data[] = [
            '',
            '(ख) गाउँपालिका/नगरपालिका:',
            '',
            '',
            '',
            '',
            '',
            '',
            '(घ) दातृपक्ष/संस्था:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 8
        $data[] = [
            '',
            '(ग) वडा नं.:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '१२. गत आ.व. सम्मको खर्च रु. (सोझै भुक्तानी र वस्तुगत अनुदान समेत)',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 9
        $data[] = [
            '७. कार्यक्रम/आयोजना सुरु भएको मिति:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(क) आन्तरिक',
            '(१) नेपाल सरकार:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 10
        $data[] = [
            '८. कार्यक्रम/आयोजना पूरा हुने मिति:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(२) संस्था/निकाय:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 11
        $data[] = [
            '९. आयोजना/कार्यालय प्रमुखको नाम:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(३) जनसहभागिता:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 12
        $data[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(ख) वैदेशिक',
            '(१) ऋण:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        // Row 13
        $data[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '(२) अनुदान:',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        $data[] = ['']; // spacing

        // Note about amount format
        $noteRow = array_fill(0, 21, '');
        $noteRow[20] = '(रकम रु. हजारमा)';
        $data[] = $noteRow;

        // ========== TABLE SECTION ==========
        $this->tableHeaderRow = count($data) + 1;

        // Main Table Headers
        $data[] = [
            'क्र.सं.',
            'कार्यक्रम/क्रियाकलाप',
            'एकाइ',
            'कार्यक्रम/आयोजनाको कुल क्रियाकलाप',
            '',
            '',
            'सम्पूर्ण कार्य मध्ये गत आर्थिक वर्ष सम्मको',
            '',
            '',
            'वार्षिक लक्ष्य',
            '',
            '',
            'प्रथम त्रैमासिक',
            '',
            '',
            'दोस्रो त्रैमासिक',
            '',
            '',
            'तेस्रो त्रैमासिक',
            '',
            '',
            'चौथो त्रैमासिक',
            '',
            '',
            'कैफियत'
        ];
        $data[] = [
            '',
            '',
            '',
            'परिमाण',
            'लागत',
            'भार',
            'सम्पन्न परिमाण',
            'खर्च',
            'भारित प्रगति',
            'परिमाण',
            'भार',
            'बजेट',
            'परिमाण',
            'भार',
            'बजेट',
            'परिमाण',
            'भार',
            'बजेट',
            'परिमाण',
            'भार',
            'बजेट',
            'परिमाण',
            'भार',
            'बजेट',
            ''
        ];
        $data[] = [
            '१',
            '२',
            '३',
            '४',
            '५',
            '६',
            '७',
            '८',
            '९',
            '१०',
            '११',
            '१२',
            '१३',
            '१४',
            '१५',
            '१६',
            '१७',
            '१८',
            '१९',
            '२०',
            '२१',
            '२२',
            '२३',
            '२४',
            '२५'
        ];
        $data[] = [''];

        // Capital Section
        $capitalTotals = [];
        if ($capitalActivities->isNotEmpty()) {
            $row = count($data) + 1;
            $data[] = ['पूँजीगत खर्च अन्तर्गतका कार्यक्रमहरूः'];
            $this->headerRows[] = $row;

            $activityStartRow = count($data) + 1;
            $data = array_merge($data, $this->buildActivityRows($capitalActivities, 'capital', $activityStartRow, $this->globalXCapital));
            $capitalTotals = $this->calculateOverallTotals($capitalActivities);
            $row = count($data) + 1;
            $data[] = $this->buildTotalRow('(क)', 'पूँजीगत कार्यक्रम हरूको जम्मा', $capitalTotals, $this->globalXCapital, true);
            $this->totalRows[] = $row;
            $data[] = [''];
        }

        // Recurrent Section
        $recurrentTotals = [];
        if ($recurrentActivities->isNotEmpty()) {
            $row = count($data) + 1;
            $data[] = ['चालू खर्च अन्तर्गतका कार्यक्रमहरुः'];
            $this->headerRows[] = $row;

            $activityStartRow = count($data) + 1;
            $data = array_merge($data, $this->buildActivityRows($recurrentActivities, 'recurrent', $activityStartRow, $this->globalXRecurrent));
            $recurrentTotals = $this->calculateOverallTotals($recurrentActivities);
            $row = count($data) + 1;
            $data[] = $this->buildTotalRow('(ख)', 'चालू खर्च अन्तर्गित का कार्यक्रम हरू को जम्मा', $recurrentTotals, $this->globalXRecurrent, false);
            $this->totalRows[] = $row;

            // Recurrent extras
            $zeroTotals = [
                'total_quantity' => 0,
                'completed_quantity' => 0,
                'planned_quantity' => 0,
                'q1_quantity' => 0,
                'q2_quantity' => 0,
                'q3_quantity' => 0,
                'q4_quantity' => 0,
                'total_budget' => 0,
                'total_expense' => 0,
                'planned_budget' => 0,
                'q1' => 0,
                'q2' => 0,
                'q3' => 0,
                'q4' => 0,
                'weighted_expense_contrib' => 0,
                'weighted_planned_contrib' => 0,
                'weighted_q1_contrib' => 0,
                'weighted_q2_contrib' => 0,
                'weighted_q3_contrib' => 0,
                'weighted_q4_contrib' => 0,
            ];
            $row = count($data) + 1;
            $data[] = $this->buildTotalRow('', 'चालूतर्फ उपभोग खर्चको जम्मा', $zeroTotals, $this->globalXRecurrent, false);
            $this->totalRows[] = $row;
            $row = count($data) + 1;
            $data[] = $this->buildTotalRow('', 'चालूतर्फ सञ्चालन खर्चको जम्मा', $zeroTotals, $this->globalXRecurrent, false);
            $this->totalRows[] = $row;
            $data[] = [''];
        }

        // Grand Total
        $globalXGrand = $this->globalXCapital + $this->globalXRecurrent;
        $grandTotals = [
            'total_quantity' => ($capitalTotals['total_quantity'] ?? 0) + ($recurrentTotals['total_quantity'] ?? 0),
            'completed_quantity' => ($capitalTotals['completed_quantity'] ?? 0) + ($recurrentTotals['completed_quantity'] ?? 0),
            'planned_quantity' => ($capitalTotals['planned_quantity'] ?? 0) + ($recurrentTotals['planned_quantity'] ?? 0),
            'q1_quantity' => ($capitalTotals['q1_quantity'] ?? 0) + ($recurrentTotals['q1_quantity'] ?? 0),
            'q2_quantity' => ($capitalTotals['q2_quantity'] ?? 0) + ($recurrentTotals['q2_quantity'] ?? 0),
            'q3_quantity' => ($capitalTotals['q3_quantity'] ?? 0) + ($recurrentTotals['q3_quantity'] ?? 0),
            'q4_quantity' => ($capitalTotals['q4_quantity'] ?? 0) + ($recurrentTotals['q4_quantity'] ?? 0),
            'total_budget' => ($capitalTotals['total_budget'] ?? 0) + ($recurrentTotals['total_budget'] ?? 0),
            'total_expense' => ($capitalTotals['total_expense'] ?? 0) + ($recurrentTotals['total_expense'] ?? 0),
            'planned_budget' => ($capitalTotals['planned_budget'] ?? 0) + ($recurrentTotals['planned_budget'] ?? 0),
            'q1' => ($capitalTotals['q1'] ?? 0) + ($recurrentTotals['q1'] ?? 0),
            'q2' => ($capitalTotals['q2'] ?? 0) + ($recurrentTotals['q2'] ?? 0),
            'q3' => ($capitalTotals['q3'] ?? 0) + ($recurrentTotals['q3'] ?? 0),
            'q4' => ($capitalTotals['q4'] ?? 0) + ($recurrentTotals['q4'] ?? 0),
            'weighted_expense_contrib' => ($capitalTotals['weighted_expense_contrib'] ?? 0) + ($recurrentTotals['weighted_expense_contrib'] ?? 0),
            'weighted_planned_contrib' => ($capitalTotals['weighted_planned_contrib'] ?? 0) + ($recurrentTotals['weighted_planned_contrib'] ?? 0),
            'weighted_q1_contrib' => ($capitalTotals['weighted_q1_contrib'] ?? 0) + ($recurrentTotals['weighted_q1_contrib'] ?? 0),
            'weighted_q2_contrib' => ($capitalTotals['weighted_q2_contrib'] ?? 0) + ($recurrentTotals['weighted_q2_contrib'] ?? 0),
            'weighted_q3_contrib' => ($capitalTotals['weighted_q3_contrib'] ?? 0) + ($recurrentTotals['weighted_q3_contrib'] ?? 0),
            'weighted_q4_contrib' => ($capitalTotals['weighted_q4_contrib'] ?? 0) + ($recurrentTotals['weighted_q4_contrib'] ?? 0),
        ];
        $row = count($data) + 1;
        $data[] = $this->buildTotalRow('(ग)', 'कुल जम्मा (पूँजीगत + चालू)', $grandTotals, $globalXGrand, true);
        $this->totalRows[] = $row;
        $data[] = [''];
        $data[] = [''];

        // Signature Section
        $this->footerStart = count($data) + 1;
        $footerRow1 = array_fill(0, 25, '');
        $footerRow1[0] = 'कार्यालय वा आयोजना प्रमुख:';
        $footerRow1[8] = 'विभागीय वा निकाय प्रमुख:';
        $footerRow1[16] = 'प्रमाणित गर्ने:';
        $data[] = $footerRow1;

        $footerRow2 = array_fill(0, 25, '');
        $footerRow2[0] = 'मिति:';
        $footerRow2[8] = 'मिति:';
        $footerRow2[16] = 'मिति:';
        $data[] = $footerRow2;

        return $data;
    }

    private function buildActivityRows($activities, $expenditureType, $startRow, float $globalX): array
    {
        $rows = [];
        $parentCounter = 1;

        $parentActivities = $activities->filter(function ($activity) {
            return is_null($activity->parent_id);
        });

        foreach ($parentActivities as $parent) {
            $rows[] = $this->buildActivityRow($parent, (string)$parentCounter, $expenditureType, $globalX);

            $children = $parent->children ?? collect();
            $hasChildren = $children->isNotEmpty();

            if ($hasChildren) {
                $childCounter = 1;
                foreach ($children as $child) {
                    $childNumber = $parentCounter . '.' . $childCounter;
                    $rows[] = $this->buildActivityRow($child, $childNumber, $expenditureType, $globalX);

                    $grandchildren = $child->children ?? collect();
                    $grandchildCounter = 1;
                    foreach ($grandchildren as $grandchild) {
                        $grandchildNumber = $parentCounter . '.' . $childCounter . '.' . $grandchildCounter;
                        $rows[] = $this->buildActivityRow($grandchild, $grandchildNumber, $expenditureType, $globalX);
                        $grandchildCounter++;
                    }
                    $childCounter++;
                }

                $parentTotals = $this->calculateTotalsForParent($parent);
                $localRow = count($rows) + 1;
                $rows[] = $this->buildTotalRow('', 'Total of ' . $parentCounter, $parentTotals, $globalX, $expenditureType === 'capital');
                $this->totalRows[] = $startRow + $localRow - 1;
            }

            $parentCounter++;
        }

        return $rows;
    }

    private function buildActivityRow($activity, $number, $expenditureType, float $globalX): array
    {
        $hasChildren = ($activity->children ?? collect())->isNotEmpty();
        $displayTotalQuantity = $hasChildren ? 0.00 : ($activity->total_quantity ?? 0);
        $displayCompletedQuantity = $hasChildren ? 0.00 : ($activity->completed_quantity ?? 0);
        $displayPlannedQuantity = $hasChildren ? 0.00 : ($activity->planned_quantity ?? 0);
        $displayQ1Quantity = $hasChildren ? 0.00 : ($activity->q1_quantity ?? 0);
        $displayQ2Quantity = $hasChildren ? 0.00 : ($activity->q2_quantity ?? 0);
        $displayQ3Quantity = $hasChildren ? 0.00 : ($activity->q3_quantity ?? 0);
        $displayQ4Quantity = $hasChildren ? 0.00 : ($activity->q4_quantity ?? 0);

        $displayTotalBudget = $hasChildren ? 0.00 : ($activity->total_budget ?? 0);
        $displayTotalExpense = $hasChildren ? 0.00 : ($activity->total_expense ?? 0);
        $displayPlannedBudget = $hasChildren ? 0.00 : ($activity->planned_budget ?? 0);
        $displayQ1 = $hasChildren ? 0.00 : ($activity->q1 ?? 0);
        $displayQ2 = $hasChildren ? 0.00 : ($activity->q2 ?? 0);
        $displayQ3 = $hasChildren ? 0.00 : ($activity->q3 ?? 0);
        $displayQ4 = $hasChildren ? 0.00 : ($activity->q4 ?? 0);

        $weightBudget = $hasChildren ? '0.00' : number_format(($activity->total_budget ?? 0) / $globalX * 100, 2);

        $isCapital = $expenditureType === 'capital';
        $weightExpense = $hasChildren || !$isCapital ? '0.00' : number_format($activity->var_total_expense * 100, 2);
        $weightPlanned = $hasChildren || !$isCapital ? '0.00' : number_format($activity->var_planned_budget * 100, 2);
        $weightQ1 = $hasChildren || !$isCapital ? '0.00' : number_format($activity->var_q1 * 100, 2);
        $weightQ2 = $hasChildren || !$isCapital ? '0.00' : number_format($activity->var_q2 * 100, 2);
        $weightQ3 = $hasChildren || !$isCapital ? '0.00' : number_format($activity->var_q3 * 100, 2);
        $weightQ4 = $hasChildren || !$isCapital ? '0.00' : number_format($activity->var_q4 * 100, 2);

        return [
            $number,
            $activity->program ?? '',
            '',
            number_format($displayTotalQuantity, 2),
            number_format($displayTotalBudget / 1000, 0),
            $weightBudget,
            number_format($displayCompletedQuantity, 2),
            number_format($displayTotalExpense / 1000, 0),
            $weightExpense,
            number_format($displayPlannedQuantity, 2),
            $weightPlanned,
            number_format($displayPlannedBudget / 1000, 0),
            number_format($displayQ1Quantity, 2),
            $weightQ1,
            number_format($displayQ1 / 1000, 0),
            number_format($displayQ2Quantity, 2),
            $weightQ2,
            number_format($displayQ2 / 1000, 0),
            number_format($displayQ3Quantity, 2),
            $weightQ3,
            number_format($displayQ3 / 1000, 0),
            number_format($displayQ4Quantity, 2),
            $weightQ4,
            number_format($displayQ4 / 1000, 0),
            ''
        ];
    }

    private function buildTotalRow($prefix, $label, $totals, float $globalX, bool $calculateVar): array
    {
        $budgetWeight = $globalX > 0 ? ($totals['total_budget'] / $globalX) * 100 : 0;
        $expenseWeight = $calculateVar && $globalX > 0 ? ($totals['weighted_expense_contrib'] / $globalX * 100) : 0;
        $plannedWeight = $calculateVar && $globalX > 0 ? ($totals['weighted_planned_contrib'] / $globalX * 100) : 0;
        $q1Weight = $calculateVar && $globalX > 0 ? ($totals['weighted_q1_contrib'] / $globalX * 100) : 0;
        $q2Weight = $calculateVar && $globalX > 0 ? ($totals['weighted_q2_contrib'] / $globalX * 100) : 0;
        $q3Weight = $calculateVar && $globalX > 0 ? ($totals['weighted_q3_contrib'] / $globalX * 100) : 0;
        $q4Weight = $calculateVar && $globalX > 0 ? ($totals['weighted_q4_contrib'] / $globalX * 100) : 0;

        return [
            $prefix ?: '',
            $label,
            '',
            '',
            number_format($totals['total_budget'] / 1000, 0),
            number_format($budgetWeight, 2),
            '',
            number_format($totals['total_expense'] / 1000, 0),
            number_format($expenseWeight, 2),
            '',
            number_format($plannedWeight, 2),
            number_format($totals['planned_budget'] / 1000, 0),
            '',
            number_format($q1Weight, 2),
            number_format($totals['q1'] / 1000, 0),
            '',
            number_format($q2Weight, 2),
            number_format($totals['q2'] / 1000, 0),
            '',
            number_format($q3Weight, 2),
            number_format($totals['q3'] / 1000, 0),
            '',
            number_format($q4Weight, 2),
            number_format($totals['q4'] / 1000, 0),
            ''
        ];
    }

    private function calculateTotalsForParent($parent): array
    {
        $totals = [
            'total_quantity' => 0,
            'completed_quantity' => 0,
            'planned_quantity' => 0,
            'q1_quantity' => 0,
            'q2_quantity' => 0,
            'q3_quantity' => 0,
            'q4_quantity' => 0,
            'total_budget' => 0,
            'total_expense' => 0,
            'planned_budget' => 0,
            'q1' => 0,
            'q2' => 0,
            'q3' => 0,
            'q4' => 0,
            'weighted_expense_contrib' => 0,
            'weighted_planned_contrib' => 0,
            'weighted_q1_contrib' => 0,
            'weighted_q2_contrib' => 0,
            'weighted_q3_contrib' => 0,
            'weighted_q4_contrib' => 0,
        ];
        $this->sumLeafNodes($parent, $totals);
        return $totals;
    }

    private function sumLeafNodes($activity, &$totals): void
    {
        $children = $activity->children ?? collect();
        if ($children->isEmpty()) {
            $totalQuantity = $activity->total_quantity ?? 0;
            $totals['total_quantity'] += $totalQuantity;
            $totals['completed_quantity'] += $activity->completed_quantity ?? 0;
            $totals['planned_quantity'] += $activity->planned_quantity ?? 0;
            $totals['q1_quantity'] += $activity->q1_quantity ?? 0;
            $totals['q2_quantity'] += $activity->q2_quantity ?? 0;
            $totals['q3_quantity'] += $activity->q3_quantity ?? 0;
            $totals['q4_quantity'] += $activity->q4_quantity ?? 0;
            $totals['total_budget'] += $activity->total_budget ?? 0;
            $totals['total_expense'] += $activity->total_expense ?? 0;
            $totals['planned_budget'] += $activity->planned_budget ?? 0;
            $totals['q1'] += $activity->q1 ?? 0;
            $totals['q2'] += $activity->q2 ?? 0;
            $totals['q3'] += $activity->q3 ?? 0;
            $totals['q4'] += $activity->q4 ?? 0;

            if ($totalQuantity > 0) {
                $budget = $activity->total_budget ?? 0;
                $progress = ($activity->completed_quantity ?? 0) / $totalQuantity;
                $totals['weighted_expense_contrib'] += $progress * $budget;

                $plannedProgress = ($activity->planned_quantity ?? 0) / $totalQuantity;
                $totals['weighted_planned_contrib'] += $plannedProgress * $budget;

                $q1Progress = ($activity->q1_quantity ?? 0) / $totalQuantity;
                $totals['weighted_q1_contrib'] += $q1Progress * $budget;

                $q2Progress = ($activity->q2_quantity ?? 0) / $totalQuantity;
                $totals['weighted_q2_contrib'] += $q2Progress * $budget;

                $q3Progress = ($activity->q3_quantity ?? 0) / $totalQuantity;
                $totals['weighted_q3_contrib'] += $q3Progress * $budget;

                $q4Progress = ($activity->q4_quantity ?? 0) / $totalQuantity;
                $totals['weighted_q4_contrib'] += $q4Progress * $budget;
            }
        } else {
            foreach ($children as $child) {
                $this->sumLeafNodes($child, $totals);
            }
        }
    }

    private function calculateOverallTotals($activities): array
    {
        $totals = [
            'total_quantity' => 0,
            'completed_quantity' => 0,
            'planned_quantity' => 0,
            'q1_quantity' => 0,
            'q2_quantity' => 0,
            'q3_quantity' => 0,
            'q4_quantity' => 0,
            'total_budget' => 0,
            'total_expense' => 0,
            'planned_budget' => 0,
            'q1' => 0,
            'q2' => 0,
            'q3' => 0,
            'q4' => 0,
            'weighted_expense_contrib' => 0,
            'weighted_planned_contrib' => 0,
            'weighted_q1_contrib' => 0,
            'weighted_q2_contrib' => 0,
            'weighted_q3_contrib' => 0,
            'weighted_q4_contrib' => 0,
        ];
        $parentActivities = $activities->filter(fn($a) => is_null($a->parent_id));
        foreach ($parentActivities as $parent) {
            $parentTotals = $this->calculateTotalsForParent($parent);
            foreach ($totals as $key => &$value) {
                $value += $parentTotals[$key];
            }
        }
        return $totals;
    }

    public function title(): string
    {
        return 'वार्षिक कार्यक्रम';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true, 'size' => 12, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            7 => ['font' => ['bold' => false, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            9 => ['font' => ['bold' => false, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            11 => ['font' => ['bold' => true, 'size' => 11, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ========== COLUMN WIDTHS (Optimized for A4 Landscape) ==========
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(6);
                $sheet->getColumnDimension('D')->setWidth(7);
                $sheet->getColumnDimension('E')->setWidth(7);
                $sheet->getColumnDimension('F')->setWidth(6);
                $sheet->getColumnDimension('G')->setWidth(7);
                $sheet->getColumnDimension('H')->setWidth(7);
                $sheet->getColumnDimension('I')->setWidth(6);
                $sheet->getColumnDimension('J')->setWidth(7);
                $sheet->getColumnDimension('K')->setWidth(6);
                $sheet->getColumnDimension('L')->setWidth(7);
                $sheet->getColumnDimension('M')->setWidth(7);
                $sheet->getColumnDimension('N')->setWidth(6);
                $sheet->getColumnDimension('O')->setWidth(7);
                $sheet->getColumnDimension('P')->setWidth(7);
                $sheet->getColumnDimension('Q')->setWidth(6);
                $sheet->getColumnDimension('R')->setWidth(7);
                $sheet->getColumnDimension('S')->setWidth(7);
                $sheet->getColumnDimension('T')->setWidth(6);
                $sheet->getColumnDimension('U')->setWidth(7);
                $sheet->getColumnDimension('V')->setWidth(7);
                $sheet->getColumnDimension('W')->setWidth(6);
                $sheet->getColumnDimension('X')->setWidth(7);
                $sheet->getColumnDimension('Y')->setWidth(12);

                // ========== HEADER SECTION STYLING (Rows 1-13) ==========
                // Merge title rows to full width
                $sheet->mergeCells('A1:Y1');   // अनुसूची–१
                $sheet->mergeCells('A3:Y3');   // (नियम...)
                $sheet->mergeCells('A5:Y5');   // वार्षिक बजेट तथा कार्यक्रमको ढाँचा
                $sheet->mergeCells('A7:Y7');   // नेपाल सरकार
                $sheet->mergeCells('A9:Y9');   // मन्त्रालय/निकाय
                $sheet->mergeCells('A11:Y11'); // वार्षिक कार्यक्रम

                // ========== FORM SECTION STYLING ==========
                // Form section styling (rows 14-26 now, reduced from 32)
                $formStart = 14;
                $formEnd = 26;

                // NO BORDERS for form section - keep it clean like the header
                // Just apply text wrapping and alignment
                $sheet->getStyle("A{$formStart}:Y{$formEnd}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A{$formStart}:Y{$formEnd}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                // Merge cells for 3-column layout
                // Row 1: Column 1 has label+value, Column 2 has label, Column 3 has label+values inline
                $sheet->mergeCells("B{$formStart}:G{$formStart}"); // Value for आ.व.
                $sheet->mergeCells("H{$formStart}:O{$formStart}"); // १०. वार्षिक बजेट रु.:
                $sheet->mergeCells("P{$formStart}:Y{$formStart}"); // Full text with सुरुको and संशोधित

                // Row 2
                $sheet->mergeCells("A" . ($formStart + 1) . ":G" . ($formStart + 1)); // २. बजेट उपशीर्षक नं.:
                $sheet->mergeCells("H" . ($formStart + 1) . ":O" . ($formStart + 1)); // (क) आन्तरिक (१) नेपाल सरकार:
                $sheet->mergeCells("P" . ($formStart + 1) . ":Y" . ($formStart + 1)); // (क) आन्तरिक (१) नेपाल सरकार:

                // Row 3
                $sheet->mergeCells("A" . ($formStart + 2) . ":G" . ($formStart + 2)); // ३. मन्त्रालय/निकाय:
                $sheet->mergeCells("H" . ($formStart + 2) . ":O" . ($formStart + 2)); // (२) संस्था/निकाय:
                $sheet->mergeCells("P" . ($formStart + 2) . ":Y" . ($formStart + 2)); // (२) संस्था/निकाय:

                // Row 4
                $sheet->mergeCells("A" . ($formStart + 3) . ":G" . ($formStart + 3)); // ४. विभाग/कार्यालय:
                $sheet->mergeCells("H" . ($formStart + 3) . ":O" . ($formStart + 3)); // (३) जनसहभागिता:
                $sheet->mergeCells("P" . ($formStart + 3) . ":Y" . ($formStart + 3)); // (३) जनसहभागिता:

                // Row 5
                $sheet->mergeCells("B" . ($formStart + 4) . ":G" . ($formStart + 4)); // Project title value
                $sheet->mergeCells("H" . ($formStart + 4) . ":O" . ($formStart + 4)); // (ख) वैदेशिक (१) ऋण: सट्टा दर:
                $sheet->mergeCells("P" . ($formStart + 4) . ":Y" . ($formStart + 4)); // (ख) वैदेशिक (१) ऋण:

                // Row 6
                $sheet->mergeCells("A" . ($formStart + 5) . ":G" . ($formStart + 5)); // ६. स्थान: (क) जिल्ला:
                $sheet->mergeCells("H" . ($formStart + 5) . ":O" . ($formStart + 5)); // (ग) मुद्रा:
                $sheet->mergeCells("P" . ($formStart + 5) . ":Y" . ($formStart + 5)); // (२) अनुदान:

                // Row 7
                $sheet->mergeCells("A" . ($formStart + 6) . ":G" . ($formStart + 6)); // (ख) गाउँपालिका/नगरपालिका:
                $sheet->mergeCells("H" . ($formStart + 6) . ":O" . ($formStart + 6)); // (घ) दातृपक्ष/संस्था:
                $sheet->mergeCells("P" . ($formStart + 6) . ":Y" . ($formStart + 6)); // Empty

                // Row 8
                $sheet->mergeCells("A" . ($formStart + 7) . ":G" . ($formStart + 7)); // (ग) वडा नं.:
                $sheet->mergeCells("H" . ($formStart + 7) . ":O" . ($formStart + 7)); // Empty
                $sheet->mergeCells("P" . ($formStart + 7) . ":Y" . ($formStart + 7)); // १२. गत आ.व. सम्मको खर्च रु...

                // Row 9
                $sheet->mergeCells("A" . ($formStart + 8) . ":G" . ($formStart + 8)); // ७. कार्यक्रम/आयोजना सुरु भएको मिति:
                $sheet->mergeCells("H" . ($formStart + 8) . ":O" . ($formStart + 8)); // Empty
                $sheet->mergeCells("P" . ($formStart + 8) . ":Y" . ($formStart + 8)); // (क) आन्तरिक (१) नेपाल सरकार:

                // Row 10
                $sheet->mergeCells("A" . ($formStart + 9) . ":G" . ($formStart + 9)); // ८. कार्यक्रम/आयोजना पूरा हुने मिति:
                $sheet->mergeCells("H" . ($formStart + 9) . ":O" . ($formStart + 9)); // Empty
                $sheet->mergeCells("P" . ($formStart + 9) . ":Y" . ($formStart + 9)); // (२) संस्था/निकाय:

                // Row 11
                $sheet->mergeCells("A" . ($formStart + 10) . ":G" . ($formStart + 10)); // ९. आयोजना/कार्यालय प्रमुखको नाम:
                $sheet->mergeCells("H" . ($formStart + 10) . ":O" . ($formStart + 10)); // Empty
                $sheet->mergeCells("P" . ($formStart + 10) . ":Y" . ($formStart + 10)); // (३) जनसहभागिता:

                // Row 12
                $sheet->mergeCells("A" . ($formStart + 11) . ":G" . ($formStart + 11)); // Empty
                $sheet->mergeCells("H" . ($formStart + 11) . ":O" . ($formStart + 11)); // Empty
                $sheet->mergeCells("P" . ($formStart + 11) . ":Y" . ($formStart + 11)); // (ख) वैदेशिक (१) ऋण:

                // Row 13
                $sheet->mergeCells("A" . ($formStart + 12) . ":G" . ($formStart + 12)); // Empty
                $sheet->mergeCells("H" . ($formStart + 12) . ":O" . ($formStart + 12)); // Empty
                $sheet->mergeCells("P" . ($formStart + 12) . ":Y" . ($formStart + 12)); // (२) अनुदान:

                // Bold form labels only for primary labels (not sub-items)
                $sheet->getStyle("A{$formStart}")->applyFromArray(['font' => ['bold' => true]]); // १.
                $sheet->getStyle("H{$formStart}")->applyFromArray(['font' => ['bold' => true]]); // १०.
                $sheet->getStyle("P{$formStart}")->applyFromArray(['font' => ['bold' => true]]); // ११.
                $sheet->getStyle("A" . ($formStart + 1))->applyFromArray(['font' => ['bold' => true]]); // २.
                $sheet->getStyle("A" . ($formStart + 2))->applyFromArray(['font' => ['bold' => true]]); // ३.
                $sheet->getStyle("A" . ($formStart + 3))->applyFromArray(['font' => ['bold' => true]]); // ४.
                $sheet->getStyle("A" . ($formStart + 4))->applyFromArray(['font' => ['bold' => true]]); // ५.
                $sheet->getStyle("A" . ($formStart + 5))->applyFromArray(['font' => ['bold' => true]]); // ६.
                $sheet->getStyle("A" . ($formStart + 8))->applyFromArray(['font' => ['bold' => true]]); // ७.
                $sheet->getStyle("A" . ($formStart + 9))->applyFromArray(['font' => ['bold' => true]]); // ८.
                $sheet->getStyle("A" . ($formStart + 10))->applyFromArray(['font' => ['bold' => true]]); // ९.
                $sheet->getStyle("P" . ($formStart + 7))->applyFromArray(['font' => ['bold' => true]]); // १२.

                // Set row heights for form section - taller for wrapped content
                for ($r = $formStart; $r <= $formEnd; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // Note row styling (now row 28 instead of 34)
                $sheet->getStyle('Y28')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);

                // ========== TABLE SECTION STYLING ==========
                $tableStart = $this->tableHeaderRow;

                // Table header merges
                $sheet->mergeCells("A{$tableStart}:A" . ($tableStart + 2));
                $sheet->mergeCells("B{$tableStart}:B" . ($tableStart + 2));
                $sheet->mergeCells("C{$tableStart}:C" . ($tableStart + 2));
                $sheet->mergeCells("D{$tableStart}:F{$tableStart}");
                $sheet->mergeCells("G{$tableStart}:I{$tableStart}");
                $sheet->mergeCells("J{$tableStart}:L{$tableStart}");
                $sheet->mergeCells("M{$tableStart}:O{$tableStart}");
                $sheet->mergeCells("P{$tableStart}:R{$tableStart}");
                $sheet->mergeCells("S{$tableStart}:U{$tableStart}");
                $sheet->mergeCells("V{$tableStart}:X{$tableStart}");
                $sheet->mergeCells("Y{$tableStart}:Y" . ($tableStart + 2));

                // Table header styling
                $sheet->getStyle("A{$tableStart}:Y" . ($tableStart + 2))->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Set header row heights
                $sheet->getRowDimension($tableStart)->setRowHeight(30);
                $sheet->getRowDimension($tableStart + 1)->setRowHeight(30);
                $sheet->getRowDimension($tableStart + 2)->setRowHeight(20);

                // ========== SECTION HEADERS STYLING ==========
                foreach ($this->headerRows as $row) {
                    $sheet->mergeCells("A{$row}:Y{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF2CC']
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(25);
                }

                // ========== TOTAL ROWS STYLING ==========
                foreach ($this->totalRows as $row) {
                    $sheet->getStyle("A{$row}:Y{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E2EFDA']
                        ],
                        'font' => ['bold' => true]
                    ]);
                }

                // ========== TABLE DATA STYLING ==========
                $tableDataStart = $tableStart + 4;
                $borderEndRow = $this->footerStart ? ($this->footerStart - 1) : $sheet->getHighestRow();

                if ($borderEndRow >= $tableDataStart) {
                    // Apply borders to all table data
                    $sheet->getStyle("A{$tableDataStart}:Y{$borderEndRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);

                    // Column alignments
                    $sheet->getStyle("A{$tableDataStart}:A{$borderEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$tableDataStart}:B{$borderEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                    $sheet->getStyle("C{$tableDataStart}:C{$borderEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("Y{$tableDataStart}:Y{$borderEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

                    // Numeric columns right-aligned
                    $numericCols = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'];
                    foreach ($numericCols as $col) {
                        $sheet->getStyle("{$col}{$tableDataStart}:{$col}{$borderEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    // Auto-adjust row heights for wrapped content
                    for ($r = $tableDataStart; $r <= $borderEndRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(-1);
                    }
                }

                // ========== FOOTER SECTION (No borders) ==========
                if ($this->footerStart) {
                    $lastRow = $sheet->getHighestRow();
                    $sheet->getStyle("A{$this->footerStart}:Y{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_NONE]],
                        'font' => ['bold' => true]
                    ]);
                }

                // ========== PAGE SETUP FOR A4 LANDSCAPE ==========
                $lastRow = $sheet->getHighestRow();
                $sheet->getPageSetup()->setPrintArea("A1:Y{$lastRow}");
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

                // Fit to one page
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0); // Allow multiple pages vertically if needed

                // Set margins (in inches)
                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setRight(0.3);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->getPageMargins()->setHeader(0.2);
                $sheet->getPageMargins()->setFooter(0.2);

                // Print settings
                $sheet->getPageSetup()->setHorizontalCentered(true);
                $sheet->setShowGridlines(false);
            },
        ];
    }
}
