<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fema_minimum_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fema_version_id')->constrained('fema_versions')->restrictOnDelete();
            $table->foreignId('fema_building_type_id')->constrained('fema_building_types')->restrictOnDelete();
            $table->string('seismicity_level');
            $table->decimal('minimum_score', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['fema_version_id', 'fema_building_type_id', 'seismicity_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fema_minimum_scores');
    }
};
