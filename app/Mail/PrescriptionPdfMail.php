<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PrescriptionPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $pdfContent;
    public $subject;

    public function __construct($pdfContent,$subject)
    {
        $this->pdfContent = $pdfContent;
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->subject('Prescription PDF '.$this->subject)
                    ->view('emails.prescription')
                    ->attachData($this->pdfContent, 'prescription.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}

