<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class PlatformMailConfig
{
    public static function apply(): void
    {
        try {
            if (! Schema::hasTable('platform_settings')) {
                return;
            }
            $settings = PlatformSetting::query()->first();
            if ($settings === null) {
                return;
            }
            $driver = $settings->mail_driver === 'smtp' ? 'smtp' : 'log';
            Config::set('mail.default', $driver);
            Config::set('mail.from.address', $settings->mail_from_address ?: config('mail.from.address'));
            Config::set('mail.from.name', $settings->mail_from_name ?: $settings->platform_name);

            if ($driver === 'smtp') {
                Config::set('mail.mailers.smtp.host', $settings->mail_host);
                Config::set('mail.mailers.smtp.port', $settings->mail_port);
                Config::set('mail.mailers.smtp.username', $settings->mail_username);
                Config::set('mail.mailers.smtp.password', $settings->mail_password);
                Config::set('mail.mailers.smtp.url', null);
                Config::set('mail.mailers.smtp.scheme', $settings->mail_encryption === 'ssl' ? 'smtps' : 'smtp');
                Config::set('mail.mailers.smtp.timeout', 15);
            }
        } catch (Throwable $exception) {
            // Houd applicatie beschikbaar bij een onbereikbare databank tijdens boot.
            report($exception);
        }
    }

    public static function fresh(): void
    {
        self::apply();
        Mail::purge('smtp');
        Mail::purge('log');
    }
}
