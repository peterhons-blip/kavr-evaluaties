<?php

namespace App\Support;

use Throwable;

final class MailDiagnostics
{
    /**
     * Maak een bewust beperkte diagnose voor de beheerhistoriek.
     * Bewaar nooit ruwe exception-teksten: SMTP-responses kunnen credentials,
     * mailadressen of resetlinks bevatten.
     *
     * @return array{code:string, explanation:string, advice:string}
     */
    public static function classify(Throwable $error): array
    {
        $message = strtolower($error->getMessage());

        if (preg_match('/(?:535|534|5\\.7\\.8|badcredentials|authentication failed|authenticat(?:ion|e)|invalid credentials)/i', $message)) {
            return [
                'code' => 'authentication',
                'explanation' => 'SMTP-aanmelding geweigerd door de mailserver.',
                'advice' => 'Controleer gebruikersnaam, Google-app-wachtwoord en of SMTP-authenticatie is toegestaan.',
            ];
        }

        if (preg_match('/(?:530|5\\.7\\.0|starttls required|must issue a starttls)/i', $message)) {
            return [
                'code' => 'tls_required',
                'explanation' => 'De mailserver vereist een beveiligde verbinding.',
                'advice' => 'Gebruik smtp.gmail.com, poort 587 en STARTTLS.',
            ];
        }

        if (preg_match('/(?:certificate verify failed|ssl operation failed|tls|handshake|certificate)/i', $message)) {
            return [
                'code' => 'tls_error',
                'explanation' => 'TLS- of certificaatcontrole is mislukt.',
                'advice' => 'Controleer poort, beveiliging, datum/tijd van de server en vertrouwde certificaten.',
            ];
        }

        if (preg_match('/(?:connection timed out|timed out|timeout|could not connect|connection refused|network is unreachable|php_network_getaddresses|getaddrinfo|dns)/i', $message)) {
            return [
                'code' => 'connection',
                'explanation' => 'Kon geen verbinding maken met de SMTP-server.',
                'advice' => 'Controleer serveradres, poort 587, DNS en firewall/uitgaand netwerkverkeer.',
            ];
        }

        if (preg_match('/(?:550|551|552|553|554|5\\.1\\.1|recipient rejected|sender rejected|relay access denied)/i', $message)) {
            return [
                'code' => 'rejected',
                'explanation' => 'De mailserver heeft de afzender of ontvanger geweigerd.',
                'advice' => 'Controleer het afzenderadres, toegestane aliassen en het ontvangstadres.',
            ];
        }

        return [
            'code' => 'unknown',
            'explanation' => 'SMTP-verzending mislukt. De technische fout is niet veilig genoeg om rechtstreeks te tonen.',
            'advice' => 'Bekijk de Laravel-log lokaal voor de volledige foutmelding en deel geen wachtwoorden of tokens.',
        ];
    }
}
