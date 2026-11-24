<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WachtwoordGewijzigd extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $firstName = trim((string) ($notifiable->first_name ?? ''));
        $greeting = $firstName !== '' ? 'Beste '.$firstName.',' : 'Beste,';

        return (new MailMessage)
            ->subject('Artelynx | Je wachtwoord is gewijzigd')
            ->greeting($greeting)
            ->line('Het wachtwoord van je Artelynx-account is succesvol gewijzigd.')
            ->line('Je kunt vanaf nu met je nieuwe wachtwoord aanmelden.')
            ->action('Naar Artelynx', route('login'))
            ->line('Heb je dit niet zelf gedaan? Neem dan onmiddellijk contact op met de beheerder van je school.')
            ->salutation('Met vriendelijke groeten, het Artelynx-team');
    }
}
