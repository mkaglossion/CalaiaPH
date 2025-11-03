<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $token;

    public function __construct($firstName, $token)
    {
        $this->firstName = $firstName;
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Verify your email address')
            ->markdown('emails.verify');
    }
}
