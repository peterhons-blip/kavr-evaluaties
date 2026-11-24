<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission', 80);
            $table->boolean('allowed');
            $table->timestamps();
            $table->unique(['school_id', 'user_id', 'permission'], 'school_user_perm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_user_permissions');
    }
};
