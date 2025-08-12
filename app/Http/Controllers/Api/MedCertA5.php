<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
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
        $this->Image(public_path() . '/img/lim_fb.png', 130, 5, 16, 16, 'PNG');
        $this->Image(public_path() . '/img/lim_rhuema.jpg', 114, 6, 15, 15, 'JPG');
        $this->Image(public_path() . '/img/cp.jpg', 98, 6, 15, 15, 'JPG');
        $this->Ln(1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(68, 3, strtoupper($this->data['profile']->name), 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Ln(1);
        $this->Cell(41, 8, 'Fellow, Philippine College of Physicians', 0, 0, 'R');
        $this->Ln(1);
        $this->Cell(50.5, 11, 'Diplomate, Philippine Rheumatology Association', 0, 0, 'R');
        $this->Ln(1);
        $this->Cell(33.5, 14, 'Email: jplimmd.clinic@gmail.com', 0, 0, 'R');
        $this->SetLineWidth(0.5);
        $this->Line(5, 23, 145, 23);
        $this->SetFont('Arial', 'B', 7);
        $this->Ln(0.05);
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(1, -8, '', '', 0, '');
        $this->Cell(116, 2, strtoupper($this->data['profile']->specialization1), 0, 0, 'R');
        $this->Ln(9);
        $this->SetFont('Arial', '', 7);
        $this->Cell(115, -17, strtoupper($this->data['profile']->specialization2), 0, 0, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', '', 7);
        $this->SetXY(7, 14);
        $this->SetFont('Arial', 'B', 4.5);
        $this->SetXY(5, 24);
        $this->MultiCell(62, 3, "Room 504. Riverside Medical", 0, 'L');
        $this->SetXY(5, 26.5);
        $this->MultiCell(62, 3, "Arts Building, BS Aquino Drive, Bacolod ", 0, 'L');
        $this->SetXY(5, 29);
        $this->MultiCell(61.5, 3, "Schedule: Mon-Wed-Fri: 1:00 PM - 5:00 PM ", 0, 'L');
        $this->SetXY(5.2, 31.5);
        $this->MultiCell(62, 3, "For appointment: 0962-484-5664 ", 0, 'L');
        $this->SetXY(5.2, 34);
        $this->MultiCell(150, 3, "Hospital Affiliations: Dr. Pablo O. Torre Memorial Hospital, Metro Bacolod Hospital and Medical Center, Bacolod Queen of Mercy Hospital, Adventist Medical Center-Bacolod", 0, 'L');

        $this->SetXY(40, 24);
        $this->MultiCell(62, 3, "Room 415. Metro Bacolod Hospital and Medical", 0, 'L');
        $this->SetXY(40, 26.5);
        $this->MultiCell(62, 3, "Center, Brgy. Estefania, Bacolod", 0, 'L');
        $this->SetXY(40, 29);
        $this->MultiCell(61.5, 3, "Schedule: Tue-Thu: 9:00 AM - 12:00 PM ", 0, 'L');
        $this->SetXY(40.2, 31.5);
        $this->MultiCell(62, 3, "For appointment: 0968-418-7873", 0, 'L');


        $this->SetXY(78, 24);
        $this->MultiCell(62, 3, "VitalRx Pharmacy and Arthritis Clinic, JTL", 0, 'L');
        $this->SetXY(78, 26.5);
        $this->MultiCell(62, 3, "Building, BS Aquino Drive, Bacolod", 0, 'L');
        $this->SetXY(78, 29);
        $this->MultiCell(61.5, 3, "Schedule: Mon-Wed-Fri: 9:00 AM - 12:00 PM ", 0, 'L');
        $this->SetXY(78.2, 31.5);
        $this->MultiCell(62, 3, "For appointment.: 0966-073-6942", 0, 'L');

        $this->SetXY(113, 24);
        $this->MultiCell(62, 3, "Agustin Medical Clinic ", 0, 'L');
        $this->SetXY(113, 26.5);
        $this->MultiCell(62, 3, "Sen Jose Locsin Street, Brgy. V, Silay ", 0, 'L');
        $this->SetXY(113, 29);
        $this->MultiCell(61.5, 3, "Schedule: Thursday 1:30 PM - 4:30 PM", 0, 'L');
        $this->SetXY(113.2, 31.5);
        $this->MultiCell(62, 3, "For appointment: 0928-259-8495", 0, 'L');

        $this->SetLineWidth(0.5);
        $this->Line(5, 37, 145, 37);
        $this->Ln(10);
    }



    public function Body()
    {
        $age = date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y;
        $maritalStatus = $this->data['patient_detail']->civil_status;
        $gender = $this->data['patient_detail']->sex == 2 ? 'Female' : 'Male';
        $consultDate = date_format(date_create($this->data['appointment_detail']->appointment_dt), 'F d, Y');
        $undersigned_dt = date_format(date_create($this->data['appointment_detail']->medcert_undersigned), 'F d, Y');
        $reason = str_replace(["\r", "\n"], '', $this->data['appointment_detail']->chiefcomplaints);
        $name = $this->data['patient_detail']->patientname;
        $address = $this->data['patient_detail']->address;
        $sex = $this->data['patient_detail']->sex == 1 ? 'Male' : 'Female';
        $assessment = $this->data['appointment_detail']->medcert_diagnosis;
        $recommendation = $this->data['appointment_detail']->medcert_remarks;
        $remarks = $this->data['appointment_detail']->medcert_remarks;

        $this->SetFont('Arial', '', 8);
        $this->SetXY(100, 44);
        $this->Cell(0, 10, 'Date: ' . $undersigned_dt, 0, 1, 'C');
        $this->SetFont('Arial', 'B', 11);
        $this->SetXY(10, 40);
        $this->MultiCell(0, 10, 'MEDICAL CERTIFICATE', 0, 'C');

        /* $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 10, 'To whom it may concern:', 0, 1, 'L'); */
        $this->Ln(3);
        $this->SetFont('Arial', '', 9);
        //$this->MultiCell(0, 5, "            This is to certify that $name, $age years old, $sex, $maritalStatus, currently residing at $address, consulted on $consultDate due to $reason.", 0, 'L');
        /* $this->MultiCell(0, 5, "            This is to certify that", 0, 'L');
        $this->SetY(73);
        $this->MultiCell(0, 5, "$name", 0, 'L'); */


        //$name = "John Doe";
        //$age = 30;
        //$sex = "Male";
        //$maritalStatus = "Single";
        //$address = "123 ABC Street, City";
        //$consultDate = "July 10, 2025";
        //$reason = "persistent cough and fever";

        $paragraph = [
            ['text' => 'This is to certify that '],
            ['text' => $name, 'style' => 'BU'],
            ['text' => ", $age years of age", 'style' => 'BU'],
            ['text' => strtolower(", $gender, ")],
            ['text' => 'presently residing at '],
            ['text' => $address, 'style' => 'BU'],
            ['text' => "has been seen and examined on "],
            ['text' => "$consultDate ", 'style' => 'BU'],
            ['text' => "with the diagnosis "],
            ['text' => "$reason.", 'style' => 'BU'],
        ];

        $this->WriteStyledText($paragraph);

        $this->Ln(3);
        // Assessment/Impression
        $this->SetFont('Arial', '', 9);
        $this->Cell(-1, 6, '', 0, 0, '');
        $this->MultiCell(0, 4, "Medications have been prescribed, and the following recommendations have been provided, barring any complications:");


        // Recommendation
        $this->SetY($this->GetY() + 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 12, 'Recommendation/s:', 0, 1);

        $this->Cell(5);
        $this->SetFont('Arial', '', 9);
        //$this->Cell(5, 5, '', 1); // Checkbox
        //$this->Cell(5, 5, 'x', 1, 0, 'C');
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' Rest for ___ days', 0, 1);
        /* $this->Cell(0, 5, '', 0, 1);
        $this->Cell(5, 5, $this->writeUnderlinedText( 21, 120, 'He/She needs medical attention/rest',5,false,5).
        $this->writeUnderlinedText( 80, 120, '19',0,true,0).
        $this->writeUnderlinedText( 85, 120, ' day/s except if with complications',5,false,5), 0); */

        $this->Cell(5);
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' Fit to work', 0, 1);

        $this->Cell(5);
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' Referal to ______________________________________________', 0, 1);

        $this->Cell(5);
        $this->Cell(5, 5, '', 1, 0, 'C');
        $this->Cell(0, 5, ' ______________________________________________________', 0, 1);
        /* $this->Cell(10, 5, '', 0, 0);
        $this->Cell(0, 5, ' his/her ______________________ on ______________________', 0, 0);
        $this->Ln(3); */

        // Remarks
        /* $this->SetFont('Arial', 'B', 7);
        $this->Cell(0, 6, 'Remarks:', 0, 1, 'L');
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(0, 5, $remarks); */
        $this->Ln(3);

        // Disclaimer
        $this->SetFont('Arial', 'I', 7);
        $this->MultiCell(0, 4, "This certificate is issued upon the request of the patient for whatever purpose it may serve excluding medico-legal matters and is not admissible in court. \n\n\n Thank you.");

    }

    function WriteStyledText($lines, $lineHeight = 6, $indent = 10, $width = 190)
    {
        $this->SetX($indent);
        $lineWidthLimit = $width - $indent;
        $currentLineWidth = 0;

        foreach ($lines as $part) {
            $text = $part['text'];
            $style = $part['style'] ?? '';
            $this->SetFont('Arial', $style, 9);

            $words = explode(' ', $text);

            foreach ($words as $word) {
                $wordWidth = $this->GetStringWidth($word . ' ');
                if ($currentLineWidth + $wordWidth > $lineWidthLimit) {
                    /* $this->Ln($lineHeight);
                    $this->SetX($indent); */
                    $currentLineWidth = 0;
                }

                $this->Write($lineHeight, $word . ' ');
                $currentLineWidth += $wordWidth;
            }
        }

        $this->Ln($lineHeight); // final line break
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


        $this->SetAutoPageBreak(true, 25);
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

    function writeUnderlinedText($x, $y, $text, $line_length, $withline, $Xcoordinate, $pos = "L")
    {
        $this->SetXY($x, $y); // Set the position
        $this->Cell(0, 6, $text, 0, 1, $pos); // Write the text

        // Get the width of the text
        $textWidth = $this->GetStringWidth($text);

        // Draw a line under the text
        if ($withline) {
            //$this->Line($x, $y + 5, $x + $textWidth+$line_length, $y + 5); // y + 6 adjusts for line position
            $xcor = $x . (int) $Xcoordinate;
            $this->Line($x + $Xcoordinate, $y + 5, $x + $textWidth + $line_length, $y + 5); // y + 6 adjusts for line position
        }
    }
}

