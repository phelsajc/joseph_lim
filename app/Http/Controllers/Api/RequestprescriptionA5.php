<?php
namespace App\Http\Controllers\Api;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;

class RequestprescriptionA5 extends Fpdf
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
        $this->Image(public_path() . '/img/kp.png', 10, 1, 25, 25, 'PNG');
        $this->Image(public_path() . '/img/cp.jpg', 27, 6, 12, 12, 'JPG');
        $this->Ln(1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(110, 3, strtoupper($this->data['profile']->name), 0, 0, 'R');
        $this->SetFont('Arial', 'B', 7);
        $this->Ln(0.05);
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(1, -8, '', '', 0, '');
        $this->Cell(116,2, strtoupper($this->data['profile']->specialization1), 0, 0, 'R');
        $this->Ln(9);
        $this->SetFont('Arial', '', 7);
        $this->Cell(115, -17, strtoupper($this->data['profile']->specialization2), 0, 0, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', '', 7);
        $this->SetXY(7, 14);
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(15, 20);
        $this->MultiCell(62, 3, "Hospital Affiliations:", 0, 'L');
        $this->SetFont('Arial', '', 7);
        $this->SetXY(15, 23); 
        $this->MultiCell(62, 3, "Dr. Pablo O. Torre Memorial\nHospital\nMetro Bacolod Medical Center\nHospital\nBacolod Queen of Mercy\nHospital", 0, 'L');
        $this->SetFont('Arial', 'B', 7);
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(55, 20);
        $this->MultiCell(62, 3, "Clinic Schedule:", 0, 'L');
        $this->SetFont('Arial', '', 7);
        $this->SetXY(55, 23); 
        $this->MultiCell(62, 3, "Metro Bacolod Hospital &\nMedical Center\nRoom 415\nMonday: 9AM - 12PM\nWednesday: 9AM-4PM\nFor appointment call or text\nsecretary: 09684187873", 0, 'L');
        $this->SetXY(95, 23); 
        $this->MultiCell(62, 3, "Fairr Diagnostic Center &\nAgustin Medical Clinic\nSilay City:\nEvery Saturday: 9AM-12PM\nFor appointments pls\nContact :09282598495", 0, 'L');
        $this->SetFont('Arial', 'B', 7);
        $this->SetLineWidth(0.5);
        $this->Line(5, 49, 145, 49);
        $this->Ln(10);
        $this->SetFont('Arial', '', 8);
        $this->AliasNbPages();
        $this->cell(15, 3, '', '0', 0, 'R');
        $this->cell(-3, 3, 'Name:', 0, 0, 'R');
        $this->cell(75, 3, strtoupper($this->data['patient_detail']->patientname), 'B', 0, 'L');
        $this->SetFont('');
        $this->cell(-13, 3, '', 0, 0);
        $this->cell(22, 3, 'Sex :', 0, 0, 'R');
        $this->cell(12, 3, strtoupper($this->data['patient_detail']->sex == 2 ? 'Female' : 'Male'), 'B', 0, 'R');
        $this->SetFont('');
        $this->cell(2, 3, '', 0, 0);
        $this->cell(8, 3, 'Age:', 0, 0);
        $this->cell(8, 3, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y, 'B', 0);
        $this->setFont('');
        $this->Ln(5);
        $this->cell(2, 3, '', '0', 0, 'R');
        $this->cell(5, 4, 'Address :', 0, 0);
        $this->cell(8, 3, '', '', 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->cell(85, 4, substr($this->data['patient_detail']->address, 0, 50), 'B', 0, 0);
        $this->cell(2, 3, '', 0, 0);
        $this->SetFont('Arial', '', 8);
        $this->cell(8, 4, 'Date:', 0, 0);
        $this->cell(15, 4, date("m/d/Y"), 'B', 0);       
        $this->Image(public_path() . '/img/rx.png', 12, 63, 9, 9, 'PNG');
    }

    public function meal1()
    {
        $this->SetFont('Arial', '', 10);
        $this->cell(35, 3, '', '', 0,'R');
        $this->cell(5, 3, 'A.) Laboratories',0, 0,'L');
        $this->Ln(5);
        $order = 1;
        foreach ($this->data['query_prescription'] as $key => $item) {
            if($item->type==1){
                $this->cell(35, 3, '', '', 0,'R');
                $this->cell(5, 3, ($order).'.'.$item['ancillary'],0, 0,'L');
                $this->Ln(5);
                $order++;
            }
        }
        $this->Ln(5);
        $this->cell(35, 3, '', '', 0,'R');
        $this->cell(5, 3, 'B.) Imaging',0, 0,'L');
        $this->Ln(5);
        $order = 1;
        foreach ($this->data['query_prescription'] as $key => $item) {
            if($item->type==2){
                $this->cell(35, 3, '', '', 0,'R');
                $this->cell(5, 3, ($order).'.'.$item['ancillary'],0, 0,'L');
                $this->Ln(5);
                $order++;
            }
        }
    }

    public function meal()
{
    $this->SetFont('Arial', '', 10);

    // --- A) Laboratories ---
    $hasLab = false;
    foreach ($this->data['query_prescription'] as $item) {
        if ($item->type == 1) {
            $hasLab = true;
            break;
        }
    }

    if ($hasLab) {
        $this->cell(35, 3, '', '', 0,'R');
        $this->cell(5, 3, 'A.) Laboratories', 0, 0, 'L');
        $this->Ln(5);

        $order = 1;
        foreach ($this->data['query_prescription'] as $item) {
            if ($item->type == 1) {
                $this->cell(35, 3, '', '', 0,'R');
                $this->cell(5, 3, $order . '. ' . $item['ancillary'], 0, 0, 'L');
                $this->Ln(5);
                $order++;
            }
        }

        $this->Ln(5);
    }

    // --- B) Imaging ---
    $hasImaging = false;
    foreach ($this->data['query_prescription'] as $item) {
        if ($item->type == 2) {
            $hasImaging = true;
            break;
        }
    }

    if ($hasImaging) {
        $this->cell(35, 3, '', '', 0,'R');
        $this->cell(5, 3, 'B.) Imaging', 0, 0, 'L');
        $this->Ln(5);

        $order = 1;
        foreach ($this->data['query_prescription'] as $item) {
            if ($item->type == 2) {
                $this->cell(35, 3, '', '', 0,'R');
                $this->cell(5, 3, $order . '. ' . $item['ancillary'], 0, 0, 'L');
                $this->Ln(5);
                $order++;
            }
        }
    }
}


    public function Body()
    {
        $this->Cell(170,6,'','',1,'');
        $this->Cell(168,13,'','',0,'');
        $this->Cell(10,3,"Date: ",'',0,'C');
        $this->Cell(15,3,date("F d,Y"),'B',0,'C');
        $this->Ln(5);
        $this->meal();
    }

    public function Footer()
    {
        $this->SetY(-17);
        $this->SetFont('Arial', 'B', 8);
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 50, 180, 130, 20, 'png');
        }
        
        $this->Cell(45, 10, '', '', 0, '');
        $this->cell(85, 3, strtoupper($this->data['profile']->name), '', 0, 'R');
        $this->Ln(5);
        $this->SetFont('Arial', '', 8);
        $this->cell(110, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->prc, 'B', 1, 'R');
        $this->cell(110, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->ptr, 'B', 1, 'R');

        $flwpdt = $this->data['appointment_detail']->followup ? date_format(date_create($this->data['appointment_detail']->followup), "F d, Y") : 'September 28, 2025';
        $this->Cell(76, 3, '', '', 1, '');
        $this->Cell(25, -9, "Next appointment: ", '', 0, 'C');
        $this->Cell(3, 3, '', 0, '');
        $this->Cell(55, -9, $flwpdt, '', 0, 'L');
        
        $this->Ln(1);
        $this->Cell(25, -9, '',  0, '');
        $this->Cell(40, -3, '', 'B', '');

        /* $this->cell(110, 3, "S2 License", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->s2, 'B', 1, 'R'); */
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

