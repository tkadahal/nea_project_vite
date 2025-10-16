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
            ['- **वार्षिक बजेट** (E) = Q1 + Q2 + Q3 + Q4 प्रत्येक पङ्क्तिमा **स्वत: गणना हुन्छ**।'],
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
        // Row 1: Project in A1, Fiscal Year in G1
        $row1 = array_fill(0, 9, '');
        $row1[0] = $this->selectedProject ?: '';
        $row1[6] = $this->selectedFiscalYear ?: '';

        // Row 2: Empty
        $row2 = array_fill(0, 9, '');

        // Headers on Row 3
        $headers = [
            'क्र.सं.',
            'कार्यक्रम/क्रियाकलाप',
            'आयोजनाको कुल क्रियाकलापको लागत',
            'गत आ.व. सम्मको खर्च',
            'वार्षिक बजेट',
            'Q1',
            'Q2',
            'Q3',
            'Q4'
        ];

        // Sample hierarchy with formulas as placeholders
        // Parent row (level 0) - will have formulas added in AfterSheet
        $row4 = [1, 'मुख्य कार्यक्रम उदाहरण (अभिभावक)', '', '', '', '', '', '', ''];

        // Child row (level 1) - will have formulas added in AfterSheet
        $row5 = ['1.1', 'उप-कार्यक्रम उदाहरण (सन्तान)', '', '', '', '', '', '', ''];

        // Grandchild row (level 2) - LEAF: user enters data, E=F+G+H+I
        $row6 = ['1.1.1', 'उप-उप-कार्यक्रम उदाहरण (सन्तानको सन्तान)', 30000, 5000, '', 5000, 5000, 0, 0];

        // Another parent (level 0) - LEAF: user enters data, E=F+G+H+I
        $row7 = [2, 'अर्को मुख्य कार्यक्रम', 20000, 3000, '', 5000, 0, 0, 0];

        // Total row (Row 8)
        $totalRow = ['कुल जम्मा', '', '', '', '', '', '', '', ''];

        return collect([$row1, $row2, $headers, $row4, $row5, $row6, $row7, $totalRow]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 40,
            'C' => 15,
            'D' => 18,
            'E' => 15,
            'F' => 8,
            'G' => 8,
            'H' => 8,
            'I' => 8,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = 8; // Row where 'कुल जम्मा' is initially located

                // --- STYLES and BORDERS (Keep these) ---

                // Style metadata (Row 1)
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('G1')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E6F3FF');
                $sheet->getStyle('G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC');

                // Bold headers (A3:I3)
                $headerStyle = $sheet->getStyle('A3:I3');
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Total style
                $totalStyle = $sheet->getStyle('A' . $lastDataRow . ':I' . $lastDataRow);
                $totalStyle->getFont()->setBold(true);
                $totalStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E6E6E6');

                // Right-align numerics
                $sheet->getStyle('C3:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Borders for table
                $sheet->getStyle('A3:I' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Dynamic data rows (from 4 to before total)
                $dataRows = range(4, $lastDataRow - 1);

                // --- FORMULA MODIFICATION ---

                // 1. Keep E = F+G+H+I for ALL data rows (E = Q1 + Q2 + Q3 + Q4)
                foreach ($dataRows as $row) {
                    $sheet->setCellValue('E' . $row, "=F{$row}+G{$row}+H{$row}+I{$row}");
                }

                // 2. Parent/Hierarchy aggregation formulas are REMOVED.

                // 3. Total row formulas (KEEP THIS)
                // This formula calculates the grand total by summing only top-level items (A-column has no dot).
                foreach (['C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
                    $formula = '=SUMPRODUCT((ISNUMBER(VALUE(A4:INDEX(A:A,ROW()-1))))*' .
                        '(ISERROR(FIND(".",A4:INDEX(A:A,ROW()-1))))*' .
                        '(' . $col . '4:INDEX(' . $col . ':' . $col . ',ROW()-1)))';

                    $sheet->setCellValue($col . $lastDataRow, $formula);
                }

                // --- VALIDATION (Keep this) ---
                // Validation for # column (rows 4 to before total)
                for ($row = 4; $row < $lastDataRow; $row++) {
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
                $sheet->setCellValue('A' . ($footerStart + 1), '१. अभिभावक पङ्क्तिहरूमा (जस्तै १, १.१) **मैनुअल रूपमा** सन्तानको योगफल (C, D, F-I कलमहरू) भर्नुपर्नेछ।');
                $sheet->setCellValue('A' . ($footerStart + 2), '२. योजना बजेट (E) = Q1 + Q2 + Q3 + Q4 प्रत्येक पङ्क्तिमा **स्वत: गणना हुन्छ**।');
                $sheet->setCellValue('A' . ($footerStart + 3), '३. अभिभावक = सन्तानहरूको योग **मैनुअल रूपमा** भर्नुपर्नेछ।');
                $sheet->setCellValue('A' . ($footerStart + 4), '४. कुल जम्मा = शीर्ष स्तर पङ्क्तिहरूको योग मात्र (१, २, ३, ...) **स्वत: गणना हुन्छ**।');
                $sheet->setCellValue('A' . ($footerStart + 5), '५. नयाँ पङ्क्ति थप्न: कुल जम्मा पङ्क्तिमाथि नयाँ पङ्क्ति घुसाउनुहोस् र क्र.सं. भर्नुहोस्।');

                $sheet->mergeCells('A' . $footerStart . ':B' . $footerStart);
            },
        ];
    }
}
