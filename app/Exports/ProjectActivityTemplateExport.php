<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Collection;

class ProjectActivityTemplateExport implements WithMultipleSheets
{
    private $selectedProject;
    private $selectedFiscalYear;

    public function __construct(?string $selectedProject = null, ?string $selectedFiscalYear = null)
    {
        $this->selectedProject = $selectedProject;
        $this->selectedFiscalYear = $selectedFiscalYear;
    }

    public function sheets(): array
    {
        return [
            'Instructions' => new InstructionsSheet(),
            'पूँजीगत खर्च' => new ExpenditureSheet('पूँजीगत', $this->selectedProject, $this->selectedFiscalYear),
            'चालू खर्च' => new ExpenditureSheet('चालू', $this->selectedProject, $this->selectedFiscalYear),
        ];
    }
}

class InstructionsSheet implements FromCollection, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return collect([
            ['प्रोजेक्ट क्रियाकलाप एक्सेल टेम्प्लेट'],
            [],
            ['अवलोकन:'],
            ['पूँजीगत खर्च र चालू खर्च शीटहरूमा डाटा भर्नुहोस्।'],
            ['# कलममा पदानुक्रमको लागि प्रयोग गर्नुहोस् (उदाहरण: १, १.१, १.१.१)।'],
            [],
            ['मान्यता नियमहरू:'],
            ['- **वार्षिक बजेट** (H) = Q1 + Q2 + Q3 + Q4 (J+L+N+P) प्रत्येक पङ्क्तिमा **स्वत: गणना हुन्छ**।'],
            ['- अभिभावक पङ्क्तिहरू (जस्तै १, १.१) ले प्रत्यक्ष सन्तानहरूको योग समान हुनुपर्छ, र यो **मैनुअल रूपमा** जाँच गरी भर्नुपर्नेछ।'],
            ['- सबै अङ्कहरू गैर-नकारात्मक।'],
            [],
            ['प्रयोग:'],
            ['१. हेडरहरू मुनि पङ्क्तिहरू थप्नुहोस् र डाटा भर्नुहोस्।'],
            ['२. .xlsx को रूपमा बचत गर्नुहोस्।'],
            ['३. एपमा अपलोड गर्नुहोस्।'],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A7')->getFont()->setBold(true);
        $sheet->getStyle('A12')->getFont()->setBold(true);
        $sheet->getStyle('A1:A15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        return [];
    }

    public function columnWidths(): array
    {
        return ['A' => 60];
    }
}

class ExpenditureSheet implements FromCollection, WithTitle, WithColumnWidths, WithEvents
{
    private $type;
    private $selectedProject;
    private $selectedFiscalYear;

    public function __construct(string $type, ?string $selectedProject = null, ?string $selectedFiscalYear = null)
    {
        $this->type = $type;
        $this->selectedProject = $selectedProject;
        $this->selectedFiscalYear = $selectedFiscalYear;
    }

    public function title(): string
    {
        return $this->type . ' खर्च';
    }

    public function collection()
    {
        // Row 1: Project in A1, Fiscal Year in H1 (Planned Budget Amount position)
        $row1 = array_fill(0, 15, '');
        $row1[0] = $this->selectedProject ?: '';
        $row1[7] = $this->selectedFiscalYear ?: '';

        // Row 2: Empty
        $row2 = array_fill(0, 15, '');

        // Headers on Row 3: 16 columns (A to P)
        $headers = [
            'क्र.सं.',
            'कार्यक्रम/क्रियाकलाप',
            'कुल बजेट',
            '',     // C:D (will be merged)
            'कुल खर्च',
            '',     // E:F
            'वार्षिक बजेट',
            '', // G:H
            'Q1',
            '',           // I:J
            'Q2',
            '',           // K:L
            'Q3',
            '',           // M:N
            'Q4',
            '',           // O:P
        ];

        // Empty row for sub-headers (Row 4, A and B will be empty after merge)
        $row4 = array_fill(0, 15, '');

        // Sample hierarchy with formulas as placeholders
        // Parent row (level 0) - manual for sums
        $row5 = [1, 'मुख्य कार्यक्रम उदाहरण (अभिभावक)', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Child row (level 1) - manual for sums
        $row6 = ['1.1', 'उप-कार्यक्रम उदाहरण (सन्तान)', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Grandchild row (level 2) - LEAF: user enters data, H=J+L+N+P
        $row7 = ['1.1.1', 'उप-उप-कार्यक्रम उदाहरण (सन्तानको सन्तान)', 30, 30000, 5, 5000, '', '', 5, 5000, 5, 5000, 0, 0, 0, 0];

        // Another parent (level 0) - LEAF: user enters data, H=J+L+N+P
        $row8 = [2, 'अर्को मुख्य कार्यक्रम', 20, 20000, 3, 3000, '', '', 5, 5000, 0, 0, 0, 0, 0, 0];

        // Total row (Row 9)
        $totalRow = ['कुल जम्मा', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        return collect([$row1, $row2, $headers, $row4, $row5, $row6, $row7, $row8, $totalRow]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // #
            'B' => 40,  // Program
            'C' => 6,   // Total Budget Qty
            'D' => 12,  // Total Budget Amount
            'E' => 6,   // Total Expense Qty
            'F' => 12,  // Total Expense Amount
            'G' => 6,   // Planned Budget Qty
            'H' => 12,  // Planned Budget Amount
            'I' => 6,   // Q1 Qty
            'J' => 8,   // Q1 Amount
            'K' => 6,   // Q2 Qty
            'L' => 8,   // Q2 Amount
            'M' => 6,   // Q3 Qty
            'N' => 8,   // Q3 Amount
            'O' => 6,   // Q4 Qty
            'P' => 8,   // Q4 Amount
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set page setup for A4 printing (Portrait orientation for better fit)
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageMargins()->setHeader(0.5);
                $sheet->getPageMargins()->setFooter(0.5);
                $sheet->getPageMargins()->setLeft(0.7);
                $sheet->getPageMargins()->setRight(0.7);
                $sheet->getPageMargins()->setTop(0.75);
                $sheet->getPageMargins()->setBottom(0.75);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0); // Allow multi-page vertically

                // --- STYLES and BORDERS ---

                // Style metadata (Row 1)
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('H1')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E6F3FF');
                $sheet->getStyle('H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC');

                // Bold headers (A3:P3) - centered
                $headerStyle = $sheet->getStyle('A3:P3');
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Merge A3:A4 and B3:B4 for two-row spanning headers - center both
                $sheet->mergeCells('A3:A4');
                $sheet->mergeCells('B3:B4');
                $sheet->getStyle('A3:A4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B3:B4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Merge paired headers and center them
                $mergedRanges = ['C3:D3', 'E3:F3', 'G3:H3', 'I3:J3', 'K3:L3', 'M3:N3', 'O3:P3'];
                foreach ($mergedRanges as $range) {
                    $sheet->mergeCells($range);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Sub-headers for Qty/Amount on Row 4 (C to P) - centered
                $subHeadersRow = 4;
                $qtySub = ['परिमाण', 'रकम', 'परिमाण', 'रकम', 'परिमाण', 'रकम', 'परिमाण', 'रकम', 'परिमाण', 'रकम', 'परिमाण', 'रकम', 'परिमाण', 'रकम'];
                for ($pair = 0; $pair < 7; $pair++) {
                    $col = $pair * 2 + 3; // Starting from column 3 (C)
                    $sheet->setCellValueByColumnAndRow($col, $subHeadersRow, $qtySub[$pair * 2]);
                    $sheet->setCellValueByColumnAndRow($col + 1, $subHeadersRow, $qtySub[$pair * 2 + 1]);
                }
                $subHeaderStyle = $sheet->getStyle('C4:P4');
                $subHeaderStyle->getFont()->setBold(true);
                $subHeaderStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $subHeaderStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('DDDDDD');

                // Ensure A4 and B4 are empty (for merges)
                $sheet->setCellValue('A4', '');
                $sheet->setCellValue('B4', '');

                // Sample data starts at row 5
                $sampleDataStart = 5;
                // Parent row (level 0) - manual for sums
                $sheet->setCellValue('A' . $sampleDataStart, 1);
                $sheet->setCellValue('B' . $sampleDataStart, 'मुख्य कार्यक्रम उदाहरण (अभिभावक)');
                // Child row (level 1) - manual for sums
                $sheet->setCellValue('A' . ($sampleDataStart + 1), '1.1');
                $sheet->setCellValue('B' . ($sampleDataStart + 1), 'उप-कार्यक्रम उदाहरण (सन्तान)');
                // Grandchild row (level 2) - LEAF
                $sheet->setCellValue('A' . ($sampleDataStart + 2), '1.1.1');
                $sheet->setCellValue('B' . ($sampleDataStart + 2), 'उप-उप-कार्यक्रम उदाहरण (सन्तानको सन्तान)');
                $sheet->setCellValue('C' . ($sampleDataStart + 2), 30);
                $sheet->setCellValue('D' . ($sampleDataStart + 2), 30000);
                $sheet->setCellValue('E' . ($sampleDataStart + 2), 5);
                $sheet->setCellValue('F' . ($sampleDataStart + 2), 5000);
                $sheet->setCellValue('I' . ($sampleDataStart + 2), 5);
                $sheet->setCellValue('J' . ($sampleDataStart + 2), 5000);
                $sheet->setCellValue('K' . ($sampleDataStart + 2), 5);
                $sheet->setCellValue('L' . ($sampleDataStart + 2), 5000);
                $sheet->setCellValue('M' . ($sampleDataStart + 2), 0);
                $sheet->setCellValue('N' . ($sampleDataStart + 2), 0);
                $sheet->setCellValue('O' . ($sampleDataStart + 2), 0);
                $sheet->setCellValue('P' . ($sampleDataStart + 2), 0);
                // Another parent (level 0) - LEAF
                $sheet->setCellValue('A' . ($sampleDataStart + 3), 2);
                $sheet->setCellValue('B' . ($sampleDataStart + 3), 'अर्को मुख्य कार्यक्रम');
                $sheet->setCellValue('C' . ($sampleDataStart + 3), 20);
                $sheet->setCellValue('D' . ($sampleDataStart + 3), 20000);
                $sheet->setCellValue('E' . ($sampleDataStart + 3), 3);
                $sheet->setCellValue('F' . ($sampleDataStart + 3), 3000);
                $sheet->setCellValue('I' . ($sampleDataStart + 3), 5);
                $sheet->setCellValue('J' . ($sampleDataStart + 3), 5000);
                $sheet->setCellValue('K' . ($sampleDataStart + 3), 0);
                $sheet->setCellValue('L' . ($sampleDataStart + 3), 0);
                $sheet->setCellValue('M' . ($sampleDataStart + 3), 0);
                $sheet->setCellValue('N' . ($sampleDataStart + 3), 0);
                $sheet->setCellValue('O' . ($sampleDataStart + 3), 0);
                $sheet->setCellValue('P' . ($sampleDataStart + 3), 0);

                // Update lastDataRow to account for shifted rows - total at row 9
                $lastDataRow = 9;

                // Total row
                $sheet->setCellValue('A' . $lastDataRow, 'कुल जम्मा');

                // Total style
                $totalStyle = $sheet->getStyle('A' . $lastDataRow . ':P' . $lastDataRow);
                $totalStyle->getFont()->setBold(true);
                $totalStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E6E6E6');

                // Right-align numerics (C3:P + data rows)
                $sheet->getStyle('C3:P' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Borders for table (A3:P lastDataRow)
                $sheet->getStyle('A3:P' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Dynamic data rows (from sampleDataStart to before total)
                $dataRows = range(5, $lastDataRow - 1);

                // --- FORMULA MODIFICATION ---

                // 1. Keep H = J + L + N + P for ALL data rows (Planned Amount = sum Q Amounts)
                foreach ($dataRows as $row) {
                    $sheet->setCellValue('H' . $row, "=J{$row}+L{$row}+N{$row}+P{$row}");
                }

                // 2. Parent/Hierarchy aggregation formulas are REMOVED (manual).

                // 3. Total row formulas (sum top-level only for C,D,E,F,G,H,I,J,K,L,M,N,O,P) - DYNAMIC SUMPRODUCT FROM OLD CODE
                $qtyCols = ['C', 'E', 'G', 'I', 'K', 'M', 'O']; // Qty columns
                $amtCols = ['D', 'F', 'H', 'J', 'L', 'N', 'P']; // Amount columns
                $dataRangeStart = 5; // Fixed start row
                foreach ($amtCols as $col) {
                    $formula = "=SUMPRODUCT((ISNUMBER(VALUE(A{$dataRangeStart}:INDEX(A:A,ROW()-1))))*(ISERROR(FIND(\".\",A{$dataRangeStart}:INDEX(A:A,ROW()-1))))*({$col}{$dataRangeStart}:INDEX({$col}:{$col},ROW()-1)))";
                    $sheet->setCellValue($col . $lastDataRow, $formula);
                }

                // --- VALIDATION ---
                // Validation for # column (rows sampleDataStart to before total) - extend to large range for added rows
                for ($row = 5; $row <= 1000; $row++) {
                    $cell = $sheet->getCell('A' . $row);
                    $validation = $cell->getDataValidation();
                    $validation->setType(DataValidation::TYPE_CUSTOM);
                    $validation->setFormula1('=AND(ISNUMBER(VALUE(SUBSTITUTE(A' . $row . ',".",""))),LEN(A' . $row . ')>0)');
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('अमान्य #');
                    $validation->setError('१ जस्तो अङ्क वा १.१ जस्तो पदानुक्रम प्रयोग गर्नुहोस्।');
                }

                // --- FOOTER NOTES (Updated) ---
                $footerStart = $lastDataRow + 2;
                $sheet->setCellValue('A' . $footerStart, 'नोट:');
                $sheet->getStyle('A' . $footerStart)->getFont()->setBold(true);
                $sheet->setCellValue('A' . ($footerStart + 1), '१. अभिभावक पङ्क्तिहरूमा (जस्तै १, १.१) **मैनुअल रूपमा** सन्तानको योगफल (C,D,E,F,G,H,I,J,K,L,M,N,O,P कलमहरू) भर्नुपर्नेछ।');
                $sheet->setCellValue('A' . ($footerStart + 2), '२. वार्षिक बजेट (H) = Q1 + Q2 + Q3 + Q4 (J+L+N+P) प्रत्येक पङ्क्तिमा **स्वत: गणना हुन्छ**।');
                $sheet->setCellValue('A' . ($footerStart + 3), '३. अभिभावक = सन्तानहरूको योग **मैनुअल रूपमा** भर्नुपर्नेछ।');
                $sheet->setCellValue('A' . ($footerStart + 4), '४. कुल जम्मा = शीर्ष स्तर पङ्क्तिहरूको योग मात्र (१, २, ३, ...) **स्वत: गणना हुन्छ**।');
                $sheet->setCellValue('A' . ($footerStart + 5), '५. नयाँ पङ्क्ति थप्न: कुल जम्मा पङ्क्तिमाथि नयाँ पङ्क्ति घुसाउनुहोस् र क्र.सं. भर्नुहोस्।');

                $sheet->mergeCells('A' . $footerStart . ':B' . $footerStart);
            },
        ];
    }
}
