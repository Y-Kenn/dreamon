<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\TwitterAccount;

class LockedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $twitter_id;
    public $twitter_username;
    public $email_address;

    public function __construct($twitter_id, $twitter_username ,$email_address)
    {
        $this->twitter_id = $twitter_id;
        $this->twitter_username = $twitter_username;
        $this->email_address = $email_address;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope();

        return $envelope->subject('Twitterアカウントが凍結された可能性があります')
            ->from('foo@example.net', 'Kamitter')
            ->to($this->email_address);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'emails.locked',
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
