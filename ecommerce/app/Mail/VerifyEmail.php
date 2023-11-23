<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verification_url;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @param  string  $verification_url
     * @return void
     */
    public function __construct(User $user, $verification_url)
    {
        $this->user = $user;
        $this->verification_url = $verification_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Verify Your Email')
            ->view('emails.verify_email');
    }
}
