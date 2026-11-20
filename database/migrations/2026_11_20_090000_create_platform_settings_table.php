<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->default('Artelynx');
            $table->string('support_email')->nullable();
            $table->string('default_timezone', 64)->default('Europe/Brussels');
            $table->string('mail_driver', 10)->default('log');
            $table->string('mail_host')->nullable();
            $table->unsignedSmallInteger('mail_port')->default(587);
            $table->string('mail_encryption', 10)->default('tls');
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->default('Artelynx');
            $table->timestamp('last_test_at')->nullable();
            $table->string('last_test_result', 12)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};