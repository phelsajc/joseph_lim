<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;

class Referral extends Fpdf
{
    private $data;
    private $leftMargin = 10;
    private $rightMargin = 10;
    private $topMargin = 10;
    private $bottomMargin = 10;
    private $lineHeight = 5;
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
    }
    
    public function Body()
    {
        $patient_detail = $this->data['patient_detail']->patientname.', '.date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y.' years old, ';
        $paited_detail2 = ($this->data['patient_detail']->sex==2?'Female, ':'Male, ').$this->data['patient_detail']->civil_status.' residing in ';
        $patient_address = $this->data['patient_detail']->address;
        $undersigned = $this->data['appointment_detail']->referral_undersigned?date_format(date_create($this->data['appointment_detail']->referral_undersigned),'F d, Y'):'';
        $this->Ln(17);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30,3,'','',0,'');
        $this->Cell(75,3," REFERRAL NOTE",'',0,'C');
        $this->Ln(10);
        $this->Cell(90,-100,'','',0,'');
        $this->Cell(40,-30,$undersigned,'',1,'C'); 
        $this->Ln(35);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(-2,5,'','',0,'');
        $this->MultiCell(115, 3,"Dear ".$this->data['appointment_detail']->referral_doctor	.",", '', 'L');
        $this->Ln(4);
        $this->SetFont('Arial', '', 10);
        $this->Cell(2,5,'','',0,'');        
        $this->SetFont('Arial', '', 10);
        $px = $this->data['patient_detail']->patientname;
        $x = 10;
        $y = 80;

        // Add styled text
        $parts = [
            ['text' => 'Respectfully referring ', 'style' => '','iscell'=>1],
            ['text' => $px, 'style' => 'B','iscell'=>1],
            ['text' => " who was seen and examined on ", 'style' => '','iscell'=>1],
            ['text' => date_format(date_create($this->data['appointment_detail']->appointment_dt),'F d, Y'), 'style' => 'B','iscell'=>1],
            ['text' => " and was diagnosed to have ", 'style' => '','iscell'=>1],
            ['text' =>$this->data['appointment_detail']->referral_diagnosis, 'style' => 'B','iscell'=>1],
            ['text' =>" for ", 'style' => '','iscell'=>1],
            ['text' =>$this->data['appointment_detail']->referral_remarks, 'style' => 'B','iscell'=>1],
        ];
        $this->TextWithStyles($x, $y, $parts);
        $this->Ln(12);
        $this->SetFont('Arial', '', 9);
        $this->Cell(5,3,'','',0,'');
        $this->Cell(19,3,"Thank you very much. ",'',0,'C');
        $this->Ln(12);
        $this->Cell(5,3,'','',0,'');
        $this->Cell(17,3,"Respectfully yours, ",'',0,'C');
    }
    
    public function Footer()
    {
        $this->SetY(-19);
        $this->SetFont('Arial', 'B', 8);
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
        $this->cell(20, 3, $this->data['profile']->ptr, 'B', 1, 'R'); $this->Cell(3, 3, '', 0, '');
        $this->SetAutoPageBreak(true, 25);
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

    function TextWithStyles($x, $y, $parts)
    {
        // Ensure the starting position is within the left and right margins
        if ($x < $this->leftMargin) {
            $x = $this->leftMargin;
        } elseif ($x > $this->w - $this->rightMargin) {
            $x = $this->w - $this->rightMargin;
        }

        // Ensure the starting position is within the top and bottom margins
        if ($y < $this->topMargin) {
            $y = $this->topMargin;
        } elseif ($y > $this->h - $this->bottomMargin) {
            $y = $this->h - $this->bottomMargin;
        }

        // Move to the starting position
        $this->SetXY($x, $y);

        $texti ='';
        $textiArr = array();
        // Handle each part, respecting new lines
        foreach ($parts as $part) {
            $text = $part['text'];
            $style = isset($part['style']) ? $part['style'] : '';
            $this->SetFont('Arial', $style, 9);
            $textiArr[]=$part;
            // Split the text into paragraphs
            
            if($part['iscell']==1){
                $paragraphs = explode(' ', $part['text']);
            }else{
                $paragraphs = explode("\n", $text);
            }

            foreach ($paragraphs as $paragraph) {
                $wordWidth = $this->GetStringWidth($paragraph . ' ');
                if ($x + $wordWidth > $this->w - $this->rightMargin) {
                    // Move to the next line
                    $x = $this->leftMargin;
                    $y += $this->lineHeight;
                    $this->SetXY($x, $y);
                }
                $texti .= $paragraph;
                // Print each paragraph using MultiCell
                $this->Cell(3,3,'','',0,'');
                if($part['iscell']==1){
                    $this->Cell($wordWidth, $this->lineHeight, $paragraph . ' ', 0, 0, '');
                }else{
                    $this->Ln(1);
                    $this->Cell(10,3,'','',0,'');
                    $this->MultiCell(0, $this->lineHeight, $paragraph, 0, 'L');
                    $this->Ln(1);
                }
                // Underline text if the style includes 'U'
                if (strpos($style, 'U') !== false) {
                    $currentX = $this->GetX();
                    $currentY = $this->GetY() - $this->lineHeight + 1; // Adjust Y position
                    $this->SetXY($this->leftMargin, $currentY); // Move to the beginning of the line

                    $this->Cell(10,3,'','',0,'');
                    $this->MultiCell(0, $this->lineHeight, str_repeat('_', strlen($paragraph)), 0, 'L');
                }

                if($part['iscell']==1){
                    $x += $wordWidth;
                    $this->SetX($x);
                }else{
                    // Move to the next line
                    $y = $this->GetY();
                    $this->SetXY($this->leftMargin, $y);
                }
    
            }
        }
    }
    
}

