<?php

// app/Mail/SendEmail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verification_url;

    /**
     * Create a new message instance.
     */
    public function __construct($verification_url)
    {
        $this->verification_url = $verification_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Verifikasi Email Anda - Tindakan Diperlukan')
            ->view('verification-email.template_email')->with([
                'verification_url' => $this->verification_url,
            ]);
    }
}