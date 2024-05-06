<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookAppointmentStatusChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $appointment)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $message = '';
        switch ($this->appointment->status) {
            case 'Confirmed':
                $message = 'Your Appointment with XL Accounting is Confirmed';
                break;
            case 'Cancelled':
                $message = 'Your Appointment with XL Accounting is Cancelled';
                break;
            default:
                $message = 'Your Appointment with XL Accounting';
                break;
        }
        return new Envelope(
            subject: $message
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $markdown = '';
        switch ($this->appointment->status) {
            case 'Confirmed':
                $markdown = 'emails.client.book-appointment-status-confirmed';
                break;
            case 'Cancelled':
                $markdown = 'emails.client.book-appointment-status-cancelled';
                break;
            default:
                $markdown = '';
                break;
        }
        return new Content(
            markdown: $markdown
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
