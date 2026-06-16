<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class CustomPrescriptiontestA5Portrait extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;
    private $rotateAngle = 0;
    private $printingMealTable = false;
    private $mealHeaderDrawnOnPage = 0;
    private $patientProfileImagePath;
    private $patientProfileImageType;

    public function __construct($data)
    {
        $this->data = $data;
        $this->preparePatientProfileImage();
        parent::__construct('P', 'mm', 'A5');
        $this->SetTitle('My pdf title', true);
        $this->SetAuthor('TJGazel', true);
        // Set auto page break with proper margin to avoid footer overlap
        $this->SetAutoPageBreak(true, 30);
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

    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        parent::AddPage($orientation, $size, $rotation);

        if (
            $this->printingMealTable
            && $this->PageNo() > 1
            && $this->mealHeaderDrawnOnPage !== $this->PageNo()
        ) {
            $this->mealHeader();
            $this->mealHeaderDrawnOnPage = $this->PageNo();
        }
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
        $this->getPatientDisplayAptDate();
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
        $this->Cell(20, 4, $this->getPatientDisplayAptDate(), 'B', 1);
        
        $this->Image(public_path() . '/img/rx.png', 12, 53, 9, 9, 'PNG');
        if ($this->patientProfileImagePath) {
            $this->Image($this->patientProfileImagePath, 120, 45, 18.5, 18.5, $this->patientProfileImageType);
        }       

        /* if ($this->PageNo() == 1) {
            $this->Ln(15);
        }else{
            $this->Ln(16);
        } */

        if ($this->PageNo() > 1) {
            $this->Ln(21);
        }

        if ($this->PageNo() == 1) {
            $this->Ln(12);
        }
    }

    private function getPatientDisplayName(): string
    {
        return utf8_decode(
            ucwords(strtolower($this->data['patient_detail']->lastname)) . ', ' .
            ucwords(strtolower($this->data['patient_detail']->firstname)) . ' ' .
            strtoupper(substr($this->data['patient_detail']->middlename, 0, 1)) . '.'
        );
    }

    private function getPatientDisplayAptDate(): string
    {
        return date('m/d/Y', strtotime($this->data['appointment_detail']->appointment_dt));
    }

    private function drawTextWatermark(): void
    {
        $savedX = $this->GetX();
        $savedY = $this->GetY();

        $name = $this->getPatientDisplayName();
        $date = $this->getPatientDisplayAptDate();//date('m/d/Y');

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

    public function mealHeader()
    {
        $this->SetAutoPageBreak(false);

        if ($this->PageNo() > 1) {
            $this->SetX(10);
        }
        $this->SetFont('Arial', '', 10);
        $this->cell(-3, 3, '', '0', 0, 'R');

        $this->Cell(57, 5, "Medicine Name", 'LTR', 0, 'C');
        $this->Cell(15, 5, "Quantity", "TR", 0, 'C');

        /* $this->Cell(16, 5, "Breakfast", 'T', 0, 'C');
        $this->Cell(16, 5, "Lunch", 1, 0, 'C');


        $this->Cell(16, 5, "Dinner", 1, 0, 'C');

        $this->Cell(10.5, 5, "Bedtime", "TR", 0, 'C'); */


        $this->Cell(63, 5, "Remarks", "TR", 0, 'C');

        $this->Ln(5);
        $this->cell(-3, 3, '', '0', 0, 'R');

        $this->SetFont('Arial', '', 9);
        $this->Cell(57, 5, "", 'LBR', 0, 'C');

        $this->Cell(15, 5, "", "RB", 0, 'C');

        /* $this->Cell(8, 5, "B", 1, 0, 'C');//bf
        $this->Cell(8, 5, "A", 1, 0, 'C');


        $this->Cell(8, 5, "B", 1, 0, 'C');
        $this->Cell(8, 5, "A", 1, 0, 'C');

        $this->Cell(8, 5, "B", 1, 0, 'C');
        $this->Cell(8, 5, "A", 1, 0, 'C');


        $this->Cell(10.5, 5, "", "RB", 0, 'C'); */


        $this->Cell(63, 5, "", "RB", 0, 'C');
        $this->Ln(5);

        $this->SetWidths(
            array(
                57,
                15,
                /* 8,
                8,
                8,
                8,
                8,
                8,
                10.5, */
                63
            )
        );

        $this->SetAutoPageBreak(true, 30);
    }

    private function formatMedicineName($genericName, $medicine, $medicineId)
    {
        if (empty($medicine)) {
            return Str::title(iconv("UTF-8", "windows-1252//TRANSLIT", $genericName . ' '));
        }

        // If medicine_id is 0, check if generic_name already contains parentheses
        if (strpos($medicine, '(') !== false) {
            // Generic name already has parentheses, just add space
            return Str::title(iconv("UTF-8", "windows-1252//TRANSLIT", $genericName . ' ' . $medicine . ' '));
        } else {
            // Generic name has no parentheses, add parentheses around medicine
            return Str::title(iconv("UTF-8", "windows-1252//TRANSLIT", $genericName . ' (' . $medicine . ') '));
        }
    }

    public function meal()
    {
        if (!empty($this->data['prescription_sections']) && is_array($this->data['prescription_sections'])) {
            $this->mealWithSections();
            return;
        }

        $this->renderMealRows($this->data['query_prescription']);
    }

    private function mealWithSections(): void
    {
        $sections = $this->data['prescription_sections'];
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
            $this->renderMealRows($items);
            $first = false;
        }
    }

    private function renderMealRows($items): void
    {
        $this->printingMealTable = true;
        $this->mealHeader();
        $this->mealHeaderDrawnOnPage = $this->PageNo();

        $this->SetFont('Arial', '', 10);
        foreach ($items as $key => $item) {
            $this->SetX(10);
            $this->cell(-3, 3, '', '0', 0, 'R');
            $this->Row(
                array(
                    $this->formatMedicineName($item['generic_name'], $item['medicine'], $item['medicine_id']),
                    $item['qty'],
                    /* $item['breakfastbefore'],
                    $item['breakfastafter'],
                    $item['lunchbefore'],
                    $item['lunchafter'],
                    $item['supperbefore'],
                    $item['supperafter'],
                    $item['bedtime'], */
                    $item['remarks']
                )
            );
        }
        $this->printingMealTable = false;
    }

    public function Body()
    {
        if ($this->PageNo() == 1) {
            $this->Ln(9);
        }
        $this->meal();
        $this->Ln(3);
        $this->Cell(41, 3, "Diagnosis: ", '', 0, 'C');
        $this->Cell(-13, 3, '', '', 0, '');
        $this->MultiCell(100, 3, $this->data['appointment_detail']->diagnosis, 'B', 'L');
    }

    public function Footer()
    {
        $this->SetY(-23);
        $this->SetFont('Arial', 'B', 10);
        $PageNo = intval($this->PageNo());
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 80, 174, 110, 20, 'png');
        }
        $this->Cell(35, 10, '', '', 0, '');
        $this->cell(85, -3, strtoupper($this->data['profile']->name), '', 0, 'R');
        $this->Ln(1);
        $this->SetFont('Arial', '', 10);
        $this->cell(100, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->prc, 'B', 1, 'R');
        $this->cell(100, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->ptr, 'B', 1, 'R');
        if ($this->data['appointment_detail']->withs2) {
            $this->Ln(0.5);
            $this->cell(100, 3, "S2 No.", '', 0, 'R');
            $this->cell(25, 3, $this->data['profile']->s2, 'B', 1, 'R');
            $this->cell(100, 3, "Date Issued:", '', 0, 'R');
            $this->cell(25, 3, date_format(date_create($this->data['profile']->date_issued), "F d, Y"), 'B', 1, 'R');
            $this->cell(100, 3, "Valid Until:", '', 0, 'R');
            $this->cell(25, 3, date_format(date_create($this->data['profile']->s2_validity), "F d, Y"), 'B', 1, 'R');
        }
        $flwpdt = $this->data['appointment_detail']->followup ? date_format(date_create($this->data['appointment_detail']->followup), "F d, Y") : '';
        $this->Cell(76, 3, '', '', 1, '');
        $this->Cell(25, -9, "Next appointment: ", '', 0, 'C');
        $this->Cell(3, 3, '', 0, '');
        $this->Cell(55, -9, $flwpdt, '', 0, 'L');
        $this->Ln(1);
        $this->Cell(25, -9, '', 0, '');
        $this->Cell(40, -3, '', 'B', '');
        $this->SetAutoPageBreak(true, 30);
    }

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function Row1($data)
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

    function Row($data)
    {
        $lineHeight = 4;
        //$this->SetFont('Arial', '', 11); // Always use size 8

        // Determine the max number of lines required for wrapping
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $text = wordwrap($data[$i], 60, "\n", true); // Wrap long text to avoid overflow
            $nb = max($nb, $this->NbLines($this->widths[$i], $text));
            $data[$i] = $text;
        }

        $h = $lineHeight * $nb;
        $this->CheckPageBreak($h);

        // Reset X position after potential page break to ensure consistent alignment
        $this->SetX(10);
        $this->cell(-3, 3, '', '0', 0, 'R'); // Move table to the left

        // Draw each cell
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L'; // default align left
            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $w, $h); // draw border
            $this->MultiCell($w, $lineHeight, $data[$i], 0, $a); // fixed font, wrapped text
            $this->SetXY($x + $w, $y); // move to the right
        }

        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
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
}