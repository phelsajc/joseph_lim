<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Mpdf\Mpdf;

class FormController extends Controller
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        // --- Create mPDF instance ---
        $mpdf = new Mpdf([
            'format' => [148.5, 210], // A5 portrait (matches your original)
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 40,  // space for header
            'margin_bottom' => 25, // space for footer
            'default_font' => 'Arial',
        ]);

        // --- Header ---
        $mpdf->SetHTMLHeader($this->headerHTML());

        // --- Footer ---
        $mpdf->SetHTMLFooter($this->footerHTML());

        // --- Body ---
        $html = $this->bodyHTML();

        // --- Write HTML ---
        $mpdf->WriteHTML($html);

        // --- Output inline ---
        return $mpdf->Output('form.pdf', 'I');
    }

    // HEADER SECTION
    private function headerHTML()
    {
        return '
        <div style="text-align:right; font-family: Arial; font-size: 8pt;">
            <img src="' . public_path('img/lim_fb.png') . '" width="30">
            <img src="' . public_path('img/lim_rhuema.jpg') . '" width="30">
            <img src="' . public_path('img/cp.jpg') . '" width="30">
            <div style="font-size:12pt; font-weight:bold;">JOSEPH PETER T. LIM, MD</div>
            <div>' . strtoupper($this->data['profile']->specialization1 . ' - ' . $this->data['profile']->specialization2) . '</div>
            <div>Fellow, Philippine College of Physicians</div>
            <div>Diplomate, Philippine Rheumatology Association</div>
            <div>Email: jplimmd.clinic@gmail.com</div>
            <hr>
        </div>';
    }

    // FOOTER SECTION
    private function footerHTML()
    {
        $signature = $this->data['profile']->signature
            ? '<img src="' . $this->data['profile']->signature . '" width="100">'
            : '';

        return '
        <div style="text-align:center; font-family: Arial; font-size: 8pt;">
            ' . $signature . '
            <div style="font-weight:bold;">' . strtoupper($this->data['profile']->name) . '</div>
            <div>License No: <u>' . $this->data['profile']->prc . '</u></div>
            <div>PTR No: <u>' . $this->data['profile']->ptr . '</u></div>
        </div>';
    }

    // BODY SECTION
    private function bodyHTML()
    {
        $patient_detail = $this->data['patient_detail']->patientname . ', ' .
            date_diff(date_create($this->data['patient_detail']->birthdate), date_create('now'))->y . ' years old, ';

        $paited_detail2 = ($this->data['patient_detail']->sex == 2 ? 'Female, ' : 'Male, ') .
            $this->data['patient_detail']->civil_status . ' residing in ';

        $patient_address = $this->data['patient_detail']->address;

        $undersigned = $this->data['appointment_detail']->referral_undersigned
            ? date_format(date_create($this->data['appointment_detail']->referral_undersigned), 'F d, Y')
            : '';

        // HTML content from DB
        $formContent = $this->data['appointment_detail']->form_content;

        return '
        <div style="font-size:10pt; line-height:1.6; margin-top:30px;">
            <p><b>Patient:</b> ' . $patient_detail . $paited_detail2 . $patient_address . '</p>
            <div>' . $formContent . '</div>
            <br><br>
            <p><b>Date:</b> ' . $undersigned . '</p>
        </div>';
    }
}
