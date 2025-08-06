<?php

namespace App\Http\Controllers\Api;

use Codedge\Fpdf\Fpdf\Fpdf;
use App\Model\Rx;
use App\Model\Ancillary;

class ChartRecordPdf extends Fpdf
{
  private $data;
  private $widths;
  private $aligns;

  public function __construct($data)
  {
    $this->data = $data;
    //parent::__construct('L', 'mm', array(216, 277));
    parent::__construct('L', 'mm', 'Legal');
    $this->SetTitle('CLINICAL CHART', true);
    $this->SetAuthor('TJGazel', true);
    $this->AddPage('P');
    $this->SetWidths(array(15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15));
    $this->Body();
  }
  public function Header()
  {
    $this->SetFont('Arial', 'B', 12);
  
    $this->Cell(15, 28, "", 0, 0, 'C');
    $this->Cell(165, 28, strtoupper("CHART"), 0, 0, 'C');

  }
  // Simple table
  function HeaderTable($data)
  {
    $this->SetFont('Arial', 'B', 12);
    $this->Ln(21);
    $this->SetFont('Arial', 'B', 10);
    $this->AliasNbPages();
    $this->Rect(11, 40, 115, 14);
    $this->SetXY(8, 40);
    $this->Cell(21, 5, 'Name', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');
    $this->SetFont('Arial', '', 10);

    $pname = explode(' ', $this->data['patient_detail']->patientname);
    $this->SetXY(32, 40);
    $this->Cell(11, 5, 'Last', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');
    $this->SetXY(8, 45);
    //$this->Cell(30, 7, strtoupper($pname[0]), 0, 0, 'C');
    $this->Cell(50, 7, strtoupper($this->data['patient_detail']->lastname), 0, 0, 'C');
    $this->SetFont('Arial', '', 10);

    $this->SetXY(57, 40);
    $this->Cell(40, 5, 'First', 0, 0, 'C');
    $this->SetFont('Arial', '', 9);
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');
    $this->SetXY(46, 45);
    //$this->Cell(15, 7, strtoupper($pname[1]), 0, 0, 'C');
    $this->Cell(60, 7, strtoupper($this->data['patient_detail']->firstname), 0, 0, 'C');
    $this->SetFont('Arial', '', 10);

    $this->SetFont('Arial', '', 10);
    $this->SetXY(80, 40);
    $this->Cell(50, 5, 'Middle', 0, 0, 'C');
    $this->SetFont('Arial', '', 9);
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->SetXY(70, 45);
    $this->Cell(70, 7, strtoupper(mb_substr($this->data['patient_detail']->middlename, 0, 1)), 0, 0, 'C');
    $this->Cell(70, 7, '', 0, 0, 'C');

    $this->Rect(126.1, 40, 10, 14);

    $this->SetFont('Arial', '', 10);
    $this->SetXY(121.1, 40);
    $this->Cell(20, 5, 'Age', 0, 0, 'C');
    $this->SetFont('Arial', '', 9);
    $this->ln(5);
    $this->SetXY(89, 45);
    $this->Cell(85, 7, date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y, 0, 0, 'C');

    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');

    $this->Rect(136, 40, 10, 14);

    $this->SetFont('Arial', '', 10);
    $this->SetXY(131.1, 40);
    $this->Cell(20, 5, 'Sex', 0, 0, 'C');
    $this->SetFont('Arial', '', 9);
    $this->ln(5);
    $this->SetXY(99, 45);
    $this->Cell(85, 7, $this->data['patient_detail']->sex == 1 ? 'M' : 'F', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');


    $this->Rect(146.1, 40, 20, 14);

    $this->SetFont('Arial', '', 9);
    $this->SetXY(147.1, 40);
    $this->Cell(20, 5, 'Civil Status', 0, 0, 'C');
    $this->SetFont('Arial', '', 9);
    $this->ln(5);
    $this->SetXY(114, 45);
    $this->Cell(85, 7, strtoupper($this->data['patient_detail']->civil_status), 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');

    $this->Rect(166.1, 40, 38, 14);

    $this->SetFont('Arial', '', 10);
    $this->SetXY(175.1, 40);
    $this->Cell(20, 5, 'Birth Date', 0, 0, 'C');
    $this->SetFont('Arial', '', 9);
    $this->ln(5);
    $this->SetXY(165, 45);
    $this->Cell(35, 7, date_format(date_create(strtoupper($this->data['patient_detail']->birthdate)), 'F d, Y'), 0, 0, 'C');

    $this->MultiCell(40, 4, $data->doctor, '', 'C', 0);
    $this->SetFont('Arial', 'B', 10);
    $this->Cell(0.1, 5, '', 0, 0, 'C');


    $this->Rect(11, 54, 193.1, 14);
    $this->SetFont('Arial', 'B', 10);
    $this->SetXY(17, 54);
    $this->Cell(21, 5, 'Patient Address', 0, 0, 'C');
    $this->ln(5);

    $this->SetFont('Arial', '', 9);
    $this->SetXY(13, 59);
    $this->MultiCell(100, 4, strtoupper($this->data['patient_detail']->address), '', 'L', 0);
    $this->Cell(0.1, 5, '', 0, 0, 'C');

    /*DATE AND TIME*/
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, 70, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(18, 70);
    $this->Cell(26, 5, 'Date time & Vital Signs', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0.1, 5, '', 0, 0, 'C');

    $this->Rect(11, 70, 193, 12);
    $this->SetFont('Arial', '', 9);

    $this->SetXY(12, 78);
    $diagnose_dt = date_create($data->appointment_dt);
    $this->MultiCell(193, 2, " Date:" . date_format($diagnose_dt, "M d, Y") . "         Weight: " . $data->weight. "        Height: " . $data->height . "       Temperature: " . $data->vit_temp .  "        BMI: " . $data->bmi . " \n", '', 'L', 0);
    $this->Cell(0.1, 5, '', 0, 0, 'C');
    //CHIEF COMPLAINTS
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, 84, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, 84);
    $this->Cell(33, 5, 'CHIEF COMPLAINTS', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, 91);

    // Calculate the height of the MultiCell content
    $ccHeight = $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(193, 3, $data->chiefcomplaints, '', 'L', 0);

    //HISTORY
    $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY+5, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY+5);
    $this->Cell(15, 5, 'HISTORY', 0, 0, 'C');
    $this->Ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, $currentY + 15); // Adjust based on the new Y position
    // Get the height of OB & Menstrual content and output it
    $this->MultiCell(193, 4, $data->history, '', 'L', 0);

    //P.E
    $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY+5, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY+5);
    //$this->Cell(-2, 5, 'HPI', 0, 0, 'C');
    $this->Cell(7, 5, 'P.E', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, $currentY + 15);
    $hpiHeight = $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(193, 3, $data->pe, '', 'L', 0);

    //diagnosis
    $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY+5, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY+5);
    $this->Cell(17, 5, 'Diagnosis', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, $currentY+ 15);
    $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(190, 3, $data->diagnosis, '', 'L', 0);

