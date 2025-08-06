<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Generics;

class CustomPrescriptiontestA5PortraitBlank extends Fpdf
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
        $this->Image(public_path() . '/img/heart_center.png', 5, 2, 15, 15, 'PNG');
        $this->Image(public_path() . '/img/heart_assoc.png', 128, 2, 15, 15, 'PNG');
        $this->Ln(1);
        $this->SetFont('Arial', 'B', 12);
        //$this->Cell(130, -5, strtoupper('MA. CLARISSA UY-PATRIMONIO, MD, FPOGS'), 0, 0, 'C');
        $this->Cell(130, -9, strtoupper($this->data['profile']->name), 0, 0, 'C');
        $this->SetFont('Arial', 'B', 7);
        $this->Ln(0.05);
        //$this->SetLineWidth(0.1);
        //$this->Line(5, 8, 203, 8);
        $this->Ln(5);
        $this->SetFont('Arial', '', size: 7);
        //$this->Cell(130, -9, strtoupper('OBSTETRICS & GYNECOLOGY'), 0, 0, 'C');
        $this->Cell(20, -8, '', '', 0, '');
        $this->Cell(92, -8, strtoupper($this->data['profile']->specialization2), 0, 0, 'C');
        $this->Ln(9);
        $this->SetFont('Arial', '', 7);
        //$this->Cell(130, -13, strtoupper("Urogynecologyy & Pelvic Reconstructive Surgery"), 0, 0, 'C');
        //$this->Cell(5,-64, '', 0, 0);
        $this->Cell(130, -17, strtoupper($this->data['profile']->specialization1), 0, 0, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', '', 7);
        $this->SetXY(7, 14); // Set X=50, Y=30
        //$this->MultiCell(130, 3, "Diplomate, Philippine Specialty Board of Internal Medicine \n Fellow and Member, Philippine College of Physicians\n Member, Philippine Society of Hypertension\nMember, Philippine Lipid and Atherosclerosis Society\nMember, International Atherosclerosis Society", 0, 'C');

        //$this->Ln(15);
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(10, 35); // Set X=50, Y=30
        $this->Cell(37, -20, strtoupper("CLINIC HOURS"), 0, 0, 'C');
        $this->Cell(-9,-10, '', 0, 0, 'C');
        $this->Cell(-18.5,-8.5, '', 'B', 0, 'C');
        $this->Ln(5);
        //$this->Cell(4.5, 10, '', '', 0, '');
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(18, 30); // Set X=50, Y=30
        //$this->Cell(62, -25, "Clinic 4, Unit 203, 2nd Floor, HealthPoint \nMedical Clinic and Laboratory, Lacson St. ", 0, 0, 'L');
        $this->MultiCell(62, 3, "VLI Medical Plaza - Rm 233", 0, 'L');

        $this->SetFont('Arial', '', 6);
        $this->SetXY(18, 33); // Set X=50, Y=30
        //$this->MultiCell(130, 5, strtoupper("BS Aquino drive, Bacolod City\n Mon, Wed, Fri-9:00 am to 12:00 pm\nContact number: 0943-700-2950"),  0, 'L');
        $this->MultiCell(62, 3, "BS Aquino drive, Bacolod City\nMon, Wed, Fri-1:00 pm to 4:00 pm\nContact number: 0943-700-2950", 0, 'L');
        $this->SetFont('Arial', 'B', 7);
        
        $this->Cell(39,-20, '', 0, 0, 'C');
        $this->Cell(-30,6.5, '', 'B', 0, 'C');
        $this->SetXY(18, 50);
        $this->Cell(62, -5, "HOSPITAL AFFILIATIONS:", 0,0, 'L');

    
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 6);
        $this->SetXY(70, 41); // Set X=50, Y=30
        $this->Cell(80, -20, strtoupper("FAIRR Diagnostic Center and Agustin Medical Clinics"), 0, 0, 'C');
        $this->Ln(5);
        $this->SetXY(77, 33); // Set X=50, Y=30
        $this->SetFont('Arial', '',6);
        $this->MultiCell(62, 3, "Sen. Jose Locsin Street, Silay City\nThurs - 1:00 pm to 4:00 pm\nContact number: 0928-259-8495", 0, 'L');

        $this->SetFont('Arial', '', 6.5);        
        $this->SetXY(51, 46.5);
        $this->MultiCell(130, 3, "Dr. Pablo O. Torre Memorial Hospital, Bacolod Queen of Mercy Hospital, Metro Bacolod\nHospital and Medical Center, Bacolod Adventist Medical Center", 0, 'L');
        $this->SetLineWidth(0.5);
        $this->Line(5, 54, 145, 54);

        $this->Ln(7);
        $this->SetFont('Arial', '', 8);
        $this->AliasNbPages();
        $this->cell(15, 3, '', '0', 0, 'R');
        $this->cell(-3, 3, 'Name:', 0, 0, 'R');
        $this->cell(75, 3, strtoupper($this->data['patient_detail']->patientname), 'B', 0, 'L');
        //$this->MultiCell(75, 3, strtoupper($this->data['patient_detail']->patientname), 'B', 'L');
        $this->SetFont('');
        $this->cell(-13, 3, '', 0, 0);
        $this->cell(22, 3, 'Sex :', 0, 0, 'R');
        $this->cell(12, 3, strtoupper($this->data['patient_detail']->sex == 2 ? 'Female' : 'Male'), 'B', 0, 'R');
        $this->SetFont('');
        $this->cell(2, 3, '', 0, 0);
        $this->cell(8, 3, 'Age:', 0, 0);
        $this->cell(8, 3, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y, 'B', 0);
        $this->SetFont('');
        //$this->cell(15, 3, '', 'B', 1);
        $this->Ln(5);
        $this->cell(2, 3, '', '0', 0, 'R');
        $this->cell(5, 4, 'Address :', 0, 0);
        $this->cell(8, 3, '', '', 0, 0);
        $this->cell(85, 4, $this->data['patient_detail']->address, 'B', 0, 0);
        
        $this->cell(2, 3, '', 0, 0);
        $this->cell(8, 4, 'Date:', 0, 0);
        $this->cell(15, 4, date("m/d/Y"), 'B', 0);
        

        $this->Image(public_path() . '/img/rx.png', 12, 70, 9, 9, 'PNG');
    }

    public function mealHeader()
    {
        $this->SetFont('Arial', '', 7);
        $this->cell(6, 3, '', '0', 0, 'R');

        $this->Cell(65, 5, "Medicine Name", 'LTR', 0, 'C');
        $this->Cell(8, 5, "QTY", "TR", 0, 'C');

        $this->Cell(48, 5, "Remarks", "TR", 0, 'C');

        $this->Ln(5);
        $this->cell(6, 3, '', '0', 0, 'R');

        $this->SetFont('Arial', '', 5);
        $this->Cell(65, 5, "", 'LBR', 0, 'C');

        $this->Cell(8, 5, "", "RB", 0, 'C');


        $this->Cell(48, 5, "", "RB", 0, 'C'); 
        $this->Ln(5);

        $this->SetWidths(
            array(
                65,
                8,
                48
            )
        );
    }
    public function meal()
    {
        $this->mealHeader();
        $this->SetFont('Arial', '', 8.5);
        foreach ($this->data['query_prescription'] as $key => $item) {
            $checkGenericname = Generics::where(['id' => $item['generic_id']])->first();
            $this->cell(6, 3, '', '0', 0, 'R');
            $this->Row(
                array(
                    $item['generic_name'] . ' (' . $item['medicine'] . ')',
                    $item['qty'],
                    $item['remarks']//10
                )
            );
        }
    }

    public function Body()
    {
        $this->Cell(170, 6, '', '', 1, '');
        $this->Cell(168, 13, '', '', 0, '');
        $this->Cell(10, 3, "Date: ", '', 0, 'C');
        $this->Cell(15, 3, date("F d,Y"), 'B', 0, 'C');
        $this->Ln(10);
        //$this->Cell(10, 1, '', '', 1, '');
        $this->meal();
        /* $flwpdt = $this->data['appointment_detail']->followup ? date_format(date_create($this->data['appointment_detail']->followup), "F d,Y") : '';
        $this->Cell(76, 3, '', '', 1, '');
        $this->Cell(65, 3, "Your next appointment is on: ", '', 0, 'C');
        $this->Cell(-13, 3, '', '', 0, '');
        $this->Cell(25, 3, $flwpdt, 'B', 0, 'C'); */
        /* $this->Ln(2);
        $this->Cell(46, 3, '', '', 1, '');
        $this->Cell(40, 3, "Recommendations: ", '', 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 7);
        $this->Cell(19, 3, '', '', 0, '');
        $this->MultiCell(100, 3, $this->data['appointment_detail']->recommendations, 'B', 'L'); */
        //$this->Image(public_path() . '/img/izelyn_wm.png', 4, 65, 140, 0, 'PNG');
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

      
        /* $this->cell(110, 3, "S2 License", '', 0, 'R');
        $this->cell(20, 3, $this->data['profile']->s2, 'B', 1, 'R'); */
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
}