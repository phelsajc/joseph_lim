<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string */
    public $code;

    /** @var string */
    public $userName;

    public function __construct($code, $userName)
    {
        $this->code = $code;
        $this->userName = $userName;
    }

    public function build()
    {
        return $this->subject('Your login verification code')
            ->view('emails.login-otp');
    }
}
