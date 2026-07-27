<?php
namespace App\Http\Controllers\Api;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class RequestprescriptionA5 extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;
    protected $checkedItems;
    private $rotateAngle = 0;
    private $patientProfileImagePath;
    private $patientProfileImageType;

    public function __construct($data, $checkedItems = [])
    {
        $this->checkedItems = $checkedItems;
        $this->data = $data;
        $this->preparePatientProfileImage();
        parent::__construct('P', 'mm', 'A5');
        $this->SetTitle('My pdf title', true);
        $this->SetAuthor('TJGazel', true);
        $this->AddPage('P');
        $this->Body();
    }
    public function __destruct()
    {
        if ($this->patientProfileImagePath && is_file($this->patientProfileImagePath)) {
            @unlink($this->patientProfileImagePath);
        }
    }

    private function preparePatientProfileImage(): void
    {
        $profileName = $this->data['patient_detail']->profile_name ?? null;
        if (!$profileName) {
            return;
        }

        $s3Path = $this->data['patient_detail']->id . '/' . $profileName;
        try {
            if (!Storage::disk('s3')->exists($s3Path)) {
                return;
            }
            $contents = Storage::disk('s3')->get($s3Path);
        } catch (\Throwable $e) {
            return;
        }

        if ($contents === null || $contents === '') {
            return;
        }

        $ext = strtolower(pathinfo($profileName, PATHINFO_EXTENSION));
        $this->patientProfileImageType = in_array($ext, ['jpg', 'jpeg'], true) ? 'JPG' : 'PNG';
        $suffix = $this->patientProfileImageType === 'JPG' ? '.jpg' : '.png';

        $tmpBase = tempnam(sys_get_temp_dir(), 'px_profile_');
        if ($tmpBase === false) {
            return;
        }
        $path = $tmpBase . $suffix;
        if (@rename($tmpBase, $path) === false) {
            @unlink($tmpBase);
            return;
        }
        if (file_put_contents($path, $contents) === false) {
            @unlink($path);
            return;
        }

        $this->patientProfileImagePath = $path;
    }

    public function Header()
    {
        $this->drawTextWatermark();
        $this->Image(public_path() . '/img/lim_fb.png', 128, 6, 20, 11, 'PNG');
        $this->Image(public_path() . '/img/lim_rhuema.jpg', 120, 6, 11, 11, 'JPG');
        $this->Image(public_path() . '/img/cp.jpg', 108, 6, 11, 11, 'JPG');
        $this->Ln(1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(52, 4, 'JOSEPH PETER T. LIM, MD', 0, 0, 'R');
        $this->SetFont('helvetica', 'B', 9);
        $this->Ln(1);
        $this->Cell(60, 10, strtoupper($this->data['profile']->specialization1 . ' - ' . $this->data['profile']->specialization2), 0, 0, 'R');
        $this->Ln(5);
        $this->SetFont('Arial', '', 8);
        $this->Cell(48, 11, 'Fellow, Philippine College of Physicians', 0, 0, 'R');
        $this->Ln(1);
        $this->Cell(59, 15, 'Diplomate, Philippine Rheumatology Association', 0, 0, 'R');
        $this->Ln(1);
        $this->Cell(39.5, 19, 'Email: jplimmd.clinic@gmail.com', 0, 0, 'R');
        $this->SetLineWidth(0.5);
        $this->SetFont('Arial', 'B', 7);
        $this->Ln(0.05);
        $this->Ln(5);
        $this->Ln(10);
        $this->SetFont('Arial', '', 7);
        $this->SetXY(7, 20);
        $this->SetFont('Arial', 'B', 4.5);
        $this->SetXY(75.5, 17);
        $this->MultiCell(62, 3, "Room 504. Riverside Medical", 0, 'L');
        $this->SetXY(75.5, 19);
        $this->MultiCell(62, 3, "Arts Building, BS Aquino Drive, Bacolod ", 0, 'L');
        $this->SetXY(75.5, 21);
        $this->MultiCell(61.5, 3, "Schedule: Mon-Wed-Fri: 2:00 PM - 5:00 PM ", 0, 'L');
        $this->SetXY(75.5, 23);
        $this->MultiCell(62, 3, "For appointment: 0962-484-5664 ", 0, 'L');
        $this->SetXY(1, 34);
        $this->SetFont('Arial', 'B', 5);
        $this->MultiCell(150, 5, "Hospital Affiliations: Dr. Pablo O. Torre Memorial Hospital, Metro Bacolod Hospital and Medical Center, Bacolod Queen of Mercy Hospital, Adventist Medical Center-Bacolod", 0, 'L');
        $this->SetFont('Arial', 'B', 4.5);
        $this->SetXY(110, 17);
        $this->MultiCell(62, 3, "Room 415. Metro Bacolod Hospital and Medical", 0, 'L');
        $this->SetXY(110, 19);
        $this->MultiCell(62, 3, "Center, Brgy. Estefania, Bacolod", 0, 'L');
        $this->SetXY(110, 21);
        $this->MultiCell(61.5, 3, "Schedule: Tue-Thu: 9:00 AM - 12:00 PM ", 0, 'L');
        $this->SetXY(110.2, 23);
        $this->MultiCell(62, 3, "For appointment: 0968-418-7873", 0, 'L');
        $this->SetXY(75.5, 26);
        $this->MultiCell(62, 3, "VitalRx Pharmacy and Arthritis Clinic, JTL", 0, 'L');
        $this->SetXY(75.5, 28);
        $this->MultiCell(62, 3, "Building, BS Aquino Drive, Bacolod", 0, 'L');
        $this->SetXY(75.5, 30);
        $this->MultiCell(61.5, 3, "Schedule: Mon-Wed-Fri: 9:00 AM - 12:00 PM ", 0, 'L');
        $this->SetXY(75.5, 32);
        $this->MultiCell(62, 3, "For appointment.: 0966-073-6942", 0, 'L');
        $this->SetXY(110, 26);
        $this->MultiCell(62, 3, "Agustin Medical Clinic ", 0, 'L');
        $this->SetXY(110, 28);
        $this->MultiCell(62, 3, "Sen Jose Locsin Street, Brgy. V, Silay ", 0, 'L');
        $this->SetXY(110, 30);
        $this->MultiCell(61.5, 3, "Schedule: Thursday 1:30 PM - 4:30 PM", 0, 'L');
        $this->SetXY(110.2, 32);
        $this->MultiCell(62, 3, "For appointment: 0928-259-8495", 0, 'L');
        $this->Ln(1);
        $this->SetLineWidth(0.5);
        $this->Line(2, 38, 146, 38);
        $this->Ln(5);

        $this->SetFont('Arial', '', 11);
        $this->AliasNbPages();
        $this->cell(12, 3, '', '0', 0, 'R');
        $this->cell(-5, 3, 'Name:', 0, 0, 'R');
        $name = $this->getPatientDisplayName();

        $this->cell(75, 3, $name, 'B', 0, 'L');

        $this->SetFont('');

        $this->cell(-13, 3, '', 0, 0);
        $this->cell(22, 3, 'Sex:', 0, 0, 'R');
        $this->cell(17, 3, strtoupper(utf8_decode($this->data['patient_detail']->sex == 2 ? 'Female' : 'Male')), 'B', 0, 'R');
        $this->SetFont('');
        $this->cell(2, 3, '', 0, 0);
        $this->cell(8, 3, 'Age:', 0, 0);
        $this->cell(8, 3, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y, 'B', 0);
        $this->setFont('');

        $this->Ln(5);
        $this->cell(-5, 3, '', '0', 0, 'R');
        $this->cell(5, 4, 'Address:', 0, 0);
        $this->cell(14, 3, '', '', 0, 0);
        $x = $this->GetX();
        $y = $this->GetY();
        $w = 85;
        $lineHeight = 4;

        $address = utf8_decode($this->data['patient_detail']->address);
        $this->MultiCell($w, $lineHeight, $address, 0, 'L');

        $lines = ceil($this->GetStringWidth($address) / $w);
        for ($i = 0; $i < $lines; $i++) {
            $this->Line($x, $y + ($i + 1) * $lineHeight, $x + $w, $y + ($i + 1) * $lineHeight);
        }

        $this->SetXY($x + $w + 5, $y);
        $this->SetFont('Arial', '', 11);
        $this->cell(-2, 3, '', 0, 0);
        $this->Cell(11, 4, 'Date:', 0, 0);

        $this->Cell(20, 4, date('m/d/Y'), 'B', 1);
        /* if ($this->patientProfileImagePath) {
            $this->Image($this->patientProfileImagePath, 120, 45, 18.5, 18.5, $this->patientProfileImageType);
        } */

        /* if ($this->PageNo() == 1) {
            $this->Ln(15);
        }else{
            $this->Ln(16);
        } */

        if ($this->PageNo() > 1) {
            $this->Ln(17);
        }

        if ($this->PageNo() == 1) {
            $this->Ln(12);
        }
    }
    public function meal()
    {
        if (!empty($this->data['diagnostic_sections']) && is_array($this->data['diagnostic_sections'])) {
            $this->mealWithDiagnosticSections();
            return;
        }

        $this->renderDiagnosticItems($this->data['query_prescription']);
    }

    private function mealWithDiagnosticSections(): void
    {
        $sections = $this->data['diagnostic_sections'];
        $first = true;
        foreach ($sections as $section) {
            if (!$first) {
                $this->Ln(4);
            }
            if (!empty($section['title'])) {
                $this->SetFont('Arial', 'B', 9);
                $this->SetX(10);
                $this->cell(-3, 3, '', '0', 0, 'R');
                $this->Cell(130, 5, utf8_decode($section['title']), 0, 1, 'L');
                $this->Ln(1);
            }
            $items = isset($section['items']) ? $section['items'] : [];
            $this->renderDiagnosticItems($items);
            $this->renderDiagnosticGroupMeta($section);
            $first = false;
        }
    }

    private function renderDiagnosticItems($items): void
    {
        $this->SetFont('Arial', '', 12);
        $lineHeight = 4.5;
        $rowCount = 0;
        $cnt = 0;
        $letterIndex = 0;
        $inSynovialFluidGroup = false;
        $itemCount = is_countable($items) ? count($items) : 0;

        foreach ($items as $item) {
            $isExtraItem = isset($item['remarks']) && $item['remarks'] == 'extra';

            if ($isExtraItem && $inSynovialFluidGroup) {
                $letter = chr(65 + $letterIndex);
                $letterIndex++;
                $this->cell(15, 1, '', '', 0, 'R');
                $this->cell(10, 1, '', '', 0, 'R');
                $fullText = $letter . '.)' . ' ' . strtoupper($item['ancillary']);
                $this->MultiCell(0, $lineHeight, $fullText, 0, 'L');
                $rowCount++;
                if ($rowCount % 25 == 0 && $rowCount != $itemCount) {
                    $this->AddPage();
                }
                continue;
            }

            if ($inSynovialFluidGroup && !$isExtraItem) {
                $inSynovialFluidGroup = false;
                $letterIndex = 0;
            }

            if (isset($item['remarks'])) {
                $cnt++;
                $this->cell(15, 1, '', '', 0, 'R');
                $fullText = $cnt . ').' . ' ' . $item['ancillary'] . ' ' . $item['remarks'];
                $this->MultiCell(0, $lineHeight, strtoupper($fullText), 0, 'L');
            } else if ($item['ancillary_id'] == 568 && (isset($item['remarks']) || $item['remarks'] != 'extra')) {
                $cnt++;
                $inSynovialFluidGroup = true;
                $letterIndex = 0;
                $this->cell(15, 1, '', '', 0, 'R');
                $fullText = $cnt . ').' . ' ' . $item['ancillary'] . ' ' . $item['remarks'];
                $this->MultiCell(0, $lineHeight, strtoupper($fullText), 0, 'L');
            } else if ($item['ancillary_id'] == 591 && isset($item['remarks'])) {
                $cnt++;
                $this->cell(15, 1, '', '', 0, 'R');
                $fullText = $cnt . ').' . $item['remarks'] . ' ' . $item['ancillary'];
                $this->MultiCell(0, $lineHeight, strtoupper($fullText), 0, 'L');
            } else if ($item['ancillary_id'] == 593 && isset($item['remarks']) || $item['ancillary_id'] == 594 && isset($item['remarks'])) {
                $cnt++;
                $this->cell(15, 1, '', '', 0, 'R');
                $fullText = $cnt . ').' . $item['ancillary'] . ' ' . $item['remarks'];
                $this->MultiCell(0, $lineHeight, strtoupper($fullText), 0, 'L');
            } else {
                $cnt++;
                $fullText = $cnt . ').' . $item['ancillary'];
                $this->cell(15, 1, '', '', 0, 'R');
                $this->MultiCell(0, $lineHeight, strtoupper($fullText), 0, 'L');
            }

            $rowCount++;
            if ($rowCount % 25 == 0 && $rowCount != $itemCount) {
                $this->AddPage();
            }
        }
        $this->SetFont('Arial', '', 8);
    }

    private function getHeaderDisplayDate(): string
    {
        if (!empty($this->data['diagnostic_sections']) && is_array($this->data['diagnostic_sections'])) {
            return date('m/d/Y');
        }

        $requestDate = $this->getDiagnosticGroupMetaValue('request_date');
        $formatted = $this->formatRequestDate($requestDate);

        return $formatted !== '' ? $formatted : date('m/d/Y');
    }

    private function formatRequestDate($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('m/d/Y');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function renderDiagnosticGroupMeta($group): void
    {
        $requestDateFormatted = $this->formatRequestDate($group['request_date'] ?? null);
        $fields = [
            'Remarks' => $group['lab_remarks'] ?? '',
            'Findings' => $group['findings'] ?? '',
            'Notes' => $group['notes'] ?? '',
            'Recommendations' => $group['recommendations'] ?? '',
            //'Request Date' => $requestDateFormatted,
        ];

        foreach ($fields as $label => $value) {
            if (trim((string) $value) === '') {
                continue;
            }
            $this->Ln(1);
            $this->SetFont('Arial', '', 8);
            $this->Cell(40, 3, $label . ': ', '', 0, 'C');
            $this->Cell(-13, 3, '', '', 0, '');
            $this->MultiCell(100, 3, $value, 'B', 'L');
        }
    }

    private function getDiagnosticGroupMetaValue(string $field, $default = '')
    {
        $detail = $this->data['diagnostic_group_detail'] ?? null;
        if (!$detail || !isset($detail->{$field})) {
            return $default;
        }

        $value = $detail->{$field};
        if ($field === 'request_date') {
            return $value !== null && $value !== '' ? $value : $default;
        }

        if (trim((string) $value) !== '') {
            return $value;
        }

        return $default;
    }

    public function Body()
    {
        $this->SetFont('Arial', 'B', 12);
        //$this->Cell(40, 3, "Request Date: "., '', 0, 'C');
        $requestDate = $this->formatRequestDate(
            $this->getDiagnosticGroupMetaValue('request_date')
        );
        if ($requestDate !== '') {
            $this->Ln(1);
            $this->Cell(40, 3, 'Request Date: ', '', 0, 'C');
            $this->Cell(-2, 3, '', '', 0, '');
            $this->MultiCell(100, 3, $requestDate, '', 'L');
        }
        $this->Ln(5);
        $this->meal();

        $this->SetFont('Arial', '', 9);
        if (empty($this->data['diagnostic_sections'])) {
            $this->Ln(5);
            $this->Cell(40, 3, "Remarks: ", '', 0, 'C');
            $this->Cell(-10, 3, '', '', 0, '');

            $remarks = $this->getDiagnosticGroupMetaValue(
                'lab_remarks',
                $this->data['appointment_detail']->lab_remarks
            );
            $this->MultiCell(100, 3, $remarks, 'B', 'L');

            $findings = $this->getDiagnosticGroupMetaValue('findings');
            if (trim((string) $findings) !== '') {
                $this->Ln(1);
                $this->Cell(40, 3, "Findings: ", '', 0, 'C');
                $this->Cell(-10, 3, '', '', 0, '');
                $this->MultiCell(100, 3, $findings, 'B', 'L');
            }

            $notes = $this->getDiagnosticGroupMetaValue('notes');
            if (trim((string) $notes) !== '') {
                $this->Ln(1);
                $this->Cell(40, 3, "Notes: ", '', 0, 'C');
                $this->Cell(-10, 3, '', '', 0, '');
                $this->MultiCell(100, 3, $notes, 'B', 'L');
            }

            $recommendations = $this->getDiagnosticGroupMetaValue('recommendations');
            if (trim((string) $recommendations) !== '') {
                $this->Ln(1);
                $this->Cell(40, 3, "Recommendations: ", '', 0, 'C');
                $this->Cell(-10, 3, '', '', 0, '');
                $this->MultiCell(100, 3, $recommendations, 'B', 'L');
            }
        }

        $this->Ln(1);
        $this->Cell(41, 3, "Diagnosis: ", '', 0, 'C');
        $this->Cell(-10, 3, '', '', 0, '');
        $this->MultiCell(100, 3, $this->data['appointment_detail']->diagnosis, 'B', 'L');

        $flwpdt = $this->data['appointment_detail']->followup ? date_format(date_create($this->data['appointment_detail']->followup), "F d, Y") : '';
        if ($flwpdt !== '') {
            $this->Ln(1);
            $this->Cell(40, 3, 'Follow up Date: ', '', 0, 'C');
            $this->Cell(-10, 3, '', '', 0, '');
            $this->MultiCell(100, 3, $flwpdt, 'B', 'L');
        }
    }

    public function Footer()
    {
        $this->SetY(-17);
        $this->SetFont('Arial', 'B', 10);
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 88, 183, 110, 20, 'png');
        }

        $this->Cell(45, 10, '', '', 0, '');
        $this->cell(85, 3, strtoupper($this->data['profile']->name), '', 0, 'R');
        $this->Ln(5);
        $this->SetFont('Arial', '', 10);
        $this->cell(110, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->prc, 'B', 1, 'R');
        $this->cell(110, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->ptr, 'B', 1, 'R');

        $this->SetY(-30); // Footer position
        $this->SetFont('Arial', '', 8);
        $this->SetX(10);

        $this->Cell(0, 5, 'Remarks:', 0, 1, 'L');
        $getFastingMode = $this->data['appointment_detail']->fasting_mode;
        // Draw boxes + text
        $remarks = [
            ['Fasting 8-10 hours', $getFastingMode == 1 ? true : false], // true = checked
            ['Fasting 10-12 hours', $getFastingMode == 2 ? true : false],
            ['Non-fasting', $getFastingMode == 3 ? true : false],
            ['*Kindly send x-ray images to email:', $this->data['appointment_detail']->send_xray_email],
            ['jplimmd@gmail.com', false]
        ];

        foreach ($remarks as $i => [$text, $checked]) {
            if ($i < 4) { // first 3 have checkboxes
                $x = 10;
                $y = $this->GetY();

                // Draw square
                $this->Rect($x, $y, 4, 4);

                // Add X if checked
                if ($checked) {
                    $this->SetXY($x, $y - 0.5); // slight up adjust
                    $this->Cell(4, 5, 'X', 0, 0, 'C');
                }

                // Text beside box
                $this->SetXY($x + 6, $y);
                $this->Cell(0, 4, $text, 0, 1, 'L');
            } else {
                // No box, just text
                $this->SetX(10);
                $this->Cell(0, 4, $text, 0, 1, 'L');
            }
        }
        $this->SetAutoPageBreak(true, 25);
    }

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function Row($data)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    private function getPatientDisplayName(): string
    {
        return utf8_decode(
            ucwords(strtolower($this->data['patient_detail']->lastname)) . ', ' .
            ucwords(strtolower($this->data['patient_detail']->firstname)) . ' ' .
            strtoupper(substr($this->data['patient_detail']->middlename, 0, 1)) . '.'
        );
    }

    private function drawTextWatermark(): void
    {
        $savedX = $this->GetX();
        $savedY = $this->GetY();

        $name = $this->getPatientDisplayName();
        $date = date('m/d/Y');

        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(220, 220, 220);

        $angle = 45;
        $lineGap = 5;
        $tileWidth = max($this->GetStringWidth($name), $this->GetStringWidth($date)) + 15;
        $stepX = $tileWidth + 10;
        $stepY = 10;

        for ($y = -10; $y < $this->h + 30; $y += $stepY) {
            $row = (int) (($y + 10) / $stepY);
            $offsetX = ($row % 2) * ($stepX / 2);
            for ($x = -10 + $offsetX; $x < $this->w + 30; $x += $stepX) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $name);
                $this->Text($x, $y + $lineGap, $date);
                $this->Rotate(0);
            }
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);
        $this->SetXY($savedX, $savedY);
    }

    private function Rotate(float $angle, float $x = -1, float $y = -1): void
    {
        if ($x === -1.0) {
            $x = $this->x;
        }
        if ($y === -1.0) {
            $y = $this->y;
        }
        if ($this->rotateAngle != 0) {
            $this->_out('Q');
        }
        $this->rotateAngle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf(
                'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
                $c,
                $s,
                -$s,
                $c,
                $cx,
                $cy,
                -$cx,
                -$cy
            ));
        }
    }

    protected function _endpage()
    {
        if ($this->rotateAngle != 0) {
            $this->rotateAngle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

}

