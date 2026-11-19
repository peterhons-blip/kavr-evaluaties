<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('slug', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'slug']);
        });

        // Bestaande schoolkoppelingen blijven behouden.
        // Rollen worden later per school via SchoolRoleCatalog aangevuld.
        foreach (DB::table('schools')->pluck('id') as $schoolId) {
            foreach (\App\Support\SchoolRoleCatalog::defaults() as $slug => $role) {
                DB::table('school_roles')->insert([
                    'school_id' => $schoolId,
                    'slug' => $slug,
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'permissions' => json_encode($role['permissions']),
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_roles');
    }
};
