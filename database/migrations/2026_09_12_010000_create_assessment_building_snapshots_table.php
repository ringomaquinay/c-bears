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
        Schema::create('assessment_building_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('building_code_snapshot');
            $table->string('building_name_snapshot');
            $table->string('owner_or_responsible_office_snapshot')->nullable();
            $table->text('address_snapshot')->nullable();
            $table->string('barangay_snapshot')->nullable();
            $table->decimal('latitude_snapshot', 10, 7)->nullable();
            $table->decimal('longitude_snapshot', 10, 7)->nullable();
            $table->string('primary_occupancy_snapshot')->nullable();
            $table->unsignedSmallInteger('number_of_storeys_snapshot')->nullable();
            $table->unsignedSmallInteger('year_built_snapshot')->nullable();
            $table->decimal('approximate_floor_area_snapshot', 12, 2)->nullable();
            $table->string('building_permit_number_snapshot')->nullable();
            $table->string('occupancy_permit_number_snapshot')->nullable();
            $table->string('property_reference_no_snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_building_snapshots');
    }
};
