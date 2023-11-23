<?php

// app/Mail/ForgotPasswordEmail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetPasswordUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($resetPasswordUrl)
    {
        $this->resetPasswordUrl = $resetPasswordUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reset Password - Tindakan Diperlukan')
            ->view('verification-email.forgot_password')->with([
                'resetPasswordUrl' => $this->resetPasswordUrl,
            ]);
    }
}