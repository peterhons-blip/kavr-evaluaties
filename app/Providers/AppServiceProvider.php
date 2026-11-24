<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Links in e-mails moeten ook achter Apache/FastCGI altijd
        // het publieke HTTPS-adres gebruiken (nooit http of localhost).
        $publicUrl = (string) config('app.url');
        if (app()->isProduction() && str_starts_with($publicUrl, 'https://')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(rtrim($publicUrl, '/'));
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Support\PlatformMailConfig::apply();

        // Registratie na aanvaarding door het mailtransport, niet alleen
        // bij de expliciete SMTP-test. Bewaar nooit inhoud of resetlinks.
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Mail\Events\MessageSent::class,
            function (\Illuminate\Mail\Events\MessageSent $event): void {
                try {
                    $message = $event->message;
                    $addresses = [];
                    foreach ([$message->getTo(), $message->getCc(), $message->getBcc()] as $group) {
                        foreach ($group as $address) {
                            $addresses[] = $address->getAddress();
                        }
                    }
                    $subject = (string) ($message->getSubject() ?? '');
                    if ($subject === 'Artelynx - SMTP testbericht') {
                        // De testfunctie registreert al zijn eigen status en diagnose.
                        return;
                    }
                    $driver = (string) config('mail.default');
                    \App\Support\AccessAudit::record('platform.mail.sent', null, null, [
                        'success' => true,
                        'driver' => $driver,
                        'recipient' => implode(', ', array_unique($addresses)),
                        'subject' => \Illuminate\Support\Str::limit(trim(preg_replace('~https?://[^\\s]+~i', '[link verwijderd]', preg_replace('/[\\r\\n\\t]+/', ' ', $subject)) ?? ''), 200, ''),
                        'category' => 'app',
                        'result' => $driver === 'log' ? 'logged_only' : 'accepted_by_server',
                    ]);
                } catch (\Throwable $exception) {
                    // E-mail is verzonden: een mislukte audit mag dat niet
                    // als verzendingfout tonen of een tweede mail veroorzaken.
                    report($exception);
                }
            }
        );
    }
}
