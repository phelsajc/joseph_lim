<?php
namespace App\Http\Controllers\Api;

use TCPDF;

class MYPDF extends TCPDF
{

    private $pdfHelper;
    /*  public function __construct(MYPDF $pdfHelper)
     {
         $this->pdfHelper = $pdfHelper;
     } */

    //Page header

    public function Header()
    {
        $this->Image(public_path() . '/img/lim_fb.png', 132, 6, 11, 11, 'PNG');
        $this->Image(public_path() . '/img/lim_rhuema.jpg', 120, 6, 11, 11, 'JPG');
        $this->Image(public_path() . '/img/cp.jpg', 108, 6, 11, 11, 'JPG');
        $this->Ln(1);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(42, 4, 'JOSEPH PETER T. LIM, MD', 0, 0, 'R');
        $this->SetFont('helvetica', 'B', 9);
        $this->Ln(1);
        $this->Cell(50, 10, strtoupper($this->Getdata['profile']->specialization1.' - '.$this->Getdata['profile']->specialization2), 0, 0, 'R');
        $this->Ln(5);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(38, 11, 'Fellow, Philippine College of Physicians', 0, 0, 'R');
        $this->Ln(1);
        $this->Cell(49, 15, 'Diplomate, Philippine Rheumatology Association', 0, 0, 'R');
        $this->Ln(1);
        $this->Cell(29.5, 19, 'Email: jplimmd.clinic@gmail.com', 0, 0, 'R');     
        $this->SetLineWidth(0.5);
        $this->SetFont('helvetica', 'B', 7);
        $this->Ln(0.05);
        $this->Ln(5);
        $this->Ln(10);
        $this->SetFont('helvetica', '', 7);
        $this->SetXY(7, 20);
        $this->SetFont('helvetica', 'B', 4.5);
        $this->SetXY(75.5, 17);
        $this->MultiCell(62, 3, "Room 504. Riverside Medical", 0, 'L');
        $this->SetXY(75.5, 19);
        $this->MultiCell(62, 3, "Arts Building, BS Aquino Drive, Bacolod ", 0, 'L');
        $this->SetXY(75.5, 21);
        $this->MultiCell(61.5, 3, "Schedule: Mon-Wed-Fri: 2:00 PM - 5:00 PM ", 0, 'L');
        $this->SetXY(75.5, 23);
        $this->MultiCell(62, 3, "For appointment: 0962-484-5664 ", 0, 'L');
        $this->SetXY(1, 34);
        $this->SetFont('helvetica', 'B', 5);
        $this->MultiCell(150, 5, "Hospital Affiliations: Dr. Pablo O. Torre Memorial Hospital, Metro Bacolod Hospital and Medical Center, Bacolod Queen of Mercy Hospital, Adventist Medical Center-Bacolod", 0, 'L');

        $this->SetFont('helvetica', 'B', 4.5);
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

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        #$this->SetY(-15);
        // Set font
        #$this->SetFont('helvetica', 'I', 8);
        // Page number
        #$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
   
        
        
        $this->SetY(-20);
        $this->SetFont('helvetica', 'B', 7);
        $PageNo = intval($this->PageNo());
        if (!empty($this->Getdata['profile']->signature)) {
            $signature = $this->Getdata['profile']->signature;
        
            // remove "data:image/png;base64," if it exists
            if (strpos($signature, 'base64,') !== false) {
                $signature = explode('base64,', $signature)[1];
            }
        
            // decode and create temporary file
            $imgData = base64_decode($signature);
            $tempFile = tempnam(sys_get_temp_dir(), 'sig_');
            file_put_contents($tempFile, $imgData);
        
            // insert the image into PDF
            $this->Image($tempFile, 85, 180, 110, 20, 'PNG');
        
            // remove the temp file after
            unlink($tempFile);
        }
        
        $this->Cell(35, 10, '', '', 0, '');
        $this->cell(85, -3, strtoupper($this->Getdata['profile']->name), '', 0, 'R');
        $this->Ln(3);
        $this->SetFont('helvetica', '', 7);
        $this->cell(100, 3, "License No:", '', 0, 'R');
        $this->cell(20, 3, $this->Getdata['profile']->prc, 'B', 1, 'R');
        $this->cell(100, 3, "PTR. No.", '', 0, 'R');
        $this->cell(20, 3, $this->Getdata['profile']->ptr, 'B', 1, 'R');
    }

    function Getdata($data)
    {
        return $data;
    }
}

class PdfService extends TCPDF
{

    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function generatePdf()
    {
        // $pdf = new TCPDF();
        /* $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Laravel TCPDF Example');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Hello, Laravel with TCPDF!', 0, 1, 'C');
        
        return $pdf->Output('example.pdf', 'I'); // 'I' for inline, 'D' for download */
        $arr = array();
        $arr['test'] = 'testxxxxxxxxxxxxxx';
        //  $myPdf = new MYPDF($arr);
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A5', true, 'UTF-8', false);
        $pdf->Getdata = $this->data;
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example 003');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 8);

        // add a page
        $pdf->AddPage();

        // set some text to print
        $txt = $this->data['appointment_detail']->form_content;

        // print a block of text using Write()
        //$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
        
        // Set line height to normal (1.0) before writing HTML
        $pdf->setHtmlVSpace(array(
            'p' => array(0 => array('h' => 0.5, 'n' => 0), 1 => array('h' => 0.5, 'n' => 0)),
            'br' => array(0 => array('h' => 0.5, 'n' => 0), 1 => array('h' => 0.5, 'n' => 0))
        ));
        
        // Use writeHTMLCell with normal line spacing
        $pdf->writeHTMLCell(130, 0, 10, 45, $txt, 0, 0, false, true, 'J', true);

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('example_003.pdf', 'I');

        //============================================================+
// END OF FILE
//============================================================+
    }
}
