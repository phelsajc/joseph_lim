<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;

class CustomPrescriptiontestA5Portrait extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;

    public function __construct($data)
    {
        $this->data = $data;
        parent::__construct('P', 'mm', 'A5');
        $this->SetTitle('My pdf title', true);
        $this->SetAuthor('TJGazel', true);
        $this->AddPage('P');
        $this->Body();
    }

    public function Header()
    {
        $this->Image(public_path() . '/img/lim_fb.png', 132, 6, 11, 11, 'PNG');
        $this->Image(public_path() . '/img/lim_rhuema.jpg', 120, 6, 11, 11, 'JPG');
        $this->Image(public_path() . '/img/cp.jpg', 108, 6, 11, 11, 'JPG');
        $this->Ln(1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(52, 4, 'JOSEPH PETER T. LIM, MD', 0, 0, 'R');
        $this->SetFont('helvetica', 'B', 9);
        $this->Ln(1);
        $this->Cell(60, 10, strtoupper($this->data['profile']->specialization1.' - '.$this->data['profile']->specialization2), 0, 0, 'R');
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
        $this->SetFont('Arial', '', 8);
        $this->AliasNbPages();
        $this->cell(10, 3, '', '0', 0, 'R');
        $this->cell(-3, 3, 'Name:', 0, 0, 'R');
        $this->cell(75, 3, strtoupper(utf8_decode($this->data['patient_detail']->patientname)), 'B', 0, 'L');
        $this->SetFont('');

        $this->cell(-13, 3, '', 0, 0);
        $this->cell(22, 3, 'Sex :', 0, 0, 'R');
        $this->cell(12, 3, strtoupper(utf8_decode($this->data['patient_detail']->sex == 2 ? 'Female' : 'Male')), 'B', 0, 'R');
        $this->SetFont('');
        $this->cell(2, 3, '', 0, 0);
        $this->cell(8, 3, 'Age:', 0, 0);
        $this->cell(8, 3, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y, 'B', 0);
        $this->setFont('');

        $this->Ln(5);
        $this->cell(-5, 3, '', '0', 0, 'R');
        $this->cell(5, 4, 'Address :', 0, 0);
        $this->cell(8, 3, '', '', 0, 0);
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
        $this->SetFont('Arial', '', 8);
        $this->Cell(8, 4, 'Date:', 0, 0);
        $this->Cell(20, 4, date("m/d/Y"), 'B', 1);
        $this->Image(public_path() . '/img/rx.png', 12, 53, 9, 9, 'PNG');


        $this->Image(public_path() . '/img/lim_wm.png', 32, 70, 80, 0, 'PNG');
        if ($this->PageNo() == 1) {
            $this->Ln(15);
        }else{
            $this->Ln(16);
           // $this->mealHeader();
        }
    }

    public function mealHeader()
    {
        $this->SetFont('Arial', '', 8);
        $this->cell(-3, 3, '', '0', 0, 'R');

        $this->Cell(45, 5, "Medicine Name", 'LTR', 0, 'C');
        $this->Cell(8, 5, "QTY", "TR", 0, 'C');

        $this->Cell(12, 5, "Breakfast", 'T', 0, 'C');
        $this->Cell(12, 5, "Lunch", 1, 0, 'C');


        $this->Cell(12, 5, "Supper", 1, 0, 'C');

        $this->Cell(9, 5, "Beds", "TR", 0, 'C');


        $this->Cell(40, 5, "Remarks", "TR", 0, 'C');

        $this->Ln(5);
        $this->cell(-3, 3, '', '0', 0, 'R');

        $this->SetFont('Arial', '', 9);
        $this->Cell(45, 5, "", 'LBR', 0, 'C');

        $this->Cell(8, 5, "", "RB", 0, 'C');

        $this->Cell(6, 5, "B", 1, 0, 'C');//bf
        $this->Cell(6, 5, "A", 1, 0, 'C');


        $this->Cell(6, 5, "B", 1, 0, 'C');
        $this->Cell(6, 5, "A", 1, 0, 'C');

        $this->Cell(6, 5, "B", 1, 0, 'C');
        $this->Cell(6, 5, "A", 1, 0, 'C');


        $this->Cell(9, 5, "", "RB", 0, 'C');


        $this->Cell(40, 5, "", "RB", 0, 'C');
        $this->Ln(5);

        $this->SetWidths(
            array(
                45,
                8,
                6,
                6,
                6,
                6,
                6,
                6,
                9,
                40
            )
        );
    }

    public function meal()
    {
        $this->Ln(1);
        #do not remove
        $this->mealHeader();
        $rowCount = 0;

        $this->SetFont('Arial', '', 9);
        foreach ($this->data['query_prescription'] as $key => $item) {
            $checkGenericname = Generics::where(['id' => $item['generic_id']])->first();
            $this->cell(-3, 3, '', '0', 0, 'R');
            $this->Row(
                array(
                    $item['medicine'].$item['generic_name'],
                    $item['qty'],
                    $item['breakfastbefore'] ?? '',
                    $item['breakfastafter'] ?? '',
                    $item['lunchbefore'] ?? '',
                    $item['lunchafter'] ?? '',
                    $item['supperbefore'] ?? '',
                    $item['supperafter'] ?? '',
                    $item['bedtime'] ?? '',
                    $item['remarks']
                )
            );
            $rowCount++;

            //if ($rowCount > 0 && $rowCount % 11 == 0) {
            if ($rowCount % 5 == 0 && $rowCount != count($this->data['query_prescription'])) {
                $this->AddPage();
             $this->mealHeader(); // Redraw headers on new page
            }
        }
    }

    public function Body()
    { 
        $this->meal();
        $this->Ln(3);
        $this->Cell(41,3,"Diagnosis: ",'',0,'C');
        $this->Cell(-13,3,'','',0,'');
        $this->MultiCell(100, 3,$this->data['appointment_detail']->diagnosis, 'B', 'L');
    }

    public function Footer()
    {
        $this->SetY(-23);
        $this->SetFont('Arial', 'B', 7);
        $PageNo = intval($this->PageNo());
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 50, 180, 130, 20, 'png');
        }
        $this->Cell(35, 10, '', '', 0, '');
        $this->cell(85, -3, strtoupper($this->data['profile']->name), '', 0, 'R');
        $this->Ln(1);
        $this->SetFont('Arial', '', 7);
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
}