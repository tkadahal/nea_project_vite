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
    protected $tableHeaderRow;

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

        // ========== HEADER SECTION (Exactly as per image) ==========
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

        // Form section rows 14-26: Recreate exact structure from image
        // Row 14
        $row14 = array_fill(0, 25, '');
        $row14[0] = '१. आ.व.:–';
        $row14[1] = $this->fiscalYear->title ?? '';
        $row14[7] = '१०. वार्षिक बजेट रु.:';
        $row14[15] = '११. कार्यक्रम/आयोजनाको कुल लागत:';
        $data[] = $row14;

        // Row 15
        $row15 = array_fill(0, 25, '');
        $row15[0] = '२. बजेट उपशीर्षक नं.:';
        $row15[7] = '(क) आन्तरिक';
        $row15[15] = '(क) आन्तरिक';
        $data[] = $row15;

        // Row 16
        $row16 = array_fill(0, 25, '');
        $row16[0] = '३. मन्त्रालय/निकाय:';
        $row16[7] = '(१) नेपाल सरकार:';
        $row16[15] = '(१) नेपाल सरकार:';
        $data[] = $row16;

        // Row 17
        $row17 = array_fill(0, 25, '');
        $row17[0] = '४. विभाग/कार्यालय:';
        $row17[7] = '(२) संस्था/निकाय:';
        $row17[15] = '(२) संस्था/निकाय:';
        $data[] = $row17;

        // Row 18
        $row18 = array_fill(0, 25, '');
        $row18[0] = '५. कार्यक्रम/आयोजनाको नाम:';
        $row18[1] = $this->project->title ?? '';
        $row18[7] = '(३) जनसहभागिता:';
        $row18[15] = '(३) जनसहभागिता:';
        $data[] = $row18;

        // Row 19
        $row19 = array_fill(0, 25, '');
        $row19[0] = '६. स्थान:';
        $row19[1] = '(क) जिल्ला:';
        $row19[7] = '(ख) वैदेशिक';
        $row19[15] = '(ख) वैदेशिक';
        $data[] = $row19;

        // Row 20
        $row20 = array_fill(0, 25, '');
        $row20[1] = '(ख) गाउँपालिका/नगरपालिका:';
        $row20[7] = '(१) ऋण:';
        $row20[15] = '(१) ऋण:';
        $data[] = $row20;

        // Row 21
        $row21 = array_fill(0, 25, '');
        $row21[1] = '(ग) वडा नं.:';
        $row21[7] = 'सट्टा दर:';
        $data[] = $row21;

        // Row 22
        $row22 = array_fill(0, 25, '');
        $row22[0] = '७. कार्यक्रम/आयोजना सुरु भएको मिति:';
        $row22[7] = '(ग) मुद्रा:';
        $row22[15] = '(२) अनुदान:';
        $data[] = $row22;

        // Row 23
        $row23 = array_fill(0, 25, '');
        $row23[0] = '८. कार्यक्रम/आयोजना पूरा हुने मिति:';
        $row23[7] = '(घ) दातृपक्ष/संस्था:';
        $data[] = $row23;

        // Row 24
        $row24 = array_fill(0, 25, '');
        $row24[0] = '९. आयोजना/कार्यालय प्रमुखको नाम:';
        $row24[15] = '१२. गत आ.व. सम्मको खर्च रु. (सोझै भुक्तानी र वस्तुगत अनुदान समेत)';
        $data[] = $row24;

        // Row 25
        $row25 = array_fill(0, 25, '');
        $row25[15] = '(क) आन्तरिक';
        $data[] = $row25;

        // Row 26
        $row26 = array_fill(0, 25, '');
        $row26[15] = '(१) नेपाल सरकार:';
        $data[] = $row26;

        // Row 27
        $row27 = array_fill(0, 25, '');
        $row27[15] = '(२) संस्था/निकाय:';
        $data[] = $row27;

        // Row 28
        $row28 = array_fill(0, 25, '');
        $row28[15] = '(३) जनसहभागिता:';
        $data[] = $row28;

        // Row 29
        $row29 = array_fill(0, 25, '');
        $row29[15] = '(ख) वैदेशिक';
        $data[] = $row29;

        // Row 30
        $row30 = array_fill(0, 25, '');
        $row30[15] = '(१) ऋण:';
        $data[] = $row30;

        // Row 31
        $row31 = array_fill(0, 25, '');
        $row31[15] = '(२) अनुदान:';
        $data[] = $row31;

        $data[] = ['']; // spacing

        // Note about amount format
        $noteRow = array_fill(0, 25, '');
        $noteRow[24] = '(रकम रु. हजारमा)';
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
        $data[] = $this->buildTotalRow('(ग)', 'कुल जम्मा (पूँजीगत + चालू)', $grandTotals, $globalXGrand, false);
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

            if ($totalQuantity > 0 && $activity->expenditure_id == 1) {
                $budget = $activity->total_budget ?? 0;
                $progress = ($activity->completed_quantity ?? 0) / $totalQuantity;
                $totals['weighted_expense_contrib'] += $progress * $budget;

                $plannedProgress = ($activity->planned_quantity ?? 0) / $totalQuantity;
                $totals['weighted_planned_contrib'] += $plannedProgress * $budget;

                $plannedQuantity = $activity->planned_quantity ?? 0;
                if ($plannedQuantity > 0) {
                    $q1Progress = ($activity->q1_quantity ?? 0) / $totalQuantity;
                    $totals['weighted_q1_contrib'] += $q1Progress * $budget;

                    $q2Progress = ($activity->q2_quantity ?? 0) / $totalQuantity;
                    $totals['weighted_q2_contrib'] += $q2Progress * $budget;

                    $q3Progress = ($activity->q3_quantity ?? 0) / $totalQuantity;
                    $totals['weighted_q3_contrib'] += $q3Progress * $budget;

                    $q4Progress = ($activity->q4_quantity ?? 0) / $totalQuantity;
                    $totals['weighted_q4_contrib'] += $q4Progress * $budget;
                }
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
            1 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true, 'size' => 11, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            7 => ['font' => ['bold' => false, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            9 => ['font' => ['bold' => false, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            11 => ['font' => ['bold' => true, 'size' => 10, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ========== COLUMN WIDTHS (Optimized for A4 Landscape) ==========
                $sheet->getColumnDimension('A')->setWidth(4.5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(5);
                $sheet->getColumnDimension('D')->setWidth(6);
                $sheet->getColumnDimension('E')->setWidth(6);
                $sheet->getColumnDimension('F')->setWidth(5);
                $sheet->getColumnDimension('G')->setWidth(6);
                $sheet->getColumnDimension('H')->setWidth(6);
                $sheet->getColumnDimension('I')->setWidth(5);
                $sheet->getColumnDimension('J')->setWidth(6);
                $sheet->getColumnDimension('K')->setWidth(5);
                $sheet->getColumnDimension('L')->setWidth(6);
                $sheet->getColumnDimension('M')->setWidth(6);
                $sheet->getColumnDimension('N')->setWidth(5);
                $sheet->getColumnDimension('O')->setWidth(6);
                $sheet->getColumnDimension('P')->setWidth(6);
                $sheet->getColumnDimension('Q')->setWidth(5);
                $sheet->getColumnDimension('R')->setWidth(6);
                $sheet->getColumnDimension('S')->setWidth(6);
                $sheet->getColumnDimension('T')->setWidth(5);
                $sheet->getColumnDimension('U')->setWidth(6);
                $sheet->getColumnDimension('V')->setWidth(6);
                $sheet->getColumnDimension('W')->setWidth(5);
                $sheet->getColumnDimension('X')->setWidth(6);
                $sheet->getColumnDimension('Y')->setWidth(10);

                // ========== HEADER SECTION STYLING (Rows 1-13) ==========
                $sheet->mergeCells('A1:Y1');
                $sheet->mergeCells('A3:Y3');
                $sheet->mergeCells('A5:Y5');
                $sheet->mergeCells('A7:Y7');
                $sheet->mergeCells('A9:Y9');
                $sheet->mergeCells('A11:Y11');

                // ========== FORM SECTION STYLING (Rows 14-31) ==========
                $formStart = 14;
                $formEnd = 31;

                // Apply wrapping and top alignment to all form cells
                $sheet->getStyle("A{$formStart}:Y{$formEnd}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A{$formStart}:Y{$formEnd}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("A{$formStart}:Y{$formEnd}")->getFont()->setSize(9);

                // Merge columns for form section
                for ($r = $formStart; $r <= $formEnd; $r++) {
                    $sheet->mergeCells("A{$r}:G{$r}");
                    $sheet->mergeCells("H{$r}:O{$r}");
                    $sheet->mergeCells("P{$r}:Y{$r}");
                }

                // Bold primary labels
                $boldRows = [14, 15, 16, 17, 18, 22, 23, 24];
                foreach ($boldRows as $row) {
                    $sheet->getStyle("A{$row}")->applyFromArray(['font' => ['bold' => true, 'size' => 9]]);
                }
                $sheet->getStyle("H14")->applyFromArray(['font' => ['bold' => true, 'size' => 9]]);
                $sheet->getStyle("P14")->applyFromArray(['font' => ['bold' => true, 'size' => 9]]);
                $sheet->getStyle("P24")->applyFromArray(['font' => ['bold' => true, 'size' => 9]]);

                // Set consistent row heights for form section
                for ($r = $formStart; $r <= $formEnd; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(24);
                }

                // Note row
                $noteRowNum = 33;
                $sheet->getStyle("Y{$noteRowNum}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 8],
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
                    'font' => ['bold' => true, 'size' => 8],
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
                $sheet->getRowDimension($tableStart)->setRowHeight(28);
                $sheet->getRowDimension($tableStart + 1)->setRowHeight(28);
                $sheet->getRowDimension($tableStart + 2)->setRowHeight(18);

                // ========== SECTION HEADERS STYLING ==========
                foreach ($this->headerRows as $row) {
                    $sheet->mergeCells("A{$row}:Y{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9],
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
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                // ========== TOTAL ROWS STYLING ==========
                foreach ($this->totalRows as $row) {
                    $sheet->getStyle("A{$row}:Y{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E2EFDA']
                        ],
                        'font' => ['bold' => true, 'size' => 8]
                    ]);
                }

                // ========== TABLE DATA STYLING ==========
                $tableDataStart = $tableStart + 4;
                $borderEndRow = $this->footerStart ? ($this->footerStart - 1) : $sheet->getHighestRow();

                if ($borderEndRow >= $tableDataStart) {
                    // Apply smaller font to all table data
                    $sheet->getStyle("A{$tableDataStart}:Y{$borderEndRow}")->getFont()->setSize(8);

                    // Apply borders
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

                    // Set consistent row heights for data rows
                    for ($r = $tableDataStart; $r <= $borderEndRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(20);
                    }
                }

                // ========== FOOTER SECTION ==========
                if ($this->footerStart) {
                    $lastRow = $sheet->getHighestRow();
                    $sheet->getStyle("A{$this->footerStart}:Y{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_NONE]],
                        'font' => ['bold' => true, 'size' => 9]
                    ]);

                    // Set footer row heights
                    for ($r = $this->footerStart; $r <= $lastRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(22);
                    }
                }

                // ========== PAGE SETUP FOR A4 LANDSCAPE ==========
                $lastRow = $sheet->getHighestRow();
                $sheet->getPageSetup()->setPrintArea("A1:Y{$lastRow}");
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

                // Fit to one page width
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Set margins (in inches) - narrower for better fit
                $sheet->getPageMargins()->setTop(0.3);
                $sheet->getPageMargins()->setRight(0.25);
                $sheet->getPageMargins()->setLeft(0.25);
                $sheet->getPageMargins()->setBottom(0.3);
                $sheet->getPageMargins()->setHeader(0.15);
                $sheet->getPageMargins()->setFooter(0.15);

                // Print settings
                $sheet->getPageSetup()->setHorizontalCentered(true);
                $sheet->setShowGridlines(false);
            },
        ];
    }
}
