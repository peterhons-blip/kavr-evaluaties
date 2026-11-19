<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_user', function (Blueprint $table) {
            $table->timestamp('dko3_hidden_at')->nullable()->index();
            $table->timestamp('dko3_synced_at')->nullable();
            $table->boolean('dko3_managed')->default(false);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_set_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('school_user', fn (Blueprint $table) => $table->dropColumn(['dko3_hidden_at', 'dko3_synced_at','dko3_managed']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('password_set_at'));
    }
};