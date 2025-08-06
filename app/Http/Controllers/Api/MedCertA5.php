<?php
namespace App\Http\Controllers\Api;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;

class MedCertA5 extends Fpdf
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
    }
    
    public function Body()
    {
        $age = date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y;
        $maritalStatus = $this->data['patient_detail']->civil_status;
        $consultDate = date_format(date_create($this->data['appointment_detail']->appointment_dt), 'F d, Y');
        $undersigned_dt = date_format(date_create($this->data['appointment_detail']->medcert_undersigned), 'F d, Y');
        $reason = $this->data['appointment_detail']->chiefcomplaints;
        $name = $this->data['patient_detail']->patientname;
        $address = $this->data['patient_detail']->address;
        $sex = $this->data['patient_detail']->sex==1?'Male':'Female';
        $assessment = $this->data['appointment_detail']->medcert_diagnosis;
        $recommendation = $this->data['appointment_detail']->medcert_remarks;
        $remarks = $this->data['appointment_detail']->medcert_remarks;        
        
        $this->SetFont('Arial', '', 8);
        $this->SetXY(100, 49); 
        $this->Cell(0, 10, 'Date: '.$undersigned_dt, 0, 1, 'C');
        $this->SetFont('Arial', 'B', 11);
        $this->SetXY(10, 60);
        $this->MultiCell(0, 10, 'MEDICAL CERTIFICATE', 0,  'C');
        
        /* $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 10, 'To whom it may concern:', 0, 1, 'L'); */
        $this->Ln(3);
        $this->SetFont('Arial', '', 9);
        //$this->MultiCell(0, 5, "            This is to certify that $name, $age years old, $sex, $maritalStatus, currently residing at $address, consulted on $consultDate due to $reason.", 0, 'L');
        /* $this->MultiCell(0, 5, "            This is to certify that", 0, 'L');
        $this->SetY(73);
        $this->MultiCell(0, 5, "$name", 0, 'L'); */

        $this->SetFont('Arial', '', 9);
        $this->SetX(10); // indent

        //$name = "John Doe";
        //$age = 30;
        //$sex = "Male";
        //$maritalStatus = "Single";
        //$address = "123 ABC Street, City";
        //$consultDate = "July 10, 2025";
        //$reason = "persistent cough and fever";
        
        $paragraph = [
            ['text' => 'This is to certify that patient '],
            ['text' => $name, 'style' => 'B'],
            ['text' => ", $age years of age"],
            //['text' => strtolower($sex), 'style' => 'B'],
            //['text' => ", $maritalStatus, currently residing at "],
            ['text' => strtolower(", $maritalStatus, ")],
            //['text' => $address, 'style' => 'B'],
            ['text' => "has consulted last $consultDate due to $reason."],
        ];
        
        $this->WriteStyledText($paragraph);

        $this->Ln(13);
        // Assessment/Impression
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 6, '', 0,0, '');
        $this->Cell(0, 6, 'Diagnosis:', 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(10, 6, '', 0,0, '');
        $this->MultiCell(0, 5, $assessment);
        $this->Ln(7);

        // Recommendation
        $this->SetFont('Arial', '', 9);
        $this->Cell(10, 6, '', 0,0, '');
        $this->Cell(0, 6, 'Recommendation:', 0, 1, 'L');
        $this->SetFont('Arial', '', 7);

        // Checkbox style recommendations
        $this->Cell(10, 5, '', 0, 0);
        //$this->Cell(105, 5, ($this->data['appointment_detail']->medcert_fit_reason? '[X] Fit to work '.$this->data['appointment_detail']->medcert_fit_reason : '[ ] Fit to work'), 0, 1);
        $this->SetFont('Arial', 'B', 9);
        //$this->Cell(105, 5, ($this->data['appointment_detail']->medcert_remarks), 0, 1);
        $this->Cell(1, 5, '', 0, 0);
        $this->MultiCell(105, 5, ($this->data['appointment_detail']->medcert_remarks));
        /* $this->Cell(1, 5, '', 0, 0);
        $this->MultiCell(105, 5, ($this->data['appointment_detail']->medcert_fit_for?'[X] Fit for '.$this->data['appointment_detail']->medcert_fit_for:'[ ] Fit for'), 0, 1);
        $this->Cell(1, 5, '', 0, 0);
        $this->Cell(5, 5, ($this->data['appointment_detail']->medcert_rest ? '[X] Rest for ' . $this->data['appointment_detail']->medcert_rest . ' days ': '[ ] Rest for'), 0, 1);
        $this->Cell(1, 5, '', 0, 0);
        $this->Cell(5, 5, ($this->data['appointment_detail']->medcert_referral ? '[X] Referral to '.$this->data['appointment_detail']->medcert_referral : '[ ] Referral to'), 0, 1); */

        $this->Ln(3);

        // Remarks
        /* $this->SetFont('Arial', 'B', 7);
        $this->Cell(0, 6, 'Remarks:', 0, 1, 'L');
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(0, 5, $remarks); */
        $this->Ln(3);

        // Disclaimer
        $this->SetFont('Arial', 'I', 7);
        $this->MultiCell(0, 4, "This certificate is being issued upon the request of the abovementioned name for whatever purpose it may serve, excluding legal matters.");

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

    function WriteStyledText($lines, $lineHeight = 6, $indent = 20, $width = 190)
    {
        $this->SetX($indent);
        $currentLine = '';
        $currentFont = '';
        
        foreach ($lines as $part) {
            $text = $part['text'];
            $style = $part['style'] ?? '';
            
            $this->SetFont('Arial', $style, 9);

            $words = explode(' ', $text);
            foreach ($words as $word) {
                $space = $currentLine === '' ? '' : ' ';
                $testLine = $currentLine . $space . $word;
                $lineWidth = $this->GetStringWidth($testLine);
                if ($lineWidth > $width - $indent) {
                    $this->MultiCell(0, $lineHeight, $currentLine, 0, 'L');
                    $this->SetX($indent);
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }

            if (!empty($currentLine)) {
                $this->SetFont('Arial', $style, 9);
                $this->Write($lineHeight, $currentLine . ' ');
                $currentLine = '';
            }
        }

        if (!empty($currentLine)) {
            $this->MultiCell(0, $lineHeight, $currentLine, 0, 'L');
        }
    }
}

