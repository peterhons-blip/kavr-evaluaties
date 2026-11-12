
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_periods');
    }
};
