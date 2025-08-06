<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;

class MedCertA5_O extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;

    public function __construct($data)
    {
        $this->data = $data;
        //parent::__construct('P', 'mm', array(220.95 ,287.02 ));
        parent::__construct('P', 'mm', 'A5');
        $this->SetTitle('My pdf title', true);
        //$this->SetMargins(160, 8, 1); 
        $this->SetAuthor('TJGazel', true);
        $this->AddPage('P');
        $this->Body();
    }
    
    public function Header()
    { 
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(-10, 5, '', 0, 0);
        $this->Cell(65, -5, 'KATRINA KAYE S. AGUSTIN, MD, FPCP', 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Ln(5); // Small gap
        $this->Cell(-10, 5, '', 0, 0);
        $this->Cell(65, 1, 'Adult Pulmonary Medicine', 0, 1, 'L');
        $this->Cell(-10, 5, '', 0, 0);
        $this->Cell(65, 5, 'Internal Medicine', 0, 1, 'L');

        $this->Ln(2); // Small gap
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(-10, 5, '', 0, 0);
        $this->Cell(25, 5, 'ALVIN C. AGUSTIN, MD, DPPS', 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(-10, 5, '', 0, 0);
        $this->Cell(65, 1, 'Ambulatory Pediatrics', 0, 1, 'L');
        $this->Ln(5); // Gap for Facebook logo
        $this->Image(public_path() . '/img/fb.png', -3, 27, 12); // Adjust icon size (x=5, y=35, width=5mm)
        $this->Cell(-5, 5, '', 0, 0);
        $this->Cell(65, 5, 'Agustin Medical Clinics', 0, 1, 'L');

        $this->SetDrawColor(0, 0, 0); // Black divider
        $this->SetLineWidth(0.4);
        $this->Line(74, 5, 74, 38); // Draw the vertical line (center)
        // Right Column: Contact Information
        $this->SetFont('Arial', '', 8);
        //$this->SetXY(76, 5); // Starting point for right column
        $this->Cell(70, 5, '', 0, 0);
        $this->Cell(30, -53, 'Clinic Numbers: 0928-259-8495 / (034) 213-21-75', 0, 0, 'L');
        //$this->Ln(2); // Small gap

        $this->Cell(-30, -35, '', 0, 0);
        $this->Cell(30, -40, 'Clinic Locations:', 0, 0, 'L');

        $this->SetFont('Arial', 'B', 7);
        $this->Cell(-30, -35, '', 0, 0);
        $this->Cell(0, -33, chr(149) . ' FAIRR Diagnostic Center', 0, 1, 'L'); // Bullet

        $this->Cell(71, 35, '', 0, 0);
        $this->Cell(68, 40, 'Sen. Jose Locsin St., Brgy. 5, Silay City (near Silay', 0, 1, 'L');
        $this->Cell(70.5, 35, '', 0, 0);
        $this->Cell(50, -33, ' City Health Office', 0, 1, 'L'); // Bullet

        $this->Ln(5); // Small gap
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(71, 5, '', 0, 0);
        $this->Cell(0, 35, chr(149) . ' Riverside Medical Arts Building (MAB): Room 520', 0, 1, 'L'); // Bullet
        $this->Cell(72, 5, '', 0, 0);
        $this->Cell(50, -28, ' BS Aquino Drive, Bacolod City', 0, 1, 'L'); // Bullet
        $this->Line(5, 38, 143, 38); // Footer horizontal line
        /* $this->Line(5,43,144,43); */
    }
    
    public function mealHeader()
    {
        $this->SetFont('Arial', '', 7);
        $this->cell(15, 3, '', '0', 0,'R');
        
        $this->Cell(100, 5, "Procedure", 'LTR', 0, 'C');

        $this->Ln(5);
        $this->cell(15, 3, '', '0', 0,'R');

        $this->SetFont('Arial', '', 5);
        $this->Cell(100, 5, "", 'LBR', 0, 'C');
        $this->Ln(5);

        $this->SetWidths(
            array(
                100,
            )
        );
    }

    public function meal()
    {
        $this->SetFont('Arial', '', 7);
        foreach ($this->data['query_prescription'] as $key => $item) {
            $this->cell(55, 3, '', '', 0,'R');
            $this->cell(5, 3, ($key+1).'.'.$item['ancillary'],0, 0,'L');
            $this->Ln(5);
        }
    }

    public function Body()
    {
        // Set title
        /* $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'AGUSTIN MEDICAL CLINIC', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Adult and Pediatric Consultation', 0, 1, 'C');
        $this->Cell(0, 6, '(034)476-57-37 / 0928-259-8495', 0, 1, 'C');
        
        // Clinic Addresses
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 6, 'FAIRR Diagnostic Center', 0, 1, 'L');
        $this->Ln(-2);
        $this->Cell(0, 6, 'Sen. Jose Locsin St., Brgy. 5, Silay City', 0, 1, 'L');
        $this->Ln(-2);
        $this->Cell(0, 6, '(Near Silay City Health Office)', 0, 1, 'L');
        
        $this->SetY(30);
        $this->Cell(0, 6, 'Riverside Medical Arts Building Room 520', 0, 1, 'R');
        $this->Ln(-2);
        $this->Cell(0, 6, 'BS Aquino Drive, Bacolod City', 0, 1, 'R'); */
        /* $this->Ln(-2);
        $this->Cell(0, 6, '(Infront of Colegio-San Agustin)', 0, 1, 'R'); */
        
        // Medical Certificate Title
        $this->SetY(45);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'MEDICAL CERTIFICATE', 0, 1, 'C');
        
        $this->Ln(5);
        // Form Content
        /* $this->SetFont('Arial', '', 10);
        $this->Cell(0, 8, 'This is to certify that', 0, 0);
        $this->Cell(-5, 8, 'dfgdfg', 0, 1);
        $this->Cell(0, 8, '____ yo/____ residing at ___________________________________________', 0, 1);
        $this->Cell(0, 8, 'has been examined at my clinic on _______________ with the following diagnosis:', 0, 1); */

        // Form Content - Using Cell for "This is to certify that" and blank line
        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 8, 'This is to certify that', 0, 0); // Fixed width for "This is to certify that"
        $this->Cell(-15, 8, '', 0, 0); // Variable/blank line for the name
        $this->Cell(10, 10,  $this->writeUnderlinedText( 43, 61, $this->data['patient_detail']->patientname.',',30,true,0,5), 0, 1); // Variable/blank line for the name
        
        // Age and Address Line
        //$this->Cell(50, 8, '', 0, 0); // Variable/blank line for the name
        $this->Cell(20, 8, $this->writeUnderlinedText( 10, 70, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y.' yo/,',0,true,0), 0, 0); // Age placeholder
        //$this->Cell(-1, 1, '', 0, 0); 
        $this->Cell(10, -3, $this->writeUnderlinedText( 22, 70, 'residing at ',76,false,5).$this->writeUnderlinedText( 40, 70, $this->data['patient_detail']->address,76,true,0,5), 0, 1);

        // Examination Details
        $this->Cell(0, 8, $this->writeUnderlinedText( 10, 80, 'has been examined at my clinic on ',5,false,5).$this->writeUnderlinedText( 25, 80, date_format(date_create($this->data['appointment_detail']->medcert_undersigned),'F d, Y'),15,true,80,"C").$this->writeUnderlinedText( 98, 80, ' with the following diagnosis:',5,false,5), 0, 1);

        // Diagnosis Box
        $this->Rect(10, $this->GetY()-5, 130, 20); // Adjusted for A5 size
        // Text inside the box
        $this->SetXY(10, 91); // Position cursor inside the box
        $this->SetFont('Arial', '', 10);

        // Sample diagnosis text
        $diagnosisText = "Patient shows symptoms of fever and cough. Advised rest for 3 days and  for 3 days and prescribed medicationPatient shows symptoms prescribed medicationPatient shows symptoms of fever and cough. Advised rest for 3 days and prescribed medication.Advised rest for 3 days and prescribed medication.";

        // Add text inside the box
        $this->MultiCell(130, 4, $this->data['appointment_detail']->medcert_diagnosis, 0, 'L'); // Adjust width to 130 (box width) and line height to 5

        // Ensure text stays within the box
        if ($this->GetY() > 100) { // 100 = 80 (start of box) + 20 (height of box)
            $this->SetXY(10, 100); // Stop at the bottom of the box
            //$this->Cell(130, 5, '...', 0, 0, 'R'); // Indicate overflow with ellipsis
        }
        // Recommendations
        $this->SetY($this->GetY() + 10);
        $this->Cell(0, 12, 'Recommendations:', 0, 1);
        
        $this->Cell(5);
        //$this->Cell(5, 5, '', 1); // Checkbox
        //$this->Cell(5, 5, 'x', 1, 0, 'C');
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' He/She needs medical attention/rest ___ day/s except if with complications', 0, 1);
        /* $this->Cell(0, 5, '', 0, 1);
        $this->Cell(5, 5, $this->writeUnderlinedText( 21, 120, 'He/She needs medical attention/rest',5,false,5).
        $this->writeUnderlinedText( 80, 120, '19',0,true,0).
        $this->writeUnderlinedText( 85, 120, ' day/s except if with complications',5,false,5), 0); */
        
        $this->Cell(5);
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' He/She is fit-for-work', 0, 1);
        
        $this->Cell(5);
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' He/She is fit-for-school', 0, 1);
        
        $this->Cell(5);
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' He/She has been administered with ________________________ vaccine on', 0, 1);
        $this->Cell(10, 5, '', 0, 0);
        $this->Cell(0, 5, ' his/her ______________________ on ______________________', 0, 0);

        /* $this->Cell(0, 5, '', 0, 1);
        $this->Cell(5, 5, $this->writeUnderlinedText( 21, 133, 'He/She has been administered with',5,false,5).
        $this->writeUnderlinedText( 78, 133, 'Covid 19xxx',0,true,0).
        $this->writeUnderlinedText( 92+($this->GetStringWidth('Covid 19xxx')/2), 133, ' vaccine on',5,false,5), 0);
        $this->Cell(5, 3, '', 0, 0);
        $this->Cell(0, 5, ' his/her ________________ on _______________', 0, 0);  */

        /* $this->Cell(0, 5, $this->writeUnderlinedText( 21, 138, 'his/her',0,false,5).
        $this->writeUnderlinedText( 33, 138, 'right arm next to shoulder',0,true,0).
        $this->writeUnderlinedText( 48+($this->GetStringWidth('right arm next to shoulder')), 138, 'on',0,false,0).
        $this->writeUnderlinedText( 53+($this->GetStringWidth('right arm next to shoulder')), 138, 'November 19,2024',5,true,0), 0); */
        
        // Physician’s Name and Signature
        /* $this->Ln(7);
        $this->Cell(0, 40, '', 0, 0); */
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 1, $this->writeUnderlinedText( 48, 156, strtoupper($this->data['profile']->name),20,true,-8), 0, 1, 'L');
        $this->Cell(40,19, '', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 1, 'Physician\'s Name and Signature', 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        
        // Date and Control Number
        $this->Ln(5);
        $this->Cell(40, -15, '', 0, 0);

       /*  $this->Cell(0, 5, '', 0, 1);
        $this->Cell(0, 5,
        $this->writeUnderlinedText( 53, 138, 'November 19,2024',5,true,0), 0); */

        //$this->Cell(0, 5, 'Date: _______________', 0, 1, 'L');
        $this->Cell(0, 5, $this->writeUnderlinedText( 43, 168, 'Date:',5,false,0).$this->writeUnderlinedText( 53, 168, '',50,true,0), 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(-40, -10, '', 0, 1);
        $this->Cell(40, -15, '', 0, 0);
        $this->Cell(-10, 1, 'This is not admissible in court', 0, 1, 'L');
        $this->Cell(40, -15, '', 0, 0);
        $this->Cell(0, 12, 'Control Number: _______________', 0, 1, 'L');
    }

    function BasicTable($header)
    {
        $this->SetFont('Arial', 'B', 6);
        $w = array(
            60,
            60
        );
        // Header
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 5, $header[$i], 1, 0, 'C');
        $this->Ln();

        $this->SetFont('Arial', '', 6);
        $this->SetWidths(
            array(
                60,
                60
            )
        );
        foreach ($this->data['query_diagnostic'] as $row) {
            $this->Row(
                array(
                    $row['diagnostic'],
                    $row['instructions']
                )
            );
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
    }

    public function Footer()
    {
        /* $this->SetY(-17);
        $this->SetFont('Arial', 'B', 10);
        if ($this->data['profile']->signature) {
            $this->Image($this->data['profile']->signature, 260, 190, 40, 20, 'png');
        }
        $this->cell(110, 3, "", '', 0, 'R');
        $this->Cell(-30,10,'','',0,'');
        $this->cell(50, 3, strtoupper($this->data['profile']->name), 'B', 1, 'R');
        $this->Ln(2);
        $this->SetFont('Arial', '', 10);
        $this->cell(110, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->prc, 'B', 1, 'R');
        $this->cell(110, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->ptr, 'B', 1, 'R');
        $this->SetAutoPageBreak(true, 25); */
    }

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
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
    
    function FancyRow($data, $border = array(), $align = array(), $style = array(), $maxline = array())
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        if (count($maxline)) {
            $_maxline = max($maxline);
            if ($nb > $_maxline) {
                $nb = $_maxline;
            }
        }
        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            // alignment
            $a = isset($align[$i]) ? $align[$i] : 'L';
            // maxline
            $m = isset($maxline[$i]) ? $maxline[$i] : false;
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            if ($border[$i] == 1) {
                $this->Rect($x, $y, $w, $h);
            } else {
                $_border = strtoupper($border[$i]);
                if (strstr($_border, 'L') !== false) {
                    $this->Line($x, $y, $x, $y + $h);
                }
                if (strstr($_border, 'R') !== false) {
                    $this->Line($x + $w, $y, $x + $w, $y + $h);
                }
                if (strstr($_border, 'T') !== false) {
                    $this->Line($x, $y, $x + $w, $y);
                }
                if (strstr($_border, 'B') !== false) {
                    $this->Line($x, $y + $h, $x + $w, $y + $h);
                }
            }
            // Setting Style
            if (isset($style[$i])) {
                $this->SetFont('', $style[$i]);
            }
            $this->MultiCell($w, 5, $data[$i], 0, $a, 0, $m);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function writeUnderlinedText($x, $y, $text, $line_length,$withline,$Xcoordinate,$pos = "L")
    {
        $this->SetXY($x, $y); // Set the position
        $this->Cell(0, 6, $text, 0, 1,$pos); // Write the text
        
        // Get the width of the text
        $textWidth = $this->GetStringWidth($text);

        // Draw a line under the text
        if($withline)
        {
            //$this->Line($x, $y + 5, $x + $textWidth+$line_length, $y + 5); // y + 6 adjusts for line position
            $xcor = $x.(int)$Xcoordinate;
            $this->Line($x+$Xcoordinate, $y + 5, $x + $textWidth+$line_length, $y + 5); // y + 6 adjusts for line position
        }
    }
}

