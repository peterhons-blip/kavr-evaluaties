<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'platform_name', 'support_email', 'default_timezone',
        'mail_driver', 'mail_host', 'mail_port', 'mail_encryption',
        'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name',
        'last_test_at', 'last_test_result',
    ];

    protected function casts(): array
    {
        return [
            'mail_password' => 'encrypted',
            'last_test_at' => 'datetime',
        ];
    }
}
