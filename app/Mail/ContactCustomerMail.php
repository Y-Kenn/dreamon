<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $text;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $text)
    {
        $this->email = $email;
        $this->text = $text;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope();

        return $envelope->subject('お問合せを受け付けました')
            ->from(env('MAIL_ADDRESS_INFO'), 'Heartroid')
            ->to($this->email);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'emails.contactCustomer',
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
