<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingUserVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $firstName;

    public function __construct($token, $firstName)
    {
        $this->token = $token;
        $this->firstName = $firstName;
    }

    public function build()
    {
        $verifyUrl = url('/verify/' . $this->token);

        return $this->subject('Verify Your CalaiaPH Account')
                    ->markdown('emails.pending_verification', [
                        'name' => $this->firstName,
                        'verifyUrl' => $verifyUrl,
                    ]);
    }
}
