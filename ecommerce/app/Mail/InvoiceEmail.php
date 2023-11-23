<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function build()
    {
        // Di sini Anda bisa menentukan tampilan email (view) dan data yang ingin Anda kirimkan
        return $this->view('emails.invoice')
            ->subject('Invoice for Event Registration');
    }
}