<?php

namespace App\Mail;

use App\Models\Farm;
use App\Models\FarmNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NotificationDigest extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, FarmNotification>  $notifications
     */
    public function __construct(public Farm $farm, public Collection $notifications) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Alertes — {$this->farm->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.notification-digest');
    }
}
