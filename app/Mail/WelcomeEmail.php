<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;

    /**
     * Create a new message instance.
     */
    public function __construct($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to CalaiaPH!')
                    ->view('emails.welcome');
    }
}
