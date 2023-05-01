<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class Mailings extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $markdown;
    public $data;
    public $from_mail;
    public $from_name;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $markdown, $data=[], $from_mail="durham_schools@cfcing.org", $from_name="Durham Schools")
    {
        $this->subject = $subject;
        $this->markdown = $markdown;
        $this->data = $data;
        $this->from_mail = $from_mail;
        $this->from_name = $from_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->from_mail, $this->from_name),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->markdown,
            with: [
                'data' => $this->data
            ]
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
