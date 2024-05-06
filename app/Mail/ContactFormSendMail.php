<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactFormSendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name, $email, $phone, $subject1, $message;
    /**
     * Create a new message instance.
     */
    public function __construct($info)
    {
        Log::info($info);
        $this->name = $info['name'];
        $this->email = $info['email'];
        $this->phone = $info['phone'];
        $this->subject1 = $info['subject'];
        $this->message = $info['message'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Visitor have requested for information from Contact Us Form',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-form-send-mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
