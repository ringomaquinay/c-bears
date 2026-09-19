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
        Schema::create('fema_score_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fema_version_id')->constrained('fema_versions')->restrictOnDelete();
            $table->foreignId('fema_building_type_id')->constrained('fema_building_types')->restrictOnDelete();
            $table->string('seismicity_level');
            $table->string('assessment_level');
            $table->string('modifier_category');
            $table->string('modifier_code');
            $table->string('modifier_name');
            $table->decimal('modifier_value', 5, 2)->nullable();
            $table->boolean('is_applicable')->default(true);
            $table->text('applicability_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique([
                'fema_version_id',
                'fema_building_type_id',
                'seismicity_level',
                'assessment_level',
                'modifier_code',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fema_score_modifiers');
    }
};
