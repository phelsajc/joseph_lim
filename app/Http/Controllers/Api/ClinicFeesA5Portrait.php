<?php

namespace App\Http\Controllers\Api;

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ClinicFeesA5Portrait extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;
    private $rotateAngle = 0;

    public function __construct($data)
    {
        $this->data = $data;
        parent::__construct('P', 'mm', 'A5');
        $this->SetTitle('Clinic Fees', true);
        $this->SetAuthor('Clinic', true);
        $this->SetAutoPageBreak(true, 30);
        $this->AddPage('P');
        $this->Body();
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
        $this->Ln(12);

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
        $aptDate = $this->data['appointment_detail']->appointment_dt
            ? date('m/d/Y', strtotime($this->data['appointment_detail']->appointment_dt))
            : date('m/d/Y');
        $this->Cell(20, 4, $aptDate, 'B', 1);

        /* if ($this->data['patient_detail']->profile_name) {
            $fileUrl = Storage::disk('s3')->temporaryUrl(
                $this->data['patient_detail']->id . '/' . $this->data['patient_detail']->profile_name,
                Carbon::now()->addMinutes(180)
            );
            $this->Image($fileUrl, 120, 45, 18.5, 18.5, 'PNG');
        } */

        if ($this->PageNo() > 1) {
            $this->Ln(17);
        }
        if ($this->PageNo() == 1) {
            $this->Ln(8);
        }
    }

    public function Body()
    {
        if ($this->PageNo() == 1) {
            $this->Ln(4);
        }

        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, 'STATEMENT OF FEES', 0, 1, 'C');
        $this->Ln(2);

        $services = $this->data['query_services'];
        if ($services->isEmpty()) {
            $this->SetFont('Arial', 'I', 9);
            $this->Cell(0, 6, 'No services recorded for this appointment.', 0, 1, 'C');
            $this->drawTotals(0);
            return;
        }

        $this->servicesHeader();
        $this->SetFont('Arial', '', 9);
        $rowNum = 1;
        foreach ($services as $item) {
            $this->SetX(10);
            $this->cell(-3, 3, '', '0', 0, 'R');
            $this->Row([
                (string) $rowNum,
                utf8_decode($item->service),
                $this->formatAmount($item->fee),
            ]);
            $rowNum++;
        }

        $gross = $services->sum('fee');
        $this->Ln(2);
        $this->drawTotals($gross);
    }

    private function drawTotals(float $gross): void
    {
        $discount = (float) ($this->data['appointment_detail']->discount ?? 0);
        $net = max($gross - $discount, 0);

        $this->SetFont('Arial', '', 9);
        $labelX = 75;
        $valueW = 35;

        $this->SetX($labelX);
        $this->Cell(30, 5, 'Subtotal:', 0, 0, 'R');
        $this->Cell($valueW, 5, $this->formatAmount($gross), 'B', 1, 'R');

        $this->SetX($labelX);
        $this->Cell(30, 5, 'Discount:', 0, 0, 'R');
        $this->Cell($valueW, 5, $this->formatAmount($discount), 'B', 1, 'R');

        $this->SetFont('Arial', 'B', 10);
        $this->SetX($labelX);
        $this->Cell(30, 6, 'Total Due:', 0, 0, 'R');
        $this->Cell($valueW, 6, $this->formatAmount($net), 'B', 1, 'R');
    }

    private function formatAmount($amount): string
    {
        return 'P ' . number_format((float) $amount, 2, '.', ',');
    }

    public function servicesHeader()
    {
        $this->SetFont('Arial', 'B', 9);
        $this->cell(-3, 3, '', '0', 0, 'R');
        $this->Cell(10, 5, '#', 'LTR', 0, 'C');
        $this->Cell(95, 5, 'Service', 'TR', 0, 'C');
        $this->Cell(30, 5, 'Amount', 'TR', 0, 'C');
        $this->Ln(5);
        $this->SetWidths([10, 95, 30]);
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

    public function Footer()
    {
        $this->SetY(-23);
        $this->SetFont('Arial', 'B', 10);
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 80, 174, 110, 20, 'png');
        }
        $this->Cell(35, 10, '', '', 0, '');
        $this->cell(85, -3, strtoupper($this->data['profile']->name), '', 0, 'R');
        $this->Ln(1);
        $this->SetFont('Arial', '', 10);
        $this->cell(100, 3, 'License No:', '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->prc, 'B', 1, 'R');
        $this->cell(100, 3, 'PTR. No.', '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->ptr, 'B', 1, 'R');
        $this->SetAutoPageBreak(true, 30);
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function Row($data)
    {
        $lineHeight = 4;
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $text = wordwrap($data[$i], 60, "\n", true);
            $nb = max($nb, $this->NbLines($this->widths[$i], $text));
            $data[$i] = $text;
        }

        $h = $lineHeight * $nb;
        $this->CheckPageBreak($h);

        $this->SetX(10);
        $this->cell(-3, 3, '', '0', 0, 'R');

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : ($i === count($data) - 1 ? 'R' : 'L');
            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, $lineHeight, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }

        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->servicesHeader();
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
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
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}
