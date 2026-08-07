<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactSubmission $submission
    ) {
    }

    public function build()
    {
        return $this
            ->from('noreply@pnehomes.com', 'CVG CMS')
            ->subject('New CVG Customer Inquiry - ' . $this->submission->full_name)
            ->view('emails.contact-submission')
            ->with([
                'submission' => $this->submission,
            ]);
    }
}
