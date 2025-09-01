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
        parent::__construct('P', 'mm', [148.5, 210]);
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
        $this->SetY(65);
        $this->MultiCell(115, 5,$this->data['appointment_detail']->referral_doctor, '', 'L');
        $this->Ln(h: 1);
        $this->MultiCell(115,5,$this->data['appointment_detail']->referral_addr1, '', 'L');
        $this->Ln(1);
        $this->MultiCell(115, 5,$this->data['appointment_detail']->referral_addr2, '', 'L');
        $this->SetY(90);
        $this->Cell(0.1,5,'','',0,'');
        $this->MultiCell(115, 3,"Dear ".$this->data['appointment_detail']->referral_doctor	.",", '', 'L');
        $this->Ln(4);
        $this->SetFont('Arial', '', 10);
        $this->Cell(2,5,'','',0,'');        
        $this->SetFont('Arial', '', 10);
        $px = $this->data['patient_detail']->patientname;
        $x = 10;
        $y = 94;

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
        $this->SetFont('Arial', '', 10);
        $this->Cell(5,3,'','',0,'');
        $this->Cell(29,3,"Thank you very much. ",'',0,'C');
        $this->Ln(12);
        $this->Cell(5,3,'','',0,'');
        $this->Cell(25,3,"Respectfully yours, ",'',0,'C');
    }
    
    public function Footer()
    {
        $this->SetY(-19);
        $this->SetFont('Arial', 'B', 8);
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 77, 178, 110, 20, 'png');
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
            $this->SetFont('Arial', $style, 10);
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

