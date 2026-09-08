<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\ContactTopic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $phone,
        public ContactTopic $topic,
        public ?string $orderNumber,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->email, "{$this->firstName} {$this->lastName}"),
            ],
            subject: __('backend.contact.subject', ['topic' => $this->topic->getLabel()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact-message',
        );
    }
}