    //PE
    $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY+5, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY+5);
    $this->Cell(16, 5, 'Remarks', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, $currentY+15);
    $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(190, 3, $data->remarks, '', 'L', 0);

    //PMHX
    /* $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY);
    $this->Cell(10, 5, 'pmHx', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 7);
    $this->SetXY(11, $currentY+7);
    $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(190, 3, $data->pmhx, '', 'L', 0); */

    //Recommendations
    /* $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY);
    $this->Cell(30, 5, 'Recommendations', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 7);
    $this->SetXY(11, $currentY+7);
    $diagnose_dt = date_create($data->pmhx);
    $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(190, 3, $data->recommendations, '', 'L', 0); */

    //Rx
    $getPrescriptions = Rx::where(['appointment_id' => $data->id])->get();
    $prescriptions = '';
    $ar = array();
    foreach ($getPrescriptions as $key => $value) {
      $prescriptions .= ($key + 1) . '.) ' . strtoupper($value->generic_name).' '. strtoupper($value->medicine) . "\r\n";
      $ar[] = ($key + 1) . '.) ' . strtoupper($value->medicine) .  ",";
    }

    $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY+5, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY+5);
    $this->Cell(20, 5, 'Medications', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, $currentY+15);
    $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(190, 3, $prescriptions, '', 'L', 0);

    //Diagnostics
    $getAncillary = Ancillary::where(['appointment_id' => $data->id])->get();
    $ancillary = '';
    $ar = array();
    foreach ($getAncillary as $key => $value) {
      $ancillary .= ($key + 1) . '.) ' . strtoupper($value->ancillary) . " "."\r\n";
      $ar[] = ($key + 1) . '.) ' . strtoupper($value->ancillary) . ",";
    }

    $currentY = $this->GetY();
    $this->SetFillColor(211, 211, 211);
    $this->Rect(11, $currentY+5, 193, 6, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->SetXY(11, $currentY+5);
    $this->Cell(20, 5, 'Diagnostics', 0, 0, 'C');
    $this->ln(5);
    $this->SetFont('Arial', '', 10);
    $this->SetXY(11, $currentY+15);
    $this->getMultiCellHeight($this, 193, 3, "");
    $this->MultiCell(190, 3, $ancillary, '', 'L', 0);
  }

  public function Body()
  {
    $width_cell = array(125, 15, 30, 25, 15, 80, 35, 65, 15, 30, 25, 55, 55, 30, 30, 30, 60, 35, 15, 25);
    $this->SetWidths(array(125, 15, 30, 25, 15, 80, 35, 65, 15, 30, 25, 55, 55, 30, 30, 30, 60, 35, 15, 25));
    for ($i = 0; $i < sizeof($this->data['getHistory']); $i++) {

      $this->HeaderTable($this->data['getHistory'][$i]);
      $this->AddPage('P');
      $this->FooterTable();
    }
  }

  public function Footer()
  {
  }

  public function FooterTable()
  {
    $this->SetY(-10);
    $this->SetFont('Arial', 'B', 50);
    $this->SetFont('Arial', '', 5);
    $this->SetAutoPageBreak(true, 25);
  }

  function SetWidths($w)
  {
    $this->widths = $w;
  }

  function SetAligns($a)
  {
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

  function WordWrap(&$text, $maxwidth)
  {
    $text = trim($text);
    if ($text === '')
      return 0;
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;

    foreach ($lines as $line) {
      $words = preg_split('/ +/', $line);
      $width = 0;

      foreach ($words as $word) {
        $wordwidth = $this->GetStringWidth($word);
        if ($wordwidth > $maxwidth) {
          // Word is too long, we cut it
          for ($i = 0; $i < strlen($word); $i++) {
            $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
            if ($width + $wordwidth <= $maxwidth) {
              $width += $wordwidth;
              $text .= substr($word, $i, 1);
            } else {
              $width = $wordwidth;
              $text = rtrim($text) . "\n" . substr($word, $i, 1);
              $count++;
            }
          }
        } elseif ($width + $wordwidth <= $maxwidth) {
          $width += $wordwidth + $space;
          $text .= $word . ' ';
        } else {
          $width = $wordwidth + $space;
          $text = rtrim($text) . "\n" . $word . ' ';
          $count++;
        }
      }
      $text = rtrim($text) . "\n";
      $count++;
    }
    $text = rtrim($text);
    return $count;
  }

  function getMultiCellHeight($pdf, $w, $h, $text)
  {
    // Save the current position
    $startY = $pdf->GetY();

    // Output the multicell in test mode (no page break or output)
    $pdf->MultiCell($w, $h, $text, 0, 'L', false);

    // Calculate the height by subtracting start Y from the current Y
    $endY = $pdf->GetY();
    $height = $endY - $startY;

    // Return to the original Y position
    $pdf->SetY($startY);

    return $height;
  }
}
