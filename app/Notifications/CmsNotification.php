<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $subject,
        private readonly string $message,
        private readonly ?string $actionUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return ['subject' => $this->subject, 'message' => $this->message, 'action_url' => $this->actionUrl];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->line($this->message);

        return $this->actionUrl ? $mail->action('View', $this->actionUrl) : $mail;
    }
}
