<?php
namespace App\Http\Controllers\Api;

//use TJGazel\LaraFpdf\LaraFpdf;
use Codedge\Fpdf\Fpdf\Fpdf;

class MedCert extends Fpdf
{
    private $data;
    private $widths;
    private $aligns;

    public function __construct($data)
    {
        $this->data = $data;
        parent::__construct('P', 'mm', array(220.95 ,287.02 ));
        //parent::__construct('P', 'mm', 'A5');
        $this->SetTitle('My pdf title', true);
        $this->SetMargins(160, 8, 1); 
        $this->SetAuthor('TJGazel', true);
        $this->AddPage('O');
        $this->Body();
    }

    public function Header()
    { 
        $this->SetFont('Arial', 'B', 7);
        $this->Image( public_path().'/img/logo2023.jpg',161,7,15,15,'JPG');
        $this->Ln(1);
        $this->Cell(130, -5, strtoupper('MA. CLARISSA UY-PATRIMONIO, MD, FPOGS'), 0, 0, 'C');
        $this->Ln(0.05);
        $this->SetLineWidth(0.1);
        $this->Line(175,8,285,8);
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(130, -9, strtoupper('OBSTETRICS & GYNECOLOGY'), 0, 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 6);
        $this->Cell(130, -13, strtoupper("Urogynecologyy & Pelvic Reconstructive Surgery"), 0, 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(49.5, -15, strtoupper("CLINIC ADDRESS"), 0, 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 6);
        $this->Cell(76.5, -20, strtoupper("Dr. Pablo O. Torre Memorial Hospital"), 0, 0, 'C');  
        $this->Ln(5);
        $this->Cell(70, -25, strtoupper("Room 777 Medical Arts Building "), 0, 0, 'C');  
        $this->Ln(5);
        $this->Cell(69, -30, strtoupper("B.S. Aquino Drive, Bacolod City"), 0, 0, 'C');  
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(54, -45, strtoupper("CLINIC HOURS"), 0, 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 6);
        $this->Cell(209.5, -50, strtoupper("Mon - Fri - 10:00AM to 3:00 PM"), 0, 0, 'C');  
        $this->Ln(5);
        $this->Cell(215, -56, strtoupper("Sat /Holiday - 10:00AM to 12:00 PM"), 0, 0, 'C');  
        $this->Ln(5);
        $this->Cell(212, -62, strtoupper("Tel. No.: (034) 433-7331 Loc. 777"), 0, 0, 'C');  
        $this->Ln(5);
        $this->Cell(199.5, -68, strtoupper("Cell #: 0925-234-5678"), 0, 0, 'C');  
        $this->SetLineWidth(0.5);
        $this->Line(175,27,285,27);
        
        /* $this->Ln(-30);
        $this->SetFont('Arial', '', 7);
        $this->AliasNbPages();
        $this->cell(15, 3, '', '0', 0,'R');
        $this->cell(20, 3, 'Name of Patient :', 0, 0,'R');
        $this->cell(60, 3, strtoupper($this->data['patient_detail']->patientname), 'B', 0,'L');
        $this->SetFont('');
        $this->cell(-13, 3, '', 0, 0);
        $this->cell(22, 3, 'Sex :', 0, 0,'R');
        $this->cell(8, 3, strtoupper($this->data['patient_detail']->sex), 'B', 0,'R');
        $this->SetFont('');
        $this->cell(2, 3, '', 0, 0);
        $this->cell(5, 3, 'Age :', 0, 0);
        $this->cell(4, 3, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y, 'B', 0);
        $this->SetFont('');
        $this->Ln(5);
        $this->cell(14, 3, '', '0', 0,'R');
        $this->cell(20, 4, 'Address :', 0, 0);
        $this->cell(-8, 3, '','' ,0, 0);
        $this->cell(97, 4, $this->data['patient_detail']->address, 'B',0, 0);
        $this->Image(public_path() . '/img/rx.png', 175, 40, 4, 4, 'PNG'); */
    }

    public function mealHeader()
    {
        $this->SetFont('Arial', '', 7);
        $this->cell(15, 3, '', '0', 0,'R');
        
        $this->Cell(27, 5, "Medicine Name", 'LTR', 0, 'C');
        $this->Cell(7, 5, "QTY", "TR", 0, 'C');

        $this->Cell(12, 5, "Breakfast", 'T', 0, 'C');
        $this->Cell(12, 5, "Lunch", 1, 0, 'C');


        $this->Cell(12, 5, "Supper", 1, 0, 'C');

        $this->Cell(9, 5, "Bed", "TR", 0, 'C');


        $this->Cell(32, 5, "Remarks", "TR", 0, 'C');

        $this->Ln(5);
        $this->cell(15, 3, '', '0', 0,'R');

        $this->SetFont('Arial', '', 5);
        $this->Cell(27, 5, "", 'LBR', 0, 'C');

        $this->Cell(7, 5, "", "RB", 0, 'C');

        $this->Cell(6, 5, "B", 1, 0, 'C');//bf
        $this->Cell(6, 5, "A", 1, 0, 'C');
        

        $this->Cell(6, 5, "B", 1, 0, 'C');
        $this->Cell(6, 5, "A", 1, 0, 'C');

        $this->Cell(6, 5, "B", 1, 0, 'C');
        $this->Cell(6, 5, "A", 1, 0, 'C');


        $this->Cell(9, 5, "", "RB", 0, 'C');


        $this->Cell(32, 5, "", "RB", 0, 'C');
        $this->Ln(5);

        $this->SetWidths(
            array(
                27,
                7,
                6,
                6,
                6,
                6,
                6,
                6,
                9,
                32
            )
        );
    }

    public function meal()
    {
        $this->mealHeader();
        $this->SetFont('Arial', '', 7);
        foreach ($this->data['query_prescription'] as $key => $item) {
            $this->cell(15, 3, '', '0', 0,'R');
                $this->Row(
                    array(
                        $item['medicine'],
                        $item['qty'],
                        $item['breakfastbefore'],
                        $item['breakfastafter'],
                        $item['lunchbefore'],
                        $item['lunchafter'],
                        $item['supperbefore'],
                        $item['supperafter'],
                        $item['bedtime'],
                        $item['remarks']//10
                    )
                );
        }
    }

    public function Body()
    {
        $patient_detail = $this->data['patient_detail']->patientname.', '.date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y.' years old, ';
        $paited_detail2 = ($this->data['patient_detail']->sex==2?'Female, ':'Male, ').$this->data['patient_detail']->civil_status.' residing in ';
        $patient_address = $this->data['patient_detail']->address;
        $this->Ln(-20);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30,3,'','',0,'');
        $this->Cell(75,3," MEDICAL CERTIFICATE",'',0,'C');
        $this->Ln(10);
        $this->Cell(25,3,'','',0,'');
        $this->SetFont('Arial', '', 9);
        $this->Cell(40,3,"This is to certifiy that I have seen and examined ",'',0,'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(13,3,'','',0,'');
        $this->Cell(30,3,$patient_detail,'B',1,'L');
        $this->Ln(2);
        $this->Cell(9,13,'','',0,'');
        $this->Cell(113,3,$paited_detail2.$patient_address,'B',1,'C');
        $this->Ln(2);
        $this->Cell(9,13,'','',0,'');
        $this->Cell(113,3,'with the following findings: ','',1,'L');
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(9,13,'','',0,'');
        $this->Cell(113,3,$this->data['appointment_detail']->medcert_diagnosis,'B',1,'C');
        $this->Ln(3);
        $this->Cell(25,3,'','',0,'');
        $this->SetFont('Arial', '', 9);
        $this->Cell(1,3,"Recommendations: ",'',0,'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(13,3,'','',0,'');
        $this->Cell(83,3,$this->data['appointment_detail']->medcert_remarks,'B',1,'L');
        $this->Ln(3);
        $this->Cell(130,3,"This certification is issued upon the request of the above-stated individual for",'',1,'C');
        $this->Ln(2);
        $this->Cell(10,3,'','',0,'');
        $this->Cell(150,3,"whatever purpose it may serve him/her best.",'',0,'L');
        /* $this->Cell(2,3,'','',0,'');
        $this->Cell(10,3,'dgf','B',0,'L'); */
        $this->Ln(5);
        $this->SetFont('Arial', '', 7);
        $this->Cell(19,3,'','',0,'');
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
        $this->SetY(-17);
        $this->SetFont('Arial', 'B', 7);
        $this->cell(105, 3, "", '', 0, 'R');
        $this->cell(20, 3, strtoupper('MA. CLARISSA UY-PATRIMONIO, MD, FPOGS'), 'B', 1, 'R');
        $this->SetFont('Arial', '', 7);
        $this->cell(105, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, '111506  ', 'B', 1, 'R');
        $this->cell(105, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, '9719778', 'B', 1, 'R');
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

