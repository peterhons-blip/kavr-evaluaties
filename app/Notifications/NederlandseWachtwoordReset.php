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
            ->line('Er is een aanvraag ontvangen om het wachtwoord van je Artelynx-account in te stellen of te wijzigen.')
            ->action('Wachtwoord instellen', $url)
            ->line('Voor je veiligheid is deze persoonlijke link '.$minutes.' minuten geldig en kan hij maar één keer worden gebruikt.')
            ->line('Heb je dit niet aangevraagd? Dan hoef je niets te doen.')
            ->salutation('Met vriendelijke groeten, het Artelynx-team');
    }
}
