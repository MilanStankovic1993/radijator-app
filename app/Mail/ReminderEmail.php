<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reminder $reminder,
        public bool $isPre = false // da znamo da li je pre-reminder
    ) {}

    public function build()
    {
        $subjectPrefix = $this->isPre ? 'Podsetnik (uskoro): ' : 'Podsetnik: ';
        return $this->subject($subjectPrefix . $this->reminder->title)
            ->view('emails.reminder')
            ->with([
                'reminder' => $this->reminder,
                'isPre'    => $this->isPre,
            ]);
    }
}
