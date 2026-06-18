<?php

namespace App\Mail;

use App\Models\Announcement;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public Student $student
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.($this->announcement->priority ?? 'Normal').'] '.$this->announcement->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.class-announcement',
        );
    }
}
