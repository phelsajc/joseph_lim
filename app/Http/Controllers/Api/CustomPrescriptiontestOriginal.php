<?php
namespace App\Http\Controllers\Api;

use TJGazel\LaraFpdf\LaraFpdf;

class CustomPrescriptiontest extends LaraFpdf
{
    private $data;
    private $widths;
    private $aligns;


    public function __construct($data)
    {
        $this->data = $data;
        
        $pdf = new LaraFpdf();
       
        //parent::__construct('P', 'mm', array(215.9,139.7));
        #goodparent::__construct('P', 'mm', array(139.7,215.9));
        parent::__construct('P', 'mm', array(169.7,215.9));
        //parent::__construct('L', 'mm', array(144.3,138.9));
        //parent::__construct('L', 'mm', array(154.3,138.9));
        //parent::__construct('L', 'mm', array(139.7,115.9));
        //parent::__construct('L', 'in', array(4.25,5.5));
        //parent::__construct('L', 'mm', array(139.7,215.9));
        //$this->SetA4();--SET
        
        $this->SetTitle('My pdf title 123', true);
        $this->SetMargins(20, 10, 1); 
        $this->SetAuthor('TJGazel', true);
      
        $this->AddPage('O');
        
        $this->Body();
    }


    public function Header()
    {
        
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,'Title',1,0,'C');
        // Line break
        $this->Ln(20);
    }

    public function Body()
    {
        $this->SetFont('Arial','B',16);
        $this->Cell(40,10,'Hello World !',1);
        $this->Cell(60,10,'Powered by FPDF.',0,1,'C');
    }

    function BasicTable($header)
    {
        $this->SetFont('Arial', 'B', 6);

        // Column widths
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
        //$this->SetY(-17);
        $this->SetFont('Arial', 'B', 5);
        $this->SetFont('Arial', '', 5);
        /* if ($this->data['dctr_details']->signature) {
            $this->Image($this->data['dctr_details']->signature, 110, 138, 40, 20, 'png');
        } */
        $this->cell(105, 3, "", '', 0, 'R');
        $this->cell(20, 3, 'JOHN CARLO', 'B', 1, 'R');
        $this->cell(105, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, 123456, 'B', 1, 'R');
        $this->cell(105, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, 123456, 'B', 1, 'R');
        $this->cell(105, 3, "Narcotic Lic. No. (S2)", '', 0, 'R');
        $this->cell(20, 3, "", 'B', 1, 'R');
        $this->cell(105, 3, "Valid Until:", '', 0, 'R');
        //$this->cell(20, 3, $this->data['dctr_details']->validity ? date_format(date_create($this->data['dctr_details']->validity), 'F d,Y') : '', 'B', 1, 'R');
        $this->cell(20, 3, 'July 13, 1991', 'B', 1, 'R');
        $this->cell(20, 3, "", 'B', 1, 'R');
        $this->Cell(105, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
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

