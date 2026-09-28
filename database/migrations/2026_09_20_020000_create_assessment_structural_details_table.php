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
        Schema::create('assessment_structural_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('fema_building_type_id')
                ->nullable()
                ->constrained('fema_building_types')
                ->restrictOnDelete();

            $table->string('fema_version_code_snapshot')->nullable();
            $table->string('fema_version_title_snapshot')->nullable();
            $table->string('fema_version_edition_snapshot')->nullable();

            $table->string('fema_building_type_code_snapshot')->nullable();
            $table->string('fema_building_type_name_snapshot')->nullable();
            $table->string('material_category_snapshot')->nullable();
            $table->string('structural_system_snapshot')->nullable();

            $table->string('seismicity_level')->nullable();
            $table->string('soil_type')->nullable();

            $table->string('vertical_irregularity_type')->nullable();
            $table->string('plan_irregularity_type')->nullable();

            $table->boolean('has_pre_code_condition')->nullable();
            $table->boolean('has_post_benchmark_condition')->nullable();

            $table->text('site_condition_notes')->nullable();
            $table->text('structural_observation_notes')->nullable();

            $table->timestamps();

            $table->index('fema_building_type_id');
            $table->index('seismicity_level');
            $table->index('soil_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_structural_details');
    }
};
