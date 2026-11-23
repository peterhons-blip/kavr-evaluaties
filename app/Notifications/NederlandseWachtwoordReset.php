<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class NederlandseWachtwoordReset extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $firstName = trim((string) ($notifiable->first_name ?? ''));
        $greeting = $firstName !== '' ? 'Beste '.$firstName.',' : 'Beste,';

        return (new MailMessage)
            ->subject('Artelynx | Wachtwoord instellen of opnieuw instellen')
            ->greeting($greeting)
            ->line('Je ontvangt deze e-mail omdat er een aanvraag is gedaan om het wachtwoord van je Artelynx-account in te stellen of opnieuw in te stellen.')
            ->action('Wachtwoord instellen', $url)
            ->line('Deze link is '.$minutes.' minuten geldig en kan slechts één keer gebruikt worden.')
            ->line('Heb je dit niet aangevraagd? Dan hoef je niets te doen.')
            ->salutation('Met vriendelijke groeten, '.PHP_EOL.'Het Artelynx-team');
    }
}
