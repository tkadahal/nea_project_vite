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
            'पूँजीगत खर्च' => new ExpenditureSheet('पूँजीगत', $this->selectedProject, $this->selectedFiscalYear), // Capital
            'चालू खर्च' => new ExpenditureSheet('चालू', $this->selectedProject, $this->selectedFiscalYear), // Recurrent
        ];
    }
}

class InstructionsSheet implements FromCollection, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return collect([
            ['प्रोजेक्ट क्रियाकलाप एक्सेल टेम्प्लेट'], // Row 1
            [], // Row 2 (empty)
            ['अवलोकन:'], // Row 3
            ['पूँजीगत खर्च र चालू खर्च शीटहरूमा डाटा भर्नुहोस्।'], // Row 4
            ['# कलममा पदानुक्रमको लागि प्रयोग गर्नुहोस् (उदाहरण: १, १.१, १.१.१)।'], // Row 5
            [], // Row 6 (empty)
            ['मान्यता नियमहरू:'], // Row 7
            ['- योजना बजेट = Q1 + Q2 + Q3 + Q4 प्रत्येक पङ्क्तिमा।'], // Row 8
            ['- अभिभावक पङ्क्तिहरूले प्रत्यक्ष सन्तानहरूको योग समान हुनुपर्छ।'], // Row 9
            ['- सबै अङ्कहरू गैर-नकारात्मक।'], // Row 10
            [], // Row 11 (empty)
            ['प्रयोग:'], // Row 12
            ['१. हेडरहरू मुनि पङ्क्तिहरू थप्नुहोस् र डाटा भर्नुहोस्।'], // Row 13
            ['२. .xlsx को रूपमा बचत गर्नुहोस्।'], // Row 14
            ['३. एपमा अपलोड गर्नुहोस्।'], // Row 15
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
        // Row 1: Project in A1, Fiscal Year in G1 (right side)
        $row1 = array_fill(0, 6, ''); // Empty A-F
        $row1[0] = $this->selectedProject ?: ''; // A1: Project
        $row1[6] = $this->selectedFiscalYear ?: ''; // G1: Fiscal Year

        // Row 2: Empty
        $row2 = array_fill(0, 8, '');

        // Headers on Row 3
        $headers = [
            'क्र.सं.', // A: #
            'कार्यक्रम/क्रियाकलाप', // B: Program
            'आयोजनाको कुल क्रियाकलापको लागत', // C: Total Budget
            'गत आ.व. सम्मको खर्च', // D: Expenses Till Date
            'वार्षिक बजेट', // E: Planned Budget FY (auto-sum of quarters OR children)
            'Q1', // F
            'Q2', // G
            'Q3', // H
            'Q4' // I
        ];

        // Sample hierarchy (Rows 4-8)
        // Adjust formulas to reference new row numbers
        $row4 = [1, 'मुख्य कार्यक्रम उदाहरण (अभिभावक)', '=C5+C6', '=D5+D6', '=SUM(F4:I4)', '=F5+F6', '=G5+G6', '=H5+H6', '=I5+I6'];

        $row5 = ['1.1', 'उप-कार्यक्रम उदाहरण (सन्तान)', '=C6', '=D6', '=SUM(F5:I5)', '=F6', 10000, 0, 0]; // Sample own Q2=10000

        $row6 = ['1.1.1', 'उप-उप-कार्यक्रम उदाहरण (सन्तानको सन्तान)', 30000, 5000, '=SUM(F6:I6)', 5000, 5000, 0, 0]; // Sample Q1+Q2=10000, Total=30000, Expense=5000

        $row7 = [2, 'अर्को मुख्य कार्यक्रम', 20000, 3000, '=SUM(F7:I7)', 5000, 0, 0, 0]; // Sample Q1=5000

        // Total row (Row 9)
        $totalRow = ['कुल जम्मा', '', '=SUM(C4:C7)', '=SUM(D4:D7)', '=SUM(E4:E7)', '=SUM(F4:F7)', '=SUM(G4:G7)', '=SUM(H4:H7)', '=SUM(I4:I7)'];

        return collect([$row1, $row2, $headers, $row4, $row5, $row6, $row7, $totalRow]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // क्र.सं.
            'B' => 40,  // Program
            'C' => 15,  // Total Budget
            'D' => 18,  // Expenses Till Date
            'E' => 15,  // Planned Budget
            'F' => 8,   // Q1
            'G' => 8,   // Q2
            'H' => 8,   // Q3
            'I' => 8,   // Q4
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow(); // e.g., now 9 + footer

                // Style metadata (Row 1)
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('G1')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E6F3FF'); // Light blue for project
                $sheet->getStyle('G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC'); // Light yellow for FY

                // Bold headers (A3:I3)
                $headerStyle = $sheet->getStyle('A3:I3');
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('CCCCCC');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Total style (A9:I9)
                $totalStyle = $sheet->getStyle('A9:I9');
                $totalStyle->getFont()->setBold(true);
                $totalStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E6E6E6');

                // Right-align numerics (C3:I lastRow)
                $sheet->getStyle('C3:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Borders for table (starting from A3)
                $sheet->getStyle('A3:I9')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Validation for # column (rows 4-8)
                for ($row = 4; $row <= 8; $row++) { // Exclude metadata, headers, total
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

                // Footer notes (start after total row 9 + empty 10)
                $footerStart = 12; // After total 9 + empty 10,11
                $sheet->setCellValue('A' . $footerStart, 'नोट:');
                $sheet->getStyle('A' . $footerStart)->getFont()->setBold(true);
                $sheet->setCellValue('A' . ($footerStart + 1), '१. सन्तानहरूमा मान भर्नुहोस्—अभिभावकहरू स्वत: योग गणना गर्छन् (C, D, F-I मा फर्मुला)।');
                $sheet->setCellValue('A' . ($footerStart + 2), '२. योजना बजेट (E) = Q1 + Q2 + Q3 + Q4 प्रत्येक पङ्क्तिमा स्वत: गणना हुन्छ।');
                $sheet->setCellValue('A' . ($footerStart + 3), '३. पदानुक्रमको लागि # कलम प्रयोग गर्नुहोस् (अधिकतम गहिराइ २); नयाँ पङ्क्ति थप्दा फर्मुला सम्पादन गर्नुहोस्।');
                $sheet->setCellValue('A' . ($footerStart + 4), '४. वार्षिक प्रतिवेदन, त्रैमासिक प्रतिवेदनहरू स्वत: गणना हुन्छन्।');

                // Merge A for footer if multi-line, but keep simple
                $sheet->mergeCells('A' . $footerStart . ':B' . $footerStart);
            },
        ];
    }
}
